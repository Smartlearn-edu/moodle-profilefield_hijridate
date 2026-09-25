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
 * Event observers definition for profilefield_hijridate.
 *
 * @package    profilefield_hijridate
 * @copyright  2026 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname'   => '\core\event\user_created',
        'callback'    => '\profilefield_hijridate\observer\user_observer::user_created',
        'includefile' => null,
        'priority'    => 100,
        'internal'    => true,
    ],
    [
        'eventname'   => '\core\event\user_updated',
        'callback'    => '\profilefield_hijridate\observer\user_observer::user_updated',
        'includefile' => null,
        'priority'    => 100,
        'internal'    => true,
    ],
];
