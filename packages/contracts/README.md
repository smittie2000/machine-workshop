# Generated contracts

This package contains TypeScript definitions generated from Laravel Data DTOs and PHP enums. PHP is the authoritative source; do not handwrite or edit a second puzzle/material interface here.

Import DTO types from `@workshop/contracts` and enum unions from `@workshop/contracts/enums`. Run `dev.ps1 generate` from the root after PHP contract changes. Include generated files and `typescript-transformer-manifest.json` in version control; the manifest lets Spatie remove stale outputs. Package-generated Illuminate/Spatie types are retained as generator support files.

See [domain contract](../../docs/03-puzzle-contract.md) and [development commands](../../docs/development.md). Generated contracts describe data; shared simulation implementation rules remain in `packages/simulation`.
