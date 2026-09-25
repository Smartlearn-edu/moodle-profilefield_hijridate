# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-09-25

### Added
- Native Moodle user profile field for Saudi Umm al-Qura Hijri calendar dates (`profilefield_hijridate`).
- Astronomical calculation engine using native PHP `IntlDateFormatter` (`islamic-umalqura`).
- Three operating modes:
  - Mode 0: Manual selection only.
  - Mode 1: Auto-convert from Gregorian date with editable override.
  - Mode 2: Auto-convert and lock (read-only derived field).
- Client-side real-time AMD auto-converter (`amd/src/converter.js`) with minified bundle and sourcemap.
- Event-driven automatic synchronization for `user_created` and `user_updated` events.
- Complete GDPR / Privacy API implementation (`\profilefield_hijridate\privacy\provider`).
- Administrative CLI backfill tool (`cli/backfill.php`).
- Unit test suite (`tests/umalqura_test.php`).
- Full English and Arabic localization (`lang/en` and `lang/ar`).
