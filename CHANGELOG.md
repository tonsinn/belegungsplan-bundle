# Contao Belegungsplan-Bundle Changelog

### v5.0.0 Stable (05.03.2026)
#### Allgemein
- Tiefgreifende Weiterentwicklung auf Kompatibilität mit Contao 5.7
- Paket umbenannt zu `tonsinn/belegungsplan-bundle` (basierend auf `mailwurm/belegungsplan-bundle` von Jan Karai)
- Entfernung von Contao 4-Altlasten
#### Anzeige-Einstellungen
- Neue Option: „Anreise/Abreise als vollständig belegt anzeigen?"
#### Template-Einstellungen
- Neues Frontend-Modul-Setting: Darstellung als Liste/Tabelle oder als Übersicht im Bootstrap Grid
- Umstellung aller `.html5`-Templates auf Twig

### v4.0.0 Stable (03.10.2024)
#### Anzeige-Einstellungen
- Auswahlmöglichkeit Standard nach Belegzeiten, Ausgabe nach Anzahl von Monaten, Ausgabe nach individuellem Zeitraum
- Je nach Auswahl Monatsliste, Eingabe Monatsanzahl oder Eingabe Zeitraum für Start und Ende der Anzeige
- dafür Anpassungen in den Templates, Sprachenfiles, Datenbank
- Änderung der Breiten der Eingabefelder
#### Eigener Text
- Link-Einstellungen Linkfarbe, Farbtransparenz, text-decoration, text-decoration-style hinzugefügt
#### Erstellung Kategorie
- Kategorienamen können jetzt verlinkt werden
- target-Attribut anpassbar
- individuelles title-Attribut möglich
- separate ID und Klasse kann vergeben werden
- Author kein Pflichtfeld mehr
- dafür Anpassungen in den Templates, Sprachenfiles, Datenbank
#### Erstellung Objekt
- Objektnamen können jetzt verlinkt werden
- Infotext kann optional im Frontend angezeigt werden oder nur im Backend
- target-Attribut anpassbar
- individuelles title-Attribut möglich
- separate ID und Klasse kann vergeben werden
- Author kein Pflichtfeld mehr
- dafür Anpassungen in den Templates, Sprachenfiles, Datenbank
#### Erstellung Belegungszeit
- Author kein Pflichtfeld mehr
#### Erstellung Feiertag
- Name des Feiertag als title-Attribut verwendbar
- Author kein Pflichtfeld mehr
- dafür Anpassungen in den Templates, Sprachenfiles, Datenbank
### Sonstiges
-Unterstützung von PHP 5 wird eingestellt

### v3.0.1 Stable (08.07.2022)
- Installation unter PHP 8.0 und höher jetzt möglich

### v3.0.0 Stable (09.02.2021)
- Wochenenden optisch hervorheben hinzugefügt (Hintergrundfarbe der Wochenenden, Textfarbe der Wochenenden, Transparenz, Zurücksetzen-Button)
- Legende Einstellungen hinzugefügt (Legende anzeigen, Textfarbe Legende freie Tage, Textfarbe Legende belegte Tage, Transparenz, Zurücksetzen-Button)
- Kategorie Einstellungen hinzugefügt (Hauptkategorien anzeigen, Hintergrundfarbe der Kategoriezeile, Textfarbe der Kategoriezeile, Transparenz, Zurücksetzen-Button)
- Rahmen Einstellungen hinzugefügt (Farbe der Tabellenrahmen, Transparenz, Zurücksetzen-Button)
- Text Einstellungen hinzugefügt (Textfarbe, Transparenz, Zurücksetzen-Button)
- Farbauswahl belegte Tage hinzugefügt (Farbe der belegten Tage, Transparenz, Zurücksetzen-Button)
- Farbauswahl freie Tage hinzugefügt (Farbe der freien Tage, Transparenz, Zurücksetzen-Button)
- Hilfe-Assistenten hinzugefügt
- eigene Widget erstellt
- Feiertage optisch hervorheben hinzugefügt (Hintergrundfarbe, Textfarbe des Feiertages)
- Templates integriert
- Kategorie kopieren wurde entfernt
- Datenbank erweitert
- Sprachenfiles angepasst

### v2.4.7 Stable (04.12.2020)
- Eintägige Buchungen jetzt möglich
- Bug behoben der eine Buchung über Silvester und Neujahr nicht bzw. falsch angezeigt hat
- Datenbank erweitert
- Sprachenfiles angepasst

### v2.4.6 Stable (25.01.2020)
- Einfügen einer fehlenden clear-Klasse, welche verhinderte Objektnamen zu bearbeiten

### v2.4.5 Stable (22.07.2019)
- Anpassung composer.json

### v2.4.4 Stable (06.03.2018)
- Texte für Ausgabe einer Legende in den Belegungsplänen Frontend

### v2.4.3 Stable (22.01.2018)
- Ausgabe Warnung bei Terminüberschneidung
- Ausgabe Warnung wenn Abreisetag vor Anreisetag

### v2.4.2 Stable (17.01.2018)
- Anpassung Sprachdateien
- SQL-Statement aktualisierung

### v2.4.1 Stable (08.01.2018)
