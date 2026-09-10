# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Bundle Does

`tonsinn/belegungsplan-bundle` is a Contao bundle (5.3 to 6.0) for creating occupancy/booking schedules ("Belegungspläne"). It renders a calendar grid showing bookings across multiple objects (e.g., rental rooms) grouped by categories, with configurable colors and display modes.

## Supported Contao versions

`composer.json` requires `contao/core-bundle: ^5.3 || ^6.0` and `php: ^8.2`.

Verified (composer resolution, container build, Twig lint, PHP lint):
- **Contao 5.3.51** on PHP 8.3
- **Contao 5.7.13** on PHP 8.5 (the remote test install)
- **Contao 6.0.0** on PHP 8.5

Backend and frontend were additionally tested by hand in the browser on Contao 6.0 and 5.3
(v5.1.2). The CLI checks above did not catch the one runtime bug found there (missing
`list.label` in `tl_belegungsplan_objekte`), so repeat a browser pass after DCA changes.

Contao-6-relevant notes:
- Contao 6 builds record labels from `list.label.fields` even in sorting mode 4. Every DCA
  needs a `label` block, even if the list is rendered via `child_record_callback`.
- Contao 6 removed input encoding. Values are stored raw and must be escaped on output —
  the controller and the DCA listeners already do this via `StringUtil::specialchars()`.
  Keep that pattern for new code.
- **Do not remove `decodeEntities` from DCA `eval` arrays.** The option is gone from
  Contao 6's own widget handling, but `Contao\CoreBundle\Migration\Version600\OutputEncodingMigration`
  still reads the flag to decide how to decode existing column values during the 5→6 migration.
  It is also still functional in Contao 5.
- `Widget::generate()` was dropped for *frontend form* widgets only. `MonthYearWizard` is a
  backend DCA widget and still uses `generate()`, which Contao 6 keeps.
- The bundle uses no `.html5` templates, no `@Contao_Global` namespace and no
  `|insert_tag_raw` filter — the three most common Contao 6 breaking changes do not apply.

## Commands

Run tests from the bundle root (requires the Contao application's autoloader):

```bash
# From the Contao application root, not the bundle root
vendor/bin/phpunit -c belegungsplan-bundle/phpunit.xml.dist

# Run a single test file
vendor/bin/phpunit -c belegungsplan-bundle/phpunit.xml.dist belegungsplan-bundle/tests/TonsinnBelegungsplanBundleTest.php
```

No build step, linter, or CI config is configured in this bundle.

## Remote test install

Development is tested against https://belegungsplan.tonsinn.de (Contao Managed Edition,
currently 5.7, with demo data). Credentials live in `testinstall.env` (gitignored, template:
`testinstall.env.example`).

```bash
cp testinstall.env.example testinstall.env   # fill in, stays out of git
bin/remote-install.sh                        # first install: rsync + composer path repo + migrate
bin/sync-belegungsplan.sh [--migrate]        # push local changes
bin/remote-uninstall.sh                      # tear down
```

`bin/remote-install.sh` wires the bundle in as a Composer **path repository**, so
`vendor/tonsinn/belegungsplan-bundle` becomes a symlink to `bundles/belegungsplan-bundle`.
Verify that symlink after installing — if the package name in `composer.json` ever stops
matching `tonsinn/belegungsplan-bundle`, Composer silently ignores the path repo and pulls
the published version from Packagist instead, and you end up testing the wrong code.

The demo install carries third-party bundles (RockSolid suite, `terminal42/contao-url-rewrite`,
`heimseiten/...`) that are pinned to Contao 5.x, so that instance **cannot** be switched to
Contao 6. Cross-version checks therefore run in throwaway installs on the same server
(`~/www/compat-test-53`, `~/www/compat-test-60`) containing only `contao/manager-bundle`
plus this bundle. Note that Composer blocks every published Contao 5.3.x release by default
because of security advisories; the throwaway install uses `--no-security-blocking` purely to
test dependency resolution.

## Architecture

### Structure

**Symfony / Contao 5+ layer** — in `src/`:
- `src/Controller/FrontendModule/BelegungsplanController.php` — The primary frontend module controller using `#[AsFrontendModule]` attribute. Handles all display logic.
- `src/Controller/FrontendModule/BelegungsplanGekacheltController.php` — Second module type (`belegungsplan_gekachelt`), Bootstrap grid output.
- `src/EventListener/DataContainer/*.php` — Five listener classes using `#[AsCallback]` attributes for backend form callbacks (validation, formatting, color conversion).
- `src/DependencyInjection/` + `src/Resources/config/services.yaml` — Standard Symfony DI with autowiring.
- `src/Resources/contao/widgets/MonthYearWizard.php` — The one remaining classic `\Widget` subclass (backend DCA widget).

**Contao resources** — in `contao/` (top-level, Contao 5 convention):
- `contao/dca/*.php` — Data Container Array definitions.
- `contao/languages/{de,en}/` — XLIFF translations plus `explain.php`/`explain.xlf` help wizards.
- `contao/templates/*.html.twig` — Twig templates.

Assets live in `public/` (CSS, JS, SVG, help-wizard images).

Since v5.0.0 there is no legacy layer left: the old `ModuleBelegungsplan`/`ModuleBelegungsinfo` classes, the duplicated `src/Resources/contao/{dca,languages,models,config}` tree and all `.html5` templates were removed.

### Database Hierarchy

```
tl_belegungsplan_category         (top-level groupings)
  └── tl_belegungsplan_objekte    (objects/rooms, child of category)
        └── tl_belegungsplan_calender  (bookings, child of object)
tl_belegungsplan_feiertage        (standalone holiday definitions)
tl_module                         (frontend module config, extended with bundle fields)
```

Note: the booking table is named `tl_belegungsplan_calender` (German misspelling of "Kalender" — intentional, matches the database).

### Templates

All templates are Twig and live in `contao/templates/`:
- `mod_belegungsplan.html.twig` — Main template
- `mod_belegungsplan_table.html.twig` — Table layout (default for the `belegungsplan` module)
- `mod_belegungsplan_bootstrap.html.twig` — Bootstrap grid (default for `belegungsplan_gekachelt`)

All three extend `@Contao/frontend_module/_base.html.twig` and override only the `content`
block. That block exists unchanged in Contao 5.3, 5.7 and 6.0 (the base template's physical
path moved in 6.0, but the `@Contao` namespace resolves it in every version).

### Color Management Pattern

Colors are stored as hex strings in the DB (via `colorpicker` widget), converted to RGB arrays on load, and assembled into `rgba(r, g, b, opacity)` CSS strings when passed to templates. The conversion happens in `BelegungsplanModuleListener` (load/save callbacks) and `BelegungsplanFeiertageListener`.

### Frontend Module Display Modes

`BelegungsplanController` supports three display modes configured per module instance:
1. **Standard** — admin selects specific months via `MonthYearWizard`
2. **Automatic** — shows N months ahead from current date
3. **Custom range** — admin-defined start/end date range

### Validation in Listeners

- `BelegungsplanCalenderListener` — checks end date > start date and detects booking overlaps (marks conflicting records with a warning in the list view)
- `BelegungsplanFeiertageListener` — prevents duplicate holiday dates

## Key Patterns

- **Callbacks** use `#[AsCallback(table: 'tl_...', target: 'config.onsubmit_callback')]` attributes — no manual service registration needed due to autowiring.
- **Models** extend Contao's `Model` class with a static `$strTable` property and custom static finder methods.
- **Serialized arrays** — many fields store PHP-serialized arrays (`StringUtil::serialize/deserialize`), especially the month/year wizard selections and color configs.
- The `Plugin.php` (Contao Manager) declares this bundle replaces the legacy `belegungsplan` bundle (without vendor prefix).
