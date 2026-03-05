# Contao 5.7 Belegungsplan Bundle

[![](https://img.shields.io/packagist/v/tonsinn/belegungsplan-bundle.svg?style=flat-square)](https://packagist.org/packages/tonsinn/belegungsplan-bundle)
[![](https://img.shields.io/packagist/dt/tonsinn/belegungsplan-bundle.svg?style=flat-square)](https://packagist.org/packages/tonsinn/belegungsplan-bundle)
[![License](https://poser.pugx.org/tonsinn/belegungsplan-bundle/license)](//packagist.org/packages/tonsinn/belegungsplan-bundle)

Contao 5 Bundle zur Erstellung von Belegungsplänen. Basierend auf der Arbeit von Jan Karai (mailwurm/belegungsplan-bundle).


## Installation
```bash
composer require tonsinn/belegungsplan-bundle
```


## Dokumentation
Eine ausführliche Dokumentation zu den Komponenten finden sie auf der [**Projektwebseite - Komponenten**](https://belegungsplan-bundle.de/komponenten.html).


## Der neueste Changelog

### v4.0.0 (03.10.2024)

#### Wichtig: Da es sich um ein großes Update handelt, erstellen Sie bitte eine Sicherungskopie ihrer Datenbank und ihrer Contao-Installation. Es müssen eventuell Einstellungen geändert werden.

#### Anzeige-Einstellungen:
- Auswahlmöglichkeit Standard nach Belegzeiten, Ausgabe nach Anzahl von Monaten, Ausgabe nach individuellem Zeitraum
- Je nach Auswahl Monatsliste, Eingabe Monatsanzahl oder Eingabe Zeitraum für Start und Ende der Anzeige
- dafür Anpassungen in den Templates, Sprachenfiles, Datenbank
- Änderung der Breiten der Eingabefelder
- Neues Template mod_belegungsplan_table hinzugefügt
#### Eigener Text:
- Link-Einstellungen Linkfarbe, Farbtransparenz, text-decoration, text-decoration-style hinzugefügt
#### Erstellung Kategorie:
- Kategorienamen können jetzt verlinkt werden
- target-Attribut anpassbar
- individuelles title-Attribut möglich
- separate ID und Klasse kann vergeben werden
- Author kein Pflichtfeld mehr
- dafür Anpassungen in den Templates, Sprachenfiles, Datenbank
#### Erstellung Objekt:
- Objektnamen können jetzt verlinkt werden
- Infotext kann optional im Frontend angezeigt werden oder nur im Backend
- target-Attribut anpassbar
- individuelles title-Attribut möglich
- separate ID und Klasse kann vergeben werden
- Author kein Pflichtfeld mehr
- dafür Anpassungen in den Templates, Sprachenfiles, Datenbank
#### Erstellung Belegungszeit:
- Author kein Pflichtfeld mehr
#### Erstellung Feiertag:
- Name des Feiertag als title-Attribut verwendbar
- Author kein Pflichtfeld mehr
- dafür Anpassungen in den Templates, Sprachenfiles, Datenbank
#### Sonstige Änderungen:
- Unterstützung von PHP 5 wird eingestellt

Hier finden sie den kompletten [**Changelog**](https://github.com/tonsinn/belegungsplan-bundle/blob/master/CHANGELOG.md).

![Collage Belegungsplan-Bundle](https://github.com/tonsinn/belegungsplan-bundle/blob/master/docs/img/Belegungsplan-Bundle.jpg)
