param([ValidateSet('setup','up','down','check','logs')][string]$Action = 'up')
$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot
$dockerCommand = Get-Command docker -ErrorAction SilentlyContinue
$dockerExe = if ($dockerCommand) { $dockerCommand.Source } else { 'C:\Program Files\Docker\Docker\resources\bin\docker.exe' }
if (!(Test-Path $dockerExe)) { throw 'Install and start Docker Desktop, then run this script again.' }
# Process-local only: newly installed Docker helpers may not be on this shell's PATH.
$env:PATH = (Split-Path $dockerExe) + [IO.Path]::PathSeparator + $env:PATH
function Invoke-Docker {
    & $dockerExe @args
    if ($LASTEXITCODE -ne 0) { throw "Docker command failed with exit code $LASTEXITCODE" }
}
Invoke-Docker info --format '{{.OSType}}'
switch ($Action) {
    'setup' {
        Invoke-Docker compose build
        Invoke-Docker compose run --rm --no-deps web pnpm install --frozen-lockfile
        Invoke-Docker compose run --rm --no-deps api composer install --no-interaction
        if (!(Test-Path apps/api/.env)) {
            Copy-Item apps/api/.env.example apps/api/.env
            Invoke-Docker compose run --rm --no-deps api php artisan key:generate
        }
        Invoke-Docker compose up -d --wait db
        Invoke-Docker compose run --rm api php artisan migrate --force
    }
    'up' { Invoke-Docker compose up -d --wait --wait-timeout 180 }
    'down' { Invoke-Docker compose down }
    'logs' { Invoke-Docker compose logs --tail 100 }
    'check' {
        Invoke-Docker compose run --rm --no-deps web pnpm build
        Invoke-Docker compose run --rm --no-deps web pnpm typecheck
        Invoke-Docker compose run --rm --no-deps web pnpm test
        Invoke-Docker compose run --rm --no-deps web pnpm --filter '@workshop/verifier' smoke
        Invoke-Docker compose run --rm api php artisan test
    }
}
