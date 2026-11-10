# AGENTS.md

## Project

PHP library (`craftpip/process-handler`) for querying running OS processes by PID or name. OS-specific drivers parse `ps` (Unix/macOS) or `tasklist` (Windows) output into `Process` objects.

- Namespace: `Craftpip\ProcessHandler`
- PSR-4 autoload root: `src/` → `Craftpip\`
- Test namespace: `Craftpip\Tests\` → `tests/`
- Entry point: `src/ProcessHandler/ProcessHandler.php`
- Driver interface: `src/ProcessHandler/Drivers/DriversInterface.php`
- Runtime dependency: `symfony/process ^4.4|^5.4|^6.2`
- PHP requirement: `>=7.2`

## Setup

```sh
composer install --prefer-dist --dev
```

`vendor/` is gitignored and required before any code runs or tests execute.

## Commands

```sh
vendor/bin/phpunit                                    # run full test suite
vendor/bin/phpunit tests/Drivers/UnixDriverTest.php   # single test file
vendor/bin/phpstan analyse                            # static analysis (level 6)
vendor/bin/php-cs-fixer fix --dry-run --diff          # check code style
vendor/bin/php-cs-fixer fix                           # auto-fix code style
composer test                                         # alias for phpunit
composer analyse                                      # alias for phpstan
composer cs:check                                     # alias for cs dry-run
composer cs:fix                                       # alias for cs fix
```

## Testing

- PHPUnit config is at `phpunit.xml` (bootstrap: `vendor/autoload.php`).
- Tests live in `tests/` with subdirectories mirroring `src/`.
- Driver tests use reflection to access private `parse()` methods — they test parsing logic with fixture strings, not real OS commands.
- `tests/ProcessHandlerTest.php` — integration tests that call real OS commands (skip gracefully if platform doesn't match).
- `tests/ProcessTest.php` — unit tests for the `Process` value object.

## CI

GitHub Actions (`.github/workflows/ci.yml`):
- **Test matrix**: PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4 on ubuntu-latest
- **Static analysis**: PHPStan + php-cs-fixer dry-run on PHP 8.1+ (push only)

## Architecture

`ProcessHandler` detects the OS via `PHP_OS` and delegates to the matching driver:

| Driver | File | `ps` / `tasklist` columns |
|--------|------|--------------------------|
| Unix | `src/ProcessHandler/Drivers/Unix.php` | `ps -eo pid,time,size,user,session,cmd` |
| MacOS | `src/ProcessHandler/Drivers/MacOS.php` | `ps -eo pid,time,rss,user,sess,args` |
| Windows | `src/ProcessHandler/Drivers/Windows.php` | `tasklist /V` CSV output |

Each driver parses CLI output into `Process` value objects (`src/ProcessHandler/Process.php`).

## Gotchas

- The `Process::isRunning()` method creates a new `ProcessHandler` instance internally — it re-queries the OS rather than caching state.
- `ProcessHandler::__construct()` returns `$this->api` (the driver), which is unusual and misleading. The return value is ignored by callers.
- `symfony/process` version varies by PHP: 4.4 for PHP 7.2-7.4, 5.4 for PHP 8.0, 6.2 for PHP 8.1+. All are allowed by `composer.json`.
- PHPStan runs on PHP 8.1+ only (it requires 8.1 itself). CI runs it in a separate job.
