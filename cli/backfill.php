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
 * Command-line utility to backfill Umm al-Qura Hijri dates for existing users.
 *
 * @package    profilefield_hijridate
 * @copyright  2026 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../../config.php');
require_once($CFG->libdir . '/clilib.php');

// Define CLI options.
[$options, $unrecognized] = cli_get_params([
    'help'      => false,
    'field'     => '',
    'source'    => '',
    'override'  => false,
    'batchsize' => 500,
], [
    'h' => 'help',
    'f' => 'field',
    's' => 'source',
    'o' => 'override',
    'b' => 'batchsize',
]);

if ($options['help']) {
    $help = <<<EOL
Backfill Umm al-Qura Hijri dates for registered Moodle users.

This script scans users who have an existing Gregorian date (such as Date of Birth)
and calculates & writes their corresponding Saudi Umm al-Qura Hijri date (YYYY-MM-DD)
into the designated custom profile field.

Options:
 -h, --help            Print out this help message.
 -f, --field=STRING    Shortname of the Hijri date profile field (optional).
                       If omitted, all fields of type 'hijridate' are processed.
 -s, --source=STRING   Shortname of the Gregorian source profile field (optional).
                       Overrides the default 'param4' source configured on the field.
 -o, --override        Force overwrite existing non-empty Hijri dates (default: false).
 -b, --batchsize=INT   Number of users to process per batch (default: 500).

Examples:
  # Backfill empty Hijri dates from configured source field:
  php user/profile/field/hijridate/cli/backfill.php --field=dob_hijri

  # Force overwrite all dates from custom field 'dob':
  php user/profile/field/hijridate/cli/backfill.php --field=dob_hijri --source=dob --override

EOL;
    cli_writeln($help);
    exit(0);
}

cli_heading('Umm al-Qura Hijri Date Backfill Utility');

// Find target Hijri fields.
$fieldparams = ['datatype' => 'hijridate'];
if (!empty($options['field'])) {
    $fieldparams['shortname'] = trim($options['field']);
}

$hijrifields = $DB->get_records('user_info_field', $fieldparams);
if (empty($hijrifields)) {
    if (!empty($options['field'])) {
        cli_error("No profile field of type 'hijridate' found with shortname '{$options['field']}'.");
    } else {
        cli_error("No custom profile fields of type 'hijridate' found on this site.");
    }
}

$batchsize = max(10, (int)$options['batchsize']);
$override = (bool)$options['override'];

foreach ($hijrifields as $hijrifield) {
    cli_writeln("Processing Hijri Field: {$hijrifield->name} [shortname: {$hijrifield->shortname}]");

    $sourceshortname = !empty($options['source']) ? trim($options['source']) : trim($hijrifield->param4 ?? '');
    if (empty($sourceshortname)) {
        cli_writeln("  [WARNING] No source Gregorian field configured. Skipping this field. Use --source to specify.");
        continue;
    }

    cli_writeln("  Source Field: {$sourceshortname}");
    cli_writeln("  Override Existing: " . ($override ? 'YES' : 'NO'));

    // Check if source field is in user_info_field or user table.
    $sourcefield = $DB->get_record('user_info_field', ['shortname' => $sourceshortname]);
    $userfields = 'id, username';
    if (!$sourcefield) {
        $usercolumns = $DB->get_columns('user');
        if (isset($usercolumns[$sourceshortname])) {
            $userfields .= ', ' . $sourceshortname;
        } else {
            cli_writeln("  [WARNING] Source field '{$sourceshortname}' not found in custom profile fields or user table. Skipping.");
            continue;
        }
    }

    $totalupdated = 0;
    $totalskipped = 0;
    $totalerrors = 0;
    $offset = 0;

    // Get total active users count.
    $totalusers = $DB->count_records('user', ['deleted' => 0]);
    cli_writeln("  Total active users to scan: {$totalusers}");

    while ($offset < $totalusers) {
        $users = $DB->get_records('user', ['deleted' => 0], 'id ASC', $userfields, $offset, $batchsize);
        if (empty($users)) {
            break;
        }

        foreach ($users as $user) {
            $rawgregorian = null;

            if ($sourcefield) {
                $sourcedata = $DB->get_record('user_info_data', [
                    'userid'  => $user->id,
                    'fieldid' => $sourcefield->id,
                ]);
                if ($sourcedata && !empty($sourcedata->data)) {
                    $rawgregorian = $sourcedata->data;
                }
            } else if (isset($user->{$sourceshortname}) && !empty($user->{$sourceshortname})) {
                $rawgregorian = $user->{$sourceshortname};
            }

            if (empty($rawgregorian)) {
                $totalskipped++;
                continue;
            }

            // Check existing Hijri date.
            $existing = $DB->get_record('user_info_data', [
                'userid'  => $user->id,
                'fieldid' => $hijrifield->id,
            ]);

            if ($existing && !empty($existing->data) && !$override) {
                $totalskipped++;
                continue;
            }

            // Convert to Hijri.
            try {
                $hijri = \profilefield_hijridate\helper\umalqura::gregorian_to_hijri($rawgregorian);
                $calculated = $hijri['formatted'];

                if ($existing) {
                    $existing->data = $calculated;
                    $DB->update_record('user_info_data', $existing);
                } else {
                    $record = (object)[
                        'userid'     => $user->id,
                        'fieldid'    => $hijrifield->id,
                        'data'       => $calculated,
                        'dataformat' => 0,
                    ];
                    $DB->insert_record('user_info_data', $record);
                }
                $totalupdated++;
            } catch (\Throwable $e) {
                $totalerrors++;
            }
        }

        $offset += $batchsize;
        $progress = min(100, round(($offset / $totalusers) * 100));
        cli_write("  Progress: {$progress}% ({$offset}/{$totalusers})\r");
    }

    cli_writeln("\n  [COMPLETED] Field {$hijrifield->shortname}:");
    cli_writeln("   - Updated: {$totalupdated}");
    cli_writeln("   - Skipped: {$totalskipped}");
    cli_writeln("   - Errors:  {$totalerrors}\n");
}

cli_writeln('Backfill process finished successfully.');
exit(0);
