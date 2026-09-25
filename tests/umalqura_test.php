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

namespace profilefield_hijridate;

use profilefield_hijridate\helper\umalqura;

/**
 * Unit tests for Umm al-Qura conversion engine.
 *
 * @package    profilefield_hijridate
 * @category   test
 * @copyright  2026 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \profilefield_hijridate\helper\umalqura
 */
class umalqura_test extends \advanced_testcase {
    /**
     * Tests Gregorian to Hijri Umm al-Qura conversion with official benchmark dates.
     *
     * @covers ::gregorian_to_hijri
     */
    public function test_gregorian_to_hijri_benchmarks(): void {
        // Benchmark 1: 1974-05-15 -> 1394-04-22.
        $res1 = umalqura::gregorian_to_hijri('1974-05-15');
        $this->assertEquals(1394, $res1['year']);
        $this->assertEquals(4, $res1['month']);
        $this->assertEquals(22, $res1['day']);
        $this->assertEquals('1394-04-22', $res1['formatted']);

        // Benchmark 2: 2024-03-11 (1st Ramadan 1445 AH).
        $res2 = umalqura::gregorian_to_hijri('2024-03-11');
        $this->assertEquals(1445, $res2['year']);
        $this->assertEquals(9, $res2['month']);
        $this->assertEquals(1, $res2['day']);
        $this->assertEquals('1445-09-01', $res2['formatted']);

        // Benchmark 3: Integer timestamp input.
        $timestamp = (int)gmmktime(12, 0, 0, 5, 15, 1974);
        $res3 = umalqura::gregorian_to_hijri($timestamp);
        $this->assertEquals('1394-04-22', $res3['formatted']);
    }

    /**
     * Tests reverse Hijri to Gregorian conversion.
     *
     * @covers ::hijri_to_gregorian
     */
    public function test_hijri_to_gregorian(): void {
        $timestamp = umalqura::hijri_to_gregorian(1394, 4, 22);
        $this->assertNotNull($timestamp);
        $this->assertEquals('1974-05-15', gmdate('Y-m-d', $timestamp));

        $timestamp2 = umalqura::hijri_to_gregorian(1445, 9, 1);
        $this->assertNotNull($timestamp2);
        $this->assertEquals('2024-03-11', gmdate('Y-m-d', $timestamp2));

        // Invalid inputs.
        $this->assertNull(umalqura::hijri_to_gregorian(0, 1, 1));
        $this->assertNull(umalqura::hijri_to_gregorian(1445, 13, 1));
        $this->assertNull(umalqura::hijri_to_gregorian(1445, 1, 35));
    }

    /**
     * Tests parsing of Hijri date strings into components.
     *
     * @covers ::parse_hijri_string
     */
    public function test_parse_hijri_string(): void {
        $parsed = umalqura::parse_hijri_string('1394-04-22');
        $this->assertIsArray($parsed);
        $this->assertEquals(1394, $parsed['year']);
        $this->assertEquals(4, $parsed['month']);
        $this->assertEquals(22, $parsed['day']);

        // Invalid strings.
        $this->assertNull(umalqura::parse_hijri_string(''));
        $this->assertNull(umalqura::parse_hijri_string(null));
        $this->assertNull(umalqura::parse_hijri_string('invalid-date'));
        $this->assertNull(umalqura::parse_hijri_string('1394-15-22'));
    }

    /**
     * Tests date validation bounds and logic.
     *
     * @covers ::validate_hijri_date
     */
    public function test_validate_hijri_date(): void {
        $this->assertTrue(umalqura::validate_hijri_date(1394, 4, 22, 1300, 1500));
        $this->assertFalse(umalqura::validate_hijri_date(1250, 4, 22, 1300, 1500));
        $this->assertFalse(umalqura::validate_hijri_date(1394, 0, 22, 1300, 1500));
        $this->assertFalse(umalqura::validate_hijri_date(1394, 13, 22, 1300, 1500));
        $this->assertFalse(umalqura::validate_hijri_date(1394, 4, 32, 1300, 1500));
    }

    /**
     * Tests month name retrieval in Arabic and English.
     *
     * @covers ::get_month_name
     * @covers ::get_months
     */
    public function test_month_names(): void {
        $this->assertEquals('Muharram', umalqura::get_month_name(1, 'en'));
        $this->assertEquals('محرم', umalqura::get_month_name(1, 'ar'));

        $this->assertEquals('Ramadan', umalqura::get_month_name(9, 'en'));
        $this->assertEquals('رمضان', umalqura::get_month_name(9, 'ar'));

        $monthsen = umalqura::get_months('en');
        $this->assertCount(12, $monthsen);
        $this->assertEquals('Dhu al-Hijjah', $monthsen[12]);

        $monthsar = umalqura::get_months('ar');
        $this->assertCount(12, $monthsar);
        $this->assertEquals('ذو الحجة', $monthsar[12]);
    }

    /**
     * Tests formatted output generation (iso, text, both).
     *
     * @covers ::format_hijri
     */
    public function test_format_hijri(): void {
        // ISO format.
        $iso = umalqura::format_hijri('1394-04-22', 'iso', 'en');
        $this->assertEquals('1394-04-22', $iso);

        // Text format.
        $text = umalqura::format_hijri('1394-04-22', 'text', 'en');
        $this->assertStringContainsString('22', $text);
        $this->assertStringContainsString('1394', $text);

        // Both format.
        $both = umalqura::format_hijri('1394-04-22', 'both', 'en');
        $this->assertStringContainsString('1394-04-22', $both);
        $this->assertStringContainsString('(', $both);

        // Empty input.
        $empty = umalqura::format_hijri('');
        $this->assertNotEmpty($empty);
    }

    /**
     * Tests retrieval of current Hijri year.
     *
     * @covers ::get_current_hijri_year
     */
    public function test_get_current_hijri_year(): void {
        $year = umalqura::get_current_hijri_year();
        $this->assertIsInt($year);
        $this->assertGreaterThanOrEqual(1445, $year);
    }
}
