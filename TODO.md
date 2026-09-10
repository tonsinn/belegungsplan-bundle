# TODO — Offene Punkte (belegungsplan-bundle)

## Contao 6 / 5.3-Update

Erledigt (CLI-/Container-Ebene, gegen 5.3.51 / 5.7.13 / 6.0.0 verifiziert):
- [x] `testinstall.env` befüllt, `bin/remote-install.sh` auf der Testinstanz gelaufen
- [x] Composer-Constraint auf `^5.3 || ^6.0`, PHP auf `^8.2` gesenkt
- [x] Composer-Auflösung, Symfony-Container-Build, Service-/Fragment-Registrierung,
      `lint:twig` und PHP-Lint auf allen drei Zielversionen fehlerfrei
- [x] Contao-6-Breaking-Changes geprüft (kein `.html5`, kein `@Contao_Global`, kein
      `|insert_tag_raw`; `decodeEntities` muss bleiben, `Widget::generate()` unkritisch)

- [x] Funktionstest im Browser unter Contao 6.0 und 5.3 durchgeführt (10.09.2026).
      Dabei gefunden und mit v5.1.2 behoben: Anlegen eines Objekts in Contao 6 scheiterte
      am fehlenden `list.label` in `tl_belegungsplan_objekte`.

Offen:
- [x] PHP-Constraint bleibt `^8.2` (seit v5.1.0 veröffentlicht), damit 5.3-LTS-Instanzen
      auf PHP 8.2/8.3 das Bundle nutzen können.
- [x] Wegwerf-Testinstallationen `~/www/compat-test-53` und `~/www/compat-test-60` auf dem
      Server gelöscht.
- [ ] Demo-Instanz belegungsplan.tonsinn.de läuft aktuell über das Composer-Path-Repo auf
      `dev-main` (Symlink auf `bundles/belegungsplan-bundle`). Nach dem v5.1.0-Release
      entscheiden, ob sie auf die Packagist-Version zurückgestellt wird
      (`bin/remote-uninstall.sh` + `composer require tonsinn/belegungsplan-bundle:^5.1`).
- [ ] Optional: `.github/workflows/ci.yml` mit Matrix `php: [8.2, 8.3, 8.4]` ×
      `contao: [5.3.*, 5.7.*, 6.0.*]` (`composer validate` + `composer install`)
- [x] Lokale Git-Remote auf SSH umgestellt, keine Anmeldedaten mehr in `.git/config`.
- [x] README.md: Changelog-Auszug auf v5.1.0 aktualisiert

## Sonstiges
- [x] `public/style.css` entfernt. War toter Code (nirgends registriert, seit dem
      v5.0.0-Aufräumen ohne `config.php`) und zudem redundant: die einzigen noch in den
      DCA genutzten Klassen `w25`/`w33` bringt Contao-Core selbst mit (in 5.3 wie 6.0).

## Fachliche Punkte — waren Release-Gate für v5.1.1
Beide mit v5.1.1 (10.09.2026) erledigt. Der zugehörige Metadaten-PR ist eröffnet und
wartet nur noch auf das Review der Contao-Maintainer.
- [x] 'Belegungsplan Liste' zeigt Monatsname und Jahr hinter dem Kategorie-Titel
      (`mod_belegungsplan_table.html.twig`, `<span class="blp-category-month">`).
      Sichtbar nur, wenn „Hauptkategorien anzeigen" aktiv ist — die Kategoriezeile
      wird sonst gar nicht gerendert.
- [x] `public/belegungsplan.svg` durch die neue, für 360×360 optimierte Grafik ersetzt.
      Wirkt über `extra.logo` in der composer.json auf Contao Manager und Packagist.
- [x] PR an `contao/package-metadata` eröffnet: **#790** „[tonsinn/belegungsplan-bundle]
      Update logo and mention Contao 6" (Logo + „Contao 5 und 6" in de.yml/en.yml).
      Wartet auf Review durch die Contao-Maintainer.
      https://github.com/contao/package-metadata/pull/790
