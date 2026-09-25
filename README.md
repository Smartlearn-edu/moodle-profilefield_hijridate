# Moodle Profile Field: Hijri Date (`profilefield_hijridate`)

[![Moodle Plugin](https://img.shields.io/badge/Moodle-4.1%20to%205.x-orange.svg)](https://moodle.org/plugins/)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

A native Moodle custom user profile field plugin implementing the official Saudi **Umm al-Qura Calendar Engine** (`islamic-umalqura`), providing manual selection, automatic Gregorian-to-Hijri derivation, event-driven profile syncing, GDPR/Privacy API compliance, AMD frontend converter, and an administrative backfill CLI.

---

## Requirements

- **Moodle**: 4.1 (LTS), 4.2, 4.3, 4.4, 4.5, 5.0, or later
- **PHP**: 8.1, 8.2, 8.3, or 8.4
- **PHP Extension**: `ext-intl` (compiled with ICU library supporting `islamic-umalqura`, standard in modern PHP distributions)

---

## Key Features

1. **Native Moodle User Profile Field**:
   - Appears directly in **Site Administration $\rightarrow$ Users $\rightarrow$ User profile fields** under *"Create a new profile field"*.
2. **Official Saudi Umm al-Qura Calendar Engine**:
   - Built on PHP's native `IntlDateFormatter` with calendar identifier `islamic-umalqura` (accurate astronomical Saudi civil calendar).
   - Zero external Composer dependencies required.
3. **Three Flexible Operating Modes**:
   - **Mode 0 (Manual selection only)**: Trainee selects Day, Month, and Year directly in Hijri.
   - **Mode 1 (Auto-convert with editable override)**: Automatically derives the Hijri date from a Gregorian date field (e.g. Date of Birth) while allowing the trainee to manually adjust by $\pm 1$ day to match their physical Saudi National ID / Civil Registry card (الأحوال المدنية).
   - **Mode 2 (Strictly derived)**: Automatically calculated from the Gregorian date and locked as read-only.
4. **Standardized API Format**:
   - Stores and exports canonical `YYYY-MM-DD` (e.g. `1394-04-22`), directly compatible with government & regulatory integrations (Future Work / Freelance Portal, Absher, Yakeen, TVTC, National eLearning Center).
5. **Real-time Client-Side Auto-Converter**:
   - Unobtrusive AMD JavaScript module (`amd/src/converter.js`) listens to changes on the Gregorian date picker and updates Hijri selects with 0ms latency using browser `Intl`.
6. **Event-Driven Automatic Synchronization**:
   - Listens to `\core\event\user_created` and `\core\event\user_updated` to auto-calculate and populate the Hijri field whenever user profile data changes.
7. **GDPR / Privacy API Compliant**:
   - Full implementation of Moodle Privacy API (`\core_privacy\local\metadata\provider`, `core_userlist_provider`, `plugin\provider`).
8. **Administrative Backfill CLI**:
   - Includes `cli/backfill.php` to calculate and populate Hijri dates for existing registered students in one command.

---

## Directory Structure

```
user/profile/field/hijridate/
├── version.php                    # Plugin metadata and version specification
├── define.class.php               # Admin configuration form when defining the field
├── field.class.php                # Display, form rendering, and data preprocessing
├── lib.php                        # Plugin callbacks and hooks
├── classes/
│   ├── helper/
│   │   └── umalqura.php           # Core Umm al-Qura conversion engine
│   ├── observer/
│   │   └── user_observer.php      # Listens to user_created & user_updated for auto-sync
│   └── privacy/
│       └── provider.php           # Moodle GDPR / Privacy API compliance
├── db/
│   ├── events.php                 # Registers event listeners for auto-conversion
│   └── upgrade.php                # Database upgrade steps
├── lang/
│   ├── en/
│   │   └── profilefield_hijridate.php # English strings
│   └── ar/
│       └── profilefield_hijridate.php # Arabic strings (تقويم أم القرى / تاريخ هجري)
├── amd/
│   ├── src/
│   │   └── converter.js           # Real-time client-side auto-converter
│   └── build/
│       ├── converter.min.js       # Minified AMD bundle
│       └── converter.min.js.map   # Source map
├── cli/
│   └── backfill.php               # CLI tool to backfill existing users
└── tests/
    └── umalqura_test.php          # PHPUnit tests for date conversion accuracy
```

---

## Installation

1. Download or clone this repository into your Moodle installation:
   ```bash
   git clone https://github.com/Smartlearn-edu/moodle-profilefield_hijridate.git user/profile/field/hijridate
   ```
2. Log in as Site Administrator and navigate to **Site Administration $\rightarrow$ Notifications** to complete the database installation.

---

## Admin Configuration

1. Go to **Site Administration $\rightarrow$ Users $\rightarrow$ Accounts $\rightarrow$ User profile fields**.
2. Select **Create a new profile field $\rightarrow$ Hijri date**.
3. Configure the settings:
   - **Start Year**: Earliest Hijri year selectable (default: 1350 AH).
   - **End Year**: Latest Hijri year selectable (default: Current Year + 5).
   - **Conversion Mode**:
     - *Manual selection only*
     - *Auto-convert from Gregorian date (editable override)*
     - *Auto-convert and lock (read-only derived field)*
   - **Source Gregorian Field**: Shortname of the source field (e.g. `dob` or `birthdate`).
   - **Display Format**:
     - `Standard ISO`: `1394-04-22`
     - `Text format`: `22 ربيع الآخر 1394 هـ` / `22 Rabi' al-Thani 1394 AH`
     - `Both ISO and Text`: `1394-04-22 (22 ربيع الآخر 1394 هـ)`

---

## CLI Backfill Tool

To populate Hijri dates for existing users who already have a Gregorian date:

```bash
# Preview options:
php user/profile/field/hijridate/cli/backfill.php --help

# Backfill empty Hijri dates:
php user/profile/field/hijridate/cli/backfill.php --field=dob_hijri

# Force overwrite all dates from source field 'dob':
php user/profile/field/hijridate/cli/backfill.php --field=dob_hijri --source=dob --override
```

---

## Privacy API (GDPR)

This plugin fully supports the Moodle Privacy API:
- Stores user Hijri date data in `{user_info_data}` linked to the user context.
- Exports user date records during Moodle Data Privacy export requests.
- Deletes user records when account deletion is requested.

---

## Testing

Run PHPUnit tests via Moodle CLI:
```bash
vendor/bin/phpunit --filter profilefield_hijridate
```

---

## License

This plugin is licensed under the **GNU General Public License v3 or later (GPL-3.0-or-later)**.
Copyright 2026 Mohammad Nabil `<mohammad@smartlearn.education>`.
