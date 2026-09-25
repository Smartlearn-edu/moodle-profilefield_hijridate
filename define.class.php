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
 * Definition of the Hijri date profile field for administration.
 *
 * @package    profilefield_hijridate
 * @copyright  2026 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class profile_define_hijridate
 *
 * Handles field definition and configuration in the Moodle admin interface.
 *
 * @package    profilefield_hijridate
 * @copyright  2026 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_define_hijridate extends profile_define_base {
    /**
     * Defines configuration elements specific to the Hijri date profile field.
     *
     * @param moodleform $form The add/edit field form.
     */
    public function define_form_specific($form) {
        $currenthijriyear = \profilefield_hijridate\helper\umalqura::get_current_hijri_year();
        $defaultearlyyear = 1350;
        $defaultlateyear = $currenthijriyear + 5;

        // Build list of Hijri years (1300 to 1500).
        $minyear = 1300;
        $maxyear = 1500;
        $hijriyears = [];
        for ($y = $minyear; $y <= $maxyear; $y++) {
            $hijriyears[$y] = (string)$y;
        }

        // Param1: Start Year (Earliest year selectable).
        $form->addElement('select', 'param1', get_string('startyear', 'profilefield_hijridate'), $hijriyears);
        $form->setType('param1', PARAM_INT);
        $form->setDefault('param1', $defaultearlyyear);
        $form->addHelpButton('param1', 'startyear', 'profilefield_hijridate');

        // Param2: End Year (Latest year selectable).
        $form->addElement('select', 'param2', get_string('endyear', 'profilefield_hijridate'), $hijriyears);
        $form->setType('param2', PARAM_INT);
        $form->setDefault('param2', $defaultlateyear);
        $form->addHelpButton('param2', 'endyear', 'profilefield_hijridate');

        // Param3: Conversion Mode.
        $modes = [
            0 => get_string('mode_manual', 'profilefield_hijridate'),
            1 => get_string('mode_autoconvert_editable', 'profilefield_hijridate'),
            2 => get_string('mode_autoconvert_locked', 'profilefield_hijridate'),
        ];
        $form->addElement('select', 'param3', get_string('conversionmode', 'profilefield_hijridate'), $modes);
        $form->setType('param3', PARAM_INT);
        $form->setDefault('param3', 0);
        $form->addHelpButton('param3', 'conversionmode', 'profilefield_hijridate');

        // Param4: Source Gregorian Field (Shortname of watching field).
        $form->addElement('text', 'param4', get_string('sourcefield', 'profilefield_hijridate'), 'maxlength="100" size="30"');
        $form->setType('param4', PARAM_ALPHANUMEXT);
        $form->setDefault('param4', '');
        $form->addHelpButton('param4', 'sourcefield', 'profilefield_hijridate');

        // Param5: Display Format on User Profile.
        $displayformats = [
            'both' => get_string('format_both', 'profilefield_hijridate'),
            'iso'  => get_string('format_iso', 'profilefield_hijridate'),
            'text' => get_string('format_text', 'profilefield_hijridate'),
        ];
        $form->addElement('select', 'param5', get_string('displayformat', 'profilefield_hijridate'), $displayformats);
        $form->setType('param5', PARAM_ALPHA);
        $form->setDefault('param5', 'both');
        $form->addHelpButton('param5', 'displayformat', 'profilefield_hijridate');

        // Default Data.
        $form->addElement('text', 'defaultdata', get_string('defaultdata', 'profilefield_hijridate'), 'maxlength="10" size="15"');
        $form->setType('defaultdata', PARAM_TEXT);
        $form->setDefault('defaultdata', '');
        $form->addHelpButton('defaultdata', 'defaultdata', 'profilefield_hijridate');
    }

    /**
     * Validates configuration data specific to the Hijri date field.
     *
     * @param stdClass $data Submitted form data.
     * @param array $files Uploaded files.
     * @return array Associative array of error messages.
     */
    public function define_validate_specific($data, $files) {
        $errors = [];

        // Validate that start year is not greater than end year.
        if (isset($data->param1) && isset($data->param2) && (int)$data->param1 > (int)$data->param2) {
            $errors['param1'] = get_string('startyearafterend', 'profilefield_hijridate');
        }

        // Validate default date if provided.
        if (!empty($data->defaultdata)) {
            $parsed = \profilefield_hijridate\helper\umalqura::parse_hijri_string($data->defaultdata);
            if (!$parsed) {
                $errors['defaultdata'] = get_string('err_invaliddefaultdata', 'profilefield_hijridate');
            } else {
                $startyear = isset($data->param1) ? (int)$data->param1 : 1300;
                $endyear = isset($data->param2) ? (int)$data->param2 : 1500;
                if ($parsed['year'] < $startyear || $parsed['year'] > $endyear) {
                    $errors['defaultdata'] = get_string('err_invaliddefaultdata', 'profilefield_hijridate');
                }
            }
        }

        return $errors;
    }
}
