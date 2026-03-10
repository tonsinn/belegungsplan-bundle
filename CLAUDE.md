# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Bundle Does

`tonsinn/belegungsplan-bundle` is a Contao 5 bundle for creating occupancy/booking schedules ("Belegungspläne"). It renders a calendar grid showing bookings across multiple objects (e.g., rental rooms) grouped by categories, with configurable colors and display modes.

## Commands

Run tests from the bundle root (requires the Contao application's autoloader):

```bash
# From the Contao application root (~/contao57), not the bundle root
vendor/bin/phpunit -c belegungsplan-bundle/phpunit.xml.dist

# Run a single test file
vendor/bin/phpunit -c belegungsplan-bundle/phpunit.xml.dist belegungsplan-bundle/tests/TonsinnBelegungsplanBundleTest.php
```

No build step, linter, or CI config is configured in this bundle.

## Architecture

### Dual-Layer Structure

The bundle has two coexisting layers:

**Modern (Contao 5 / Symfony)** — in `src/`:
- `src/Controller/FrontendModule/BelegungsplanController.php` — The primary frontend module controller using `#[AsFrontendModule]` attribute. ~730 lines, handles all display logic.
- `src/EventListener/DataContainer/*.php` — Five listener classes using `#[AsCallback]` attributes for backend form callbacks (validation, formatting, color conversion).
- `src/DependencyInjection/` + `src/Resources/config/services.yaml` — Standard Symfony DI with autowiring.

**Legacy (Contao 4-style)** — in `src/Resources/contao/`:
- `modules/ModuleBelegungsplan.php` — Old-style module class, kept for backward compatibility.
- `dca/*.php` — Data Container Array definitions (global array mutations).
- `models/*.php` — Four Contao model classes mapping to database tables.
- `widgets/*.php` — Three custom backend widgets (`MonthYearWizard`, `CheckBoxWithDragAndDropWizard`, `CheckBoxWithoutDragAndDropWizard`).

The modern controller (`BelegungsplanController`) is the active implementation. The legacy module exists alongside it.

### Database Hierarchy

```
tl_belegungsplan_category         (top-level groupings)
  └── tl_belegungsplan_objekte    (objects/rooms, child of category)
        └── tl_belegungsplan_calender  (bookings, child of object)
tl_belegungsplan_feiertage        (standalone holiday definitions)
tl_module                         (frontend module config, extended with bundle fields)
```

Note: the booking table is named `tl_belegungsplan_calender` (German misspelling of "Kalender" — intentional, matches the database).

### DCA Split

DCA definitions exist in **two** locations that both get loaded by Contao:
- `src/Resources/contao/dca/` — original DCA files
- `contao/dca/` — newer/overriding DCA files (also merged by Contao's DCA loader)

When modifying table structure, check both locations. The `contao/` directory at the root is the Contao 5 standard location; `src/Resources/contao/` is the older convention.

### Templates

- `contao/templates/mod_belegungsplan.html.twig` — Main Twig template (Contao 5 style, active)
- `contao/templates/mod_belegungsplan_table.html.twig` — Table layout variant
- `src/Resources/contao/templates/modules/mod_belegungsplan_*.html5` — Legacy HTML5 templates (bootstrap, jquery, simple, table variants)

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
