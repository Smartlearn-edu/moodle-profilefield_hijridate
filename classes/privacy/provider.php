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

namespace profilefield_hijridate\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider implementation for profilefield_hijridate.
 *
 * @package    profilefield_hijridate
 * @copyright  2026 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Returns metadata about user data stored by this plugin.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection The collection of metadata items.
     */
    public static function get_metadata(collection $collection): collection {
        return $collection->add_database_table('user_info_data', [
            'userid'     => 'privacy:metadata:profilefield_hijridate:userid',
            'fieldid'    => 'privacy:metadata:profilefield_hijridate:fieldid',
            'data'       => 'privacy:metadata:profilefield_hijridate:data',
            'dataformat' => 'privacy:metadata:profilefield_hijridate:dataformat',
        ], 'privacy:metadata:profilefield_hijridate:tableexplanation');
    }

    /**
     * Gets the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user ID to search for.
     * @return contextlist The contextlist containing the list of contexts.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT ctx.id
                  FROM {user_info_data} uda
                  JOIN {user_info_field} uif ON uda.fieldid = uif.id
                  JOIN {context} ctx ON ctx.instanceid = uda.userid
                       AND ctx.contextlevel = :contextlevel
                 WHERE uda.userid = :userid
                       AND uif.datatype = :datatype";
        $params = [
            'userid'       => $userid,
            'contextlevel' => CONTEXT_USER,
            'datatype'     => 'hijridate',
        ];
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Gets the list of users within a specific context.
     *
     * @param userlist $userlist The userlist to populate with user IDs.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_user) {
            return;
        }

        $sql = "SELECT uda.userid
                  FROM {user_info_data} uda
                  JOIN {user_info_field} uif ON uda.fieldid = uif.id
                 WHERE uda.userid = :userid
                       AND uif.datatype = :datatype";

        $params = [
            'userid'   => $context->instanceid,
            'datatype' => 'hijridate',
        ];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Exports all user data for the specified user in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        $user = $contextlist->get_user();
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_USER && $context->instanceid == $user->id) {
                $results = static::get_records($user->id);
                foreach ($results as $result) {
                    $data = (object)[
                        'name'        => $result->name,
                        'description' => $result->description,
                        'data'        => \profilefield_hijridate\helper\umalqura::format_hijri(
                            $result->data,
                            $result->param5 ?: 'both'
                        ),
                        'rawdata'     => $result->data,
                    ];
                    writer::with_context($context)->export_data([
                        get_string('pluginname', 'profilefield_hijridate'),
                    ], $data);
                }
            }
        }
    }

    /**
     * Deletes all user data which matches the specified context.
     *
     * @param \context $context A user context.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if ($context->contextlevel == CONTEXT_USER) {
            static::delete_data($context->instanceid);
        }
    }

    /**
     * Deletes multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        $context = $userlist->get_context();

        if ($context instanceof \context_user) {
            static::delete_data($context->instanceid);
        }
    }

    /**
     * Deletes all user data for the specified user in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        $user = $contextlist->get_user();
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_USER && $context->instanceid == $user->id) {
                static::delete_data($context->instanceid);
            }
        }
    }

    /**
     * Deletes data related to a userid for hijridate fields.
     *
     * @param int $userid The user ID.
     */
    protected static function delete_data(int $userid) {
        global $DB;

        $params = [
            'userid'   => $userid,
            'datatype' => 'hijridate',
        ];

        $DB->delete_records_select(
            'user_info_data',
            "fieldid IN (SELECT id FROM {user_info_field} WHERE datatype = :datatype) AND userid = :userid",
            $params
        );
    }

    /**
     * Gets records related to this plugin and user.
     *
     * @param int $userid The user ID.
     * @return array An array of records.
     */
    protected static function get_records(int $userid): array {
        global $DB;

        $sql = "SELECT uda.*, uif.name, uif.description, uif.param5
                  FROM {user_info_data} uda
                  JOIN {user_info_field} uif ON uda.fieldid = uif.id
                 WHERE uda.userid = :userid
                       AND uif.datatype = :datatype";
        $params = [
            'userid'   => $userid,
            'datatype' => 'hijridate',
        ];

        return $DB->get_records_sql($sql, $params);
    }
}
