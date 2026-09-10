# Contao Belegungsplan Bundle

[![](https://img.shields.io/packagist/v/tonsinn/belegungsplan-bundle.svg?style=flat-square)](https://packagist.org/packages/tonsinn/belegungsplan-bundle)
[![](https://img.shields.io/packagist/dt/tonsinn/belegungsplan-bundle.svg?style=flat-square)](https://packagist.org/packages/tonsinn/belegungsplan-bundle)
[![License](https://poser.pugx.org/tonsinn/belegungsplan-bundle/license)](//packagist.org/packages/tonsinn/belegungsplan-bundle)

Contao-Bundle zur Erstellung von Belegungsplänen, für Contao 5 und 6. Basierend auf der Arbeit von Jan Karai (mailwurm/belegungsplan-bundle).


## Systemvoraussetzungen
| | |
|---|---|
| Contao | 5.3 LTS, 5.7 LTS oder 6.0 |
| PHP | ab 8.2 (Contao 6 setzt selbst PHP 8.4 voraus) |

## Installation
```bash
composer require tonsinn/belegungsplan-bundle
```


## Dokumentation
Eine ausführliche Dokumentation zu den Komponenten finden sie auf der [**Projektwebseite - Komponenten**](https://belegungsplan-bundle.de/komponenten.html).


## Der neueste Changelog

### v5.1.1 (10.09.2026)

#### Logo:
- Neue, für 360×360 optimierte Grafik (Contao Manager, Packagist)

#### Belegungsplan-Liste:
- Monatsname und Jahr werden zusätzlich hinter dem Kategorie-Titel angezeigt
  (bei aktivierter Option „Hauptkategorien anzeigen")

### v5.1.0 (10.09.2026)

#### Contao-Kompatibilität:
- Unterstützung für **Contao 6.0** ergänzt
- Unterstützung auf **Contao 5.3 LTS** erweitert
- PHP-Anforderung von 8.4 auf 8.2 gesenkt, damit Contao-5.3-Installationen auf
  PHP 8.2/8.3 das Bundle nutzen können
- Am Bundle-Code selbst waren dafür keine Änderungen nötig

### v5.0.0 (05.03.2026)

#### Wichtig: Da es sich um ein großes Update handelt, erstellen Sie bitte eine Sicherungskopie ihrer Datenbank und ihrer Contao-Installation. Es müssen eventuell Einstellungen geändert werden.

#### Allgemein:
- Tiefgreifende Weiterentwicklung auf Kompatibilität mit Contao 5.7
- Entfernung von Contao 4-Altlasten

#### Anzeige-Einstellungen:
- Neue Option: „Anreise/Abreise als vollständig belegt anzeigen?"

#### Template-Einstellungen:
- Neues Frontend-Modul-Setting: Darstellung als Liste/Tabelle oder als Übersicht im Bootstrap Grid
- Umstellung aller `.html5`-Templates auf Twig

Hier finden sie den kompletten [**Changelog**](https://github.com/tonsinn/belegungsplan-bundle/blob/master/CHANGELOG.md).

![Collage Belegungsplan-Bundle](https://github.com/tonsinn/belegungsplan-bundle/blob/master/docs/img/Belegungsplan-Bundle.jpg)
