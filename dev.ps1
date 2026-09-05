param([ValidateSet('setup','up','down','check','logs','docker','generate')][string]$Action = 'up')
$DockerArguments = $args
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
function Generate-Contracts {
    Invoke-Docker compose run --rm --no-deps api php artisan route:clear --no-interaction
    Invoke-Docker compose run --rm --no-deps api php artisan typescript:transform --no-interaction
    Invoke-Docker compose run --rm --no-deps api php artisan wayfinder:generate --path=../web/src/generated --no-interaction
    Invoke-Docker compose run --rm --no-deps api php artisan workshop:export-simulation-fixture --no-interaction
}
function Get-GeneratedSnapshot {
    $paths = @('packages/contracts', 'apps/web/src/generated', 'packages/simulation/fixtures')
    $files = Get-ChildItem -Path $paths -Recurse -File | Where-Object { $_.Extension -in @('.ts', '.json') }
    return ($files | Sort-Object FullName | ForEach-Object { $_.FullName + ':' + (Get-FileHash -LiteralPath $_.FullName -Algorithm SHA256).Hash }) -join "`n"
}
switch ($Action) {
    'docker' { Invoke-Docker @DockerArguments }
    'generate' { Generate-Contracts }
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
        Invoke-Docker compose run --rm api php artisan db:seed --force --no-interaction
        Generate-Contracts
    }
    'up' { Invoke-Docker compose up -d --wait --wait-timeout 180 }
    'down' { Invoke-Docker compose down }
    'logs' { Invoke-Docker compose logs --tail 100 }
    'check' {
        $generatedBefore = Get-GeneratedSnapshot
        Generate-Contracts
        if ($generatedBefore -cne (Get-GeneratedSnapshot)) {
            throw 'Generated contracts changed. Review the output, then rerun check. Use dev.ps1 generate after PHP DTO or route edits.'
        }
        Invoke-Docker compose --profile test up -d --wait test-db
        Invoke-Docker compose run --rm --no-deps web pnpm build
        Invoke-Docker compose run --rm --no-deps web pnpm typecheck
        Invoke-Docker compose run --rm --no-deps web pnpm test
        Invoke-Docker compose run --rm --no-deps web pnpm --filter '@workshop/verifier' smoke
        Invoke-Docker compose run --rm --no-deps api php artisan config:clear --no-interaction
        Invoke-Docker compose run --rm --no-deps api php artisan test --compact
    }
}
