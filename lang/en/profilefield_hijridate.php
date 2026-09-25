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

/**
 * English language strings for profilefield_hijridate.
 *
 * @package    profilefield_hijridate
 * @copyright  2026 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Hijri date';
$string['startyear'] = 'Start year';
$string['startyear_help'] = 'The earliest Hijri year that can be selected.';
$string['endyear'] = 'End year';
$string['endyear_help'] = 'The latest Hijri year that can be selected.';
$string['startyearafterend'] = 'Start year cannot be greater than end year';
$string['conversionmode'] = 'Conversion mode';
$string['conversionmode_help'] = 'Choose how this field obtains its value: manual entry by trainee, auto-converted from a Gregorian date with manual adjustment allowed, or strictly derived (read-only).';
$string['mode_manual'] = 'Manual selection only';
$string['mode_autoconvert_editable'] = 'Auto-convert from Gregorian date (editable override)';
$string['mode_autoconvert_locked'] = 'Auto-convert and lock (read-only derived field)';
$string['sourcefield'] = 'Source Gregorian field';
$string['sourcefield_help'] = 'The shortname of the Gregorian date profile field to watch for auto-conversion (e.g. dob, birthdate).';
$string['displayformat'] = 'Display format';
$string['displayformat_help'] = 'How the Hijri date should be displayed on the user profile.';
$string['format_iso'] = 'Standard ISO (e.g. 1394-04-22)';
$string['format_text'] = 'Text format (e.g. 22 Rabi II 1394 AH)';
$string['format_both'] = 'Both ISO and Text (e.g. 1394-04-22 (22 Rabi II 1394 AH))';
$string['defaultdata'] = 'Default value';
$string['defaultdata_help'] = 'Optional default Hijri date in YYYY-MM-DD format.';
$string['day'] = 'Day';
$string['month'] = 'Month';
$string['year'] = 'Year';
$string['hijri_suffix'] = 'AH';
$string['notset'] = 'Not set';
$string['err_requireddate'] = 'Hijri date is required.';
$string['err_incompletedate'] = 'Please select a complete Hijri date (day, month, and year).';
$string['err_invaliddate'] = 'The specified Hijri date is invalid.';
$string['err_invaliddefaultdata'] = 'Default date must be in YYYY-MM-DD format and contain a valid Hijri date.';

$string['month1'] = 'Muharram';
$string['month2'] = 'Safar';
$string['month3'] = 'Rabi\' al-Awwal';
$string['month4'] = 'Rabi\' al-Thani';
$string['month5'] = 'Jumada al-Ula';
$string['month6'] = 'Jumada al-Akhirah';
$string['month7'] = 'Rajab';
$string['month8'] = 'Sha\'ban';
$string['month9'] = 'Ramadan';
$string['month10'] = 'Shawwal';
$string['month11'] = 'Dhu al-Qi\'dah';
$string['month12'] = 'Dhu al-Hijjah';

$string['privacy:metadata:profilefield_hijridate:tableexplanation'] = 'The Hijri date profile field plugin stores users\' Hijri date values in Moodle\'s user profile field data table.';
$string['privacy:metadata:profilefield_hijridate:userid'] = 'The ID of the user whose Hijri date is stored.';
$string['privacy:metadata:profilefield_hijridate:fieldid'] = 'The ID of the Hijri date custom profile field.';
$string['privacy:metadata:profilefield_hijridate:data'] = 'The user\'s Hijri date in YYYY-MM-DD format.';
$string['privacy:metadata:profilefield_hijridate:dataformat'] = 'The format of the stored data.';
