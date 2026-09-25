<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace profilefield_hijridate\helper;

/**
 * Umm al-Qura calendar conversion helper class.
 *
 * Encapsulates conversion between Gregorian and Saudi Umm al-Qura Hijri dates
 * using PHP's native IntlDateFormatter and IntlCalendar.
 *
 * @package    profilefield_hijridate
 * @copyright  2026 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class umalqura {
    /** @var array English names of Hijri months. */
    const MONTHS_EN = [
        1 => 'Muharram',
        2 => 'Safar',
        3 => "Rabi' al-Awwal",
        4 => "Rabi' al-Thani",
        5 => 'Jumada al-Ula',
        6 => 'Jumada al-Akhirah',
        7 => 'Rajab',
        8 => "Sha'ban",
        9 => 'Ramadan',
        10 => 'Shawwal',
        11 => "Dhu al-Qi'dah",
        12 => 'Dhu al-Hijjah',
    ];

    /** @var array Arabic names of Hijri months. */
    const MONTHS_AR = [
        1 => 'محرم',
        2 => 'صفر',
        3 => 'ربيع الأول',
        4 => 'ربيع الآخر',
        5 => 'جمادى الأولى',
        6 => 'جمادى الآخرة',
        7 => 'رجب',
        8 => 'شعبان',
        9 => 'رمضان',
        10 => 'شوال',
        11 => 'ذو القعدة',
        12 => 'ذو الحجة',
    ];

    /**
     * Converts a Gregorian timestamp or date string to Umm al-Qura Hijri components.
     *
     * @param int|string $time Gregorian timestamp (int) or date string (e.g. '1974-05-15').
     * @return array Associative array with year, month, day, formatted string, and month names.
     */
    public static function gregorian_to_hijri($time): array {
        if (is_numeric($time)) {
            $timestamp = (int)$time;
        } else if (is_string($time) && !empty($time)) {
            // Strip any trailing time or whitespace, normalize format.
            $timestr = trim($time);
            if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})/', $timestr, $matches)) {
                $timestamp = (int)gmmktime(12, 0, 0, (int)$matches[2], (int)$matches[3], (int)$matches[1]);
            } else {
                $parsed = strtotime($timestr . ' 12:00:00 UTC');
                $timestamp = ($parsed !== false) ? $parsed : time();
            }
        } else {
            $timestamp = time();
        }

        // Format into canonical ISO format using Latin numerals.
        $formatter = new \IntlDateFormatter(
            'en_US@calendar=islamic-umalqura',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            'Asia/Riyadh',
            \IntlDateFormatter::TRADITIONAL,
            'yyyy-MM-dd'
        );

        $formatted = $formatter->format($timestamp);
        $parts = explode('-', $formatted);

        $year = isset($parts[0]) ? (int)$parts[0] : 0;
        $month = isset($parts[1]) ? (int)$parts[1] : 0;
        $day = isset($parts[2]) ? (int)$parts[2] : 0;

        return [
            'year'          => $year,
            'month'         => $month,
            'day'           => $day,
            'formatted'     => sprintf('%04d-%02d-%02d', $year, $month, $day),
            'month_name_ar' => self::get_month_name($month, 'ar'),
            'month_name_en' => self::get_month_name($month, 'en'),
            'month_name'    => self::get_month_name($month),
        ];
    }

    /**
     * Converts an Umm al-Qura Hijri date back to a Gregorian Unix timestamp.
     *
     * @param int $year Hijri year (e.g. 1394).
     * @param int $month Hijri month (1 to 12).
     * @param int $day Hijri day (1 to 30).
     * @return int|null Unix timestamp representing noon UTC of the Gregorian date, or null on failure.
     */
    public static function hijri_to_gregorian(int $year, int $month, int $day): ?int {
        if ($year <= 0 || $month < 1 || $month > 12 || $day < 1 || $day > 30) {
            return null;
        }

        $datestr = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $formatter = new \IntlDateFormatter(
            'en_US@calendar=islamic-umalqura',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            'UTC',
            \IntlDateFormatter::TRADITIONAL,
            'yyyy-MM-dd'
        );

        $timestamp = $formatter->parse($datestr);
        if ($timestamp === false) {
            return null;
        }

        return (int)$timestamp;
    }

    /**
     * Parses a canonical Hijri date string (YYYY-MM-DD) into year, month, and day components.
     *
     * @param string|null $date Date string in YYYY-MM-DD format.
     * @return array|null Array with 'year', 'month', 'day' keys, or null if invalid.
     */
    public static function parse_hijri_string(?string $date): ?array {
        if (empty($date) || !is_string($date)) {
            return null;
        }

        $trimmed = trim($date);
        if (!preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $trimmed, $matches)) {
            return null;
        }

        $year = (int)$matches[1];
        $month = (int)$matches[2];
        $day = (int)$matches[3];

        if ($year <= 0 || $month < 1 || $month > 12 || $day < 1 || $day > 30) {
            return null;
        }

        return [
            'year'  => $year,
            'month' => $month,
            'day'   => $day,
        ];
    }

    /**
     * Validates whether given Hijri date components are valid and within the configured year range.
     *
     * @param int $year Hijri year.
     * @param int $month Hijri month (1-12).
     * @param int $day Hijri day (1-30).
     * @param int $minyear Minimum allowed year.
     * @param int $maxyear Maximum allowed year.
     * @return bool True if valid, false otherwise.
     */
    public static function validate_hijri_date(
        int $year,
        int $month,
        int $day,
        int $minyear = 1300,
        int $maxyear = 1500
    ): bool {
        if ($month < 1 || $month > 12) {
            return false;
        }
        if ($day < 1 || $day > 30) {
            return false;
        }
        if ($year < $minyear || $year > $maxyear) {
            return false;
        }
        return true;
    }

    /**
     * Returns the name of a Hijri month.
     *
     * @param int $month Month number (1 to 12).
     * @param string|null $lang Specific language code ('ar', 'en'), or null for current user language.
     * @return string Localized month name.
     */
    public static function get_month_name(int $month, ?string $lang = null): string {
        if ($month < 1 || $month > 12) {
            return '';
        }

        if ($lang === 'ar') {
            return self::MONTHS_AR[$month] ?? '';
        }
        if ($lang === 'en') {
            return self::MONTHS_EN[$month] ?? '';
        }

        // Try Moodle's string translation.
        if (function_exists('get_string')) {
            return get_string('month' . $month, 'profilefield_hijridate');
        }

        return self::MONTHS_EN[$month] ?? '';
    }

    /**
     * Returns an array of all 12 Hijri months with localized names.
     *
     * @param string|null $lang Specific language code, or null for current user language.
     * @return array Associative array of month number (1-12) to month name.
     */
    public static function get_months(?string $lang = null): array {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = self::get_month_name($m, $lang);
        }
        return $months;
    }

    /**
     * Formats a stored Hijri date string according to display format preference.
     *
     * @param string|null $hijridate Hijri date in YYYY-MM-DD format.
     * @param string $displayformat 'iso', 'text', or 'both'.
     * @param string|null $lang Specific language code, or null for current language.
     * @return string Formatted string ready for output.
     */
    public static function format_hijri(?string $hijridate, string $displayformat = 'both', ?string $lang = null): string {
        if (empty($hijridate)) {
            return function_exists('get_string') ? get_string('notset', 'profilefield_hijridate') : '';
        }

        $parsed = self::parse_hijri_string($hijridate);
        if (!$parsed) {
            return (string)$hijridate;
        }

        $iso = sprintf('%04d-%02d-%02d', $parsed['year'], $parsed['month'], $parsed['day']);

        if ($displayformat === 'iso') {
            return $iso;
        }

        $monthname = self::get_month_name($parsed['month'], $lang);
        $suffix = function_exists('get_string') ? get_string('hijri_suffix', 'profilefield_hijridate') : 'AH';
        $text = sprintf('%d %s %d %s', $parsed['day'], $monthname, $parsed['year'], $suffix);

        if ($displayformat === 'text') {
            return $text;
        }

        // Default to combined format.
        return sprintf('%s (%s)', $iso, $text);
    }

    /**
     * Retrieves the current Hijri year in the Umm al-Qura calendar.
     *
     * @return int Current Hijri year.
     */
    public static function get_current_hijri_year(): int {
        $hijri = self::gregorian_to_hijri(time());
        return $hijri['year'];
    }
}
