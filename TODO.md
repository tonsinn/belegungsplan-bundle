# TODO — Offene Punkte (belegungsplan-bundle)

## Contao 6 / 5.3-Update

Erledigt (CLI-/Container-Ebene, gegen 5.3.51 / 5.7.13 / 6.0.0 verifiziert):
- [x] `testinstall.env` befüllt, `bin/remote-install.sh` auf der Testinstanz gelaufen
- [x] Composer-Constraint auf `^5.3 || ^6.0`, PHP auf `^8.2` gesenkt
- [x] Composer-Auflösung, Symfony-Container-Build, Service-/Fragment-Registrierung,
      `lint:twig` und PHP-Lint auf allen drei Zielversionen fehlerfrei
- [x] Contao-6-Breaking-Changes geprüft (kein `.html5`, kein `@Contao_Global`, kein
      `|insert_tag_raw`; `decodeEntities` muss bleiben, `Widget::generate()` unkritisch)

Offen:
- [ ] **Funktionstest im Browser** steht noch aus (bewusst zurückgestellt): Backend-DCA
      aller vier Tabellen + `tl_module`-Palette anlegen/speichern, Frontend in allen drei
      `belegungsplan_showAusgabe`-Modi rendern. Braucht für 5.3/6.0 eine eigene
      Subdomain/vHost in KeyHelp — per SSH nicht anlegbar.
- [ ] Entscheidung: PHP-Constraint `^8.2` (jetzt) vs. `^8.4` (Upstream-Stand v5.0.9).
      Senkung war nötig, damit 5.3-LTS-Instanzen auf PHP 8.2/8.3 das Bundle nutzen können.
- [ ] Wegwerf-Testinstallationen auf dem Server aufräumen, wenn nicht mehr gebraucht:
      `~/www/compat-test-53`, `~/www/compat-test-60` (je einige hundert MB; prüfen, ob sie
      über den Default-vHost öffentlich erreichbar sind)
- [ ] Demo-Instanz belegungsplan.tonsinn.de läuft aktuell über das Composer-Path-Repo auf
      `dev-main` (Symlink auf `bundles/belegungsplan-bundle`). Nach dem v5.1.0-Release
      entscheiden, ob sie auf die Packagist-Version zurückgestellt wird
      (`bin/remote-uninstall.sh` + `composer require tonsinn/belegungsplan-bundle:^5.1`).
- [ ] Optional: `.github/workflows/ci.yml` mit Matrix `php: [8.2, 8.3, 8.4]` ×
      `contao: [5.3.*, 5.7.*, 6.0.*]` (`composer validate` + `composer install`)
- [ ] Lokale Git-Zugangsdaten: die Remote-URL in `.git/config` enthält Anmeldedaten im
      Klartext. Auf SSH oder einen Credential-Helper umstellen und die alten Daten
      zurückziehen. (Betrifft nur die lokale Arbeitskopie, nicht das Repository.)
- [x] README.md: Changelog-Auszug auf v5.1.0 aktualisiert

## Fachliche Punkte — Release-Gate für v5.1.1
**Beide Punkte müssen vor der Veröffentlichung von v5.1.1 erledigt sein.**
- [x] 'Belegungsplan Liste' zeigt Monatsname und Jahr hinter dem Kategorie-Titel
      (`mod_belegungsplan_table.html.twig`, `<span class="blp-category-month">`).
      Sichtbar nur, wenn „Hauptkategorien anzeigen" aktiv ist — die Kategoriezeile
      wird sonst gar nicht gerendert. Browser-Sichtprüfung steht noch aus.
- Belegungsplan.svg ist für 360x360 optimiert statt jetziger Grafik
  Metadaten-Repo meta/tonsinn/belegungsplan-bundle/logo.svg: identisch mit dem Original und
  bereits gemergt. Für das neue Logo bräuchte es einen eigenen, kleinen PR an
  contao/package-metadata.
