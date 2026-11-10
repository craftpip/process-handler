# Plan: Modernize craftpip/process-handler

## Goal

Bring the project up to modern standards while maintaining maximum PHP version compatibility (PHP 7.2+).

## Current State

- PHP library for querying OS processes by PID/name
- Requires PHP >=8.1 (only because of `symfony/process ^6.2`)
- PHPUnit ~3.7|~4.0|~5.0 with deprecated XML schema
- Single placeholder test (`'hey' == 'hey'`)
- No static analysis, no code style tooling
- Stale `.travis.yml` (tests PHP 5.3-7.1)
- Manual `test.php` not integrated into test suite

## Decisions

- **PHP floor**: 7.2 (EOL but max compat as requested)
- **symfony/process**: `^4.4|^5.4|^6.2` (covers PHP 7.2 through 8.x)
- **PHPUnit**: `^9.6` (supports PHP 7.3-8.x; skip PHP 7.2/7.3 from CI matrix — both EOL)
- **PHPStan**: `^1.12`, level 6, runs on PHP 8.1+ only
- **PHP-CS-Fixer**: `^3.0`, @PER-CS2.0 ruleset
- **CI**: GitHub Actions, PHP 7.4/8.0/8.1/8.2/8.3/8.4 matrix

## Steps

### 1. Update `composer.json`

- Change `"php": ">=8.1"` → `"php": ">=7.2"`
- Change `"symfony/process": "^6.2"` → `"symfony/process": "^4.4|^5.4|^6.2"`
- Change `"phpunit/phpunit": "~3.7|~4.0|~5.0"` → `"phpunit/phpunit": "^9.6"`
- Add `"phpstan/phpstan": "^1.12"` to require-dev
- Add `"friendsofphp/php-cs-fixer": "^3.0"` to require-dev
- Add scripts section:
  ```json
  "scripts": {
      "test": "phpunit",
      "analyse": "phpstan analyse",
      "cs:check": "php-cs-fixer fix --dry-run --diff",
      "cs:fix": "php-cs-fixer fix"
  }
  ```

### 2. Rewrite `phpunit.xml`

Migrate to PHPUnit 9 schema:
- Remove: `convertErrorsToExceptions`, `convertNoticesToExceptions`, `convertWarningsToExceptions`, `syntaxCheck`, `backupStaticAttributes`
- Replace `<filter><whitelist>` with `<source><include>`
- Update test suite directory to `tests/`
- Remove `<logging>` section (use CLI `--coverage-*` flags instead)

### 3. Create test directory structure

```
tests/
├── ProcessHandlerTest.php
├── ProcessTest.php
└── Drivers/
    ├── UnixDriverTest.php
    ├── WindowsDriverTest.php
    └── MacOSDriverTest.php
```

### 4. Write tests

**tests/ProcessHandlerTest.php:**
- `testGetProcessReturnsProcessObject` — mock driver, verify Process returned
- `testGetProcessReturnsFalseWhenNotFound` — mock driver returning empty array
- `testGetProcessThrowsExceptionWithoutPid` — no PID provided
- `testGetAllProcessesReturnsArray` — mock driver
- `testIsRunningReturnsTrueWhenProcessExists`
- `testIsRunningReturnsFalseWhenProcessNotFound`

**tests/ProcessTest.php:**
- `testGettersReturnConstructorValues` — all 9 getters
- `testIsRunningReQueriesProcessHandler` — verify it creates a new ProcessHandler instance

**tests/Drivers/UnixDriverTest.php:**
- `testParseReturnsProcessesFromPsOutput` — feed sample `ps -eo` output
- `testParseSkipsHeaderLine`
- `testParseHandlesSingleProcess`
- `testGetAllProcessesRunsCorrectCommand`
- `testGetProcessByPidRunsCorrectCommand`

**tests/Drivers/WindowsDriverTest.php:**
- `testParseOutputReturnsProcessesFromTasklistOutput` — feed sample `tasklist /V` output
- `testParseOutputHandlesEmptyOutput`
- `testParseOutputHandlesNoResults`

**tests/Drivers/MacOSDriverTest.php:**
- `testParseReturnsProcessesFromPsOutput` — feed sample macOS `ps` output

Driver tests will use reflection to access private `parse()`/`parseOutput()` methods, avoiding real OS calls.

### 5. Add PHPStan

Create `phpstan.neon`:
```neon
parameters:
    level: 6
    paths:
        - src
    tmpDir: .phpstan-cache
```

Run `phpstan analyse` and generate a baseline for any existing issues.

### 6. Add PHP-CS-Fixer

Create `.php-cs-fixer.dist.php`:
- Ruleset: `@PER-CS2.0`
- Target: `src/` and `tests/`
- Cache file: `.php-cs-fixer.cache`

Run once to auto-fix, commit the result.

### 7. Delete old files

- `.travis.yml` — stale, replaced by GitHub Actions
- `test.php` — manual smoke test, not part of suite
- `test/` directory — replaced by `tests/`

### 8. Create GitHub Actions CI

`.github/workflows/ci.yml`:
- Trigger: push and pull_request
- Matrix: PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4
- Steps: checkout, setup-php, composer install, phpunit
- Additional job (PHP 8.1+ only): phpstan analyse, php-cs-fixer --dry-run

### 9. Update `AGENTS.md`

Update commands, tooling info, and testing sections.

## Execution Order

1. ✅ composer.json + phpunit.xml
2. ✅ `tests/` directory with all test files
3. ✅ phpstan.neon
4. ✅ .php-cs-fixer.dist.php
5. ✅ Delete old files
6. ✅ GitHub Actions CI
7. ✅ AGENTS.md update
