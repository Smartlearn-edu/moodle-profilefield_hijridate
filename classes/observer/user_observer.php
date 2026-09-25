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

namespace profilefield_hijridate\observer;

/**
 * Event observer for user creation and update events.
 *
 * Automatically converts Gregorian date fields to Hijri Umm al-Qura dates
 * when auto-conversion is enabled on the field.
 *
 * @package    profilefield_hijridate
 * @copyright  2026 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_observer {
    /**
     * Observer for core\event\user_created.
     *
     * @param \core\event\user_created $event The event object.
     */
    public static function user_created(\core\event\user_created $event): void {
        self::process_user_sync((int)$event->objectid);
    }

    /**
     * Observer for core\event\user_updated.
     *
     * @param \core\event\user_updated $event The event object.
     */
    public static function user_updated(\core\event\user_updated $event): void {
        self::process_user_sync((int)$event->objectid);
    }

    /**
     * Synchronizes Gregorian dates to Hijri dates for all auto-convert fields of a user.
     *
     * @param int $userid The ID of the user.
     */
    public static function process_user_sync(int $userid): void {
        global $DB;

        if ($userid <= 0) {
            return;
        }

        try {
            $user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0]);
            if (!$user) {
                return;
            }

            // Find all profile fields of type hijridate.
            $hijrifields = $DB->get_records('user_info_field', ['datatype' => 'hijridate']);
            if (empty($hijrifields)) {
                return;
            }

            foreach ($hijrifields as $field) {
                $mode = isset($field->param3) ? (int)$field->param3 : 0;
                // Mode 1: Auto-convert with override allowed; Mode 2: Strictly derived.
                if ($mode !== 1 && $mode !== 2) {
                    continue;
                }

                $sourceshortname = !empty($field->param4) ? trim($field->param4) : '';
                if (empty($sourceshortname)) {
                    continue;
                }

                $rawgregorian = null;

                // First check custom profile fields.
                $sourcefield = $DB->get_record('user_info_field', ['shortname' => $sourceshortname]);
                if ($sourcefield) {
                    $sourcedata = $DB->get_record('user_info_data', [
                        'userid'  => $userid,
                        'fieldid' => $sourcefield->id,
                    ]);
                    if ($sourcedata && !empty($sourcedata->data)) {
                        $rawgregorian = $sourcedata->data;
                    }
                } else if (isset($user->{$sourceshortname}) && !empty($user->{$sourceshortname})) {
                    // Check if it's a standard user column (e.g. timecreated or custom column).
                    $rawgregorian = $user->{$sourceshortname};
                }

                if (empty($rawgregorian)) {
                    continue;
                }

                // Convert Gregorian date to Hijri canonical format.
                $hijri = \profilefield_hijridate\helper\umalqura::gregorian_to_hijri($rawgregorian);
                $calculatedhijri = $hijri['formatted'];

                $existing = $DB->get_record('user_info_data', [
                    'userid'  => $userid,
                    'fieldid' => $field->id,
                ]);

                if ($existing) {
                    if ($mode === 2) {
                        // Strictly derived: Always sync with calculated Hijri date.
                        if ($existing->data !== $calculatedhijri) {
                            $existing->data = $calculatedhijri;
                            $DB->update_record('user_info_data', $existing);
                        }
                    } else if ($mode === 1 && empty($existing->data)) {
                        // Mode 1: Only auto-populate if currently empty (preserves manual overrides).
                        $existing->data = $calculatedhijri;
                        $DB->update_record('user_info_data', $existing);
                    }
                } else {
                    $newrecord = (object)[
                        'userid'     => $userid,
                        'fieldid'    => $field->id,
                        'data'       => $calculatedhijri,
                        'dataformat' => 0,
                    ];
                    $DB->insert_record('user_info_data', $newrecord);
                }
            }
        } catch (\Throwable $e) {
            debugging('Error in profilefield_hijridate user sync: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
}
