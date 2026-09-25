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
 * Hijri date profile field implementation class.
 *
 * @package    profilefield_hijridate
 * @copyright  2026 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class profile_field_hijridate
 *
 * Handles displaying, editing, validating, and saving Hijri dates in user profiles.
 *
 * @package    profilefield_hijridate
 * @copyright  2026 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_field_hijridate extends profile_field_base {
    /**
     * Adds the Hijri date dropdown selector elements to the profile editing form.
     *
     * @param moodleform $mform The Moodle form instance.
     */
    public function edit_field_add($mform) {
        global $PAGE;

        $startyear = !empty($this->field->param1) ? (int)$this->field->param1 : 1350;
        $endyear = !empty($this->field->param2) ? (int)$this->field->param2 : 1470;

        // Day choices.
        $dayoptions = ['' => get_string('day', 'profilefield_hijridate')];
        for ($d = 1; $d <= 30; $d++) {
            $dayoptions[$d] = (string)$d;
        }

        // Month choices.
        $monthoptions = ['' => get_string('month', 'profilefield_hijridate')];
        $months = \profilefield_hijridate\helper\umalqura::get_months();
        foreach ($months as $m => $mname) {
            $monthoptions[$m] = $mname;
        }

        // Year choices.
        $yearoptions = ['' => get_string('year', 'profilefield_hijridate')];
        for ($y = $startyear; $y <= $endyear; $y++) {
            $yearoptions[$y] = (string)$y;
        }

        $elements = [];
        $elements[] = $mform->createElement('select', 'day', get_string('day', 'profilefield_hijridate'), $dayoptions);
        $elements[] = $mform->createElement('select', 'month', get_string('month', 'profilefield_hijridate'), $monthoptions);
        $elements[] = $mform->createElement('select', 'year', get_string('year', 'profilefield_hijridate'), $yearoptions);

        $mform->addGroup($elements, $this->inputname, format_string($this->field->name), ' ', true);

        // If auto-convert is enabled, load AMD module to auto-populate from Gregorian field.
        $conversionmode = isset($this->field->param3) ? (int)$this->field->param3 : 0;
        $sourcefield = !empty($this->field->param4) ? trim($this->field->param4) : '';

        if (!empty($sourcefield) && ($conversionmode === 1 || $conversionmode === 2)) {
            $PAGE->requires->js_call_amd('profilefield_hijridate/converter', 'init', [
                'sourceField' => $sourcefield,
                'targetField' => $this->inputname,
                'mode'        => $conversionmode,
            ]);
        }
    }

    /**
     * Sets the default value for the field in the form.
     *
     * @param moodleform $mform The Moodle form instance.
     */
    public function edit_field_set_default($mform) {
        if (!empty($this->field->defaultdata)) {
            $parsed = \profilefield_hijridate\helper\umalqura::parse_hijri_string($this->field->defaultdata);
            if ($parsed) {
                $mform->setDefault($this->inputname, [
                    'day'   => $parsed['day'],
                    'month' => $parsed['month'],
                    'year'  => $parsed['year'],
                ]);
            }
        }
    }

    /**
     * Loads saved user data into the user object prepared for form presentation.
     *
     * @param stdClass $user The user object being prepared for the form.
     */
    public function edit_load_user_data($user) {
        if (!empty($this->data)) {
            $parsed = \profilefield_hijridate\helper\umalqura::parse_hijri_string($this->data);
            if ($parsed) {
                $user->{$this->inputname} = [
                    'day'   => $parsed['day'],
                    'month' => $parsed['month'],
                    'year'  => $parsed['year'],
                ];
                return;
            }
        }
        parent::edit_load_user_data($user);
    }

    /**
     * Freezes the field if locked or configured in strictly derived mode (param3 = 2).
     *
     * @param moodleform $mform The Moodle form instance.
     */
    public function edit_field_set_locked($mform) {
        if (!$mform->elementExists($this->inputname)) {
            return;
        }

        $conversionmode = isset($this->field->param3) ? (int)$this->field->param3 : 0;
        $strictlyderived = ($conversionmode === 2);
        $userlocked = $this->is_locked() && !has_capability('moodle/user:update', \context_system::instance());

        if ($strictlyderived || $userlocked) {
            $mform->hardFreeze($this->inputname);
            if (!empty($this->data)) {
                $parsed = \profilefield_hijridate\helper\umalqura::parse_hijri_string($this->data);
                if ($parsed) {
                    $mform->setConstant($this->inputname, [
                        'day'   => $parsed['day'],
                        'month' => $parsed['month'],
                        'year'  => $parsed['year'],
                    ]);
                }
            }
        }
    }

    /**
     * Preprocesses the field data before saving to the database.
     *
     * Transforms the form array or string into canonical YYYY-MM-DD format.
     *
     * @param mixed $data Form data submitted for this field.
     * @param stdClass $datarecord The record object that will be saved.
     * @return string Canonical YYYY-MM-DD string, or empty string.
     */
    public function edit_save_data_preprocess($data, $datarecord) {
        if (is_array($data)) {
            $day = !empty($data['day']) ? (int)$data['day'] : 0;
            $month = !empty($data['month']) ? (int)$data['month'] : 0;
            $year = !empty($data['year']) ? (int)$data['year'] : 0;

            if ($day > 0 && $month > 0 && $year > 0) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
            return '';
        }

        if (is_string($data) && !empty($data)) {
            $parsed = \profilefield_hijridate\helper\umalqura::parse_hijri_string($data);
            if ($parsed) {
                return sprintf('%04d-%02d-%02d', $parsed['year'], $parsed['month'], $parsed['day']);
            }
            return '';
        }

        return '';
    }

    /**
     * Validates the submitted form data for this field.
     *
     * @param stdClass $usernew Data submitted from profile form.
     * @return array Associative array of error messages.
     */
    public function edit_validate_field($usernew) {
        $errors = [];

        $val = isset($usernew->{$this->inputname}) ? $usernew->{$this->inputname} : null;
        $day = (is_array($val) && !empty($val['day'])) ? (int)$val['day'] : 0;
        $month = (is_array($val) && !empty($val['month'])) ? (int)$val['month'] : 0;
        $year = (is_array($val) && !empty($val['year'])) ? (int)$val['year'] : 0;

        $isanyfilled = ($day > 0 || $month > 0 || $year > 0);
        $isallfilled = ($day > 0 && $month > 0 && $year > 0);

        if ($this->is_required() && !$isallfilled) {
            $errors[$this->inputname] = get_string('err_requireddate', 'profilefield_hijridate');
            return $errors;
        }

        if ($isanyfilled && !$isallfilled) {
            $errors[$this->inputname] = get_string('err_incompletedate', 'profilefield_hijridate');
            return $errors;
        }

        if ($isallfilled) {
            $startyear = !empty($this->field->param1) ? (int)$this->field->param1 : 1300;
            $endyear = !empty($this->field->param2) ? (int)$this->field->param2 : 1500;

            if (!\profilefield_hijridate\helper\umalqura::validate_hijri_date($year, $month, $day, $startyear, $endyear)) {
                $errors[$this->inputname] = get_string('err_invaliddate', 'profilefield_hijridate');
            }
        }

        return $errors;
    }

    /**
     * Checks if the field is empty in submitted form data.
     *
     * @param stdClass $usernew The submitted data.
     * @return bool True if empty.
     */
    public function is_empty($usernew) {
        if (!isset($usernew->{$this->inputname})) {
            return true;
        }
        $val = $usernew->{$this->inputname};
        if (is_array($val)) {
            return empty($val['day']) || empty($val['month']) || empty($val['year']);
        }
        return empty($val);
    }

    /**
     * Displays the formatted data for this field in the user profile.
     *
     * @return string Formatted string according to configured displayformat (param5).
     */
    public function display_data() {
        if (empty($this->data)) {
            return get_string('notset', 'profilefield_hijridate');
        }

        $displayformat = !empty($this->field->param5) ? $this->field->param5 : 'both';
        return \profilefield_hijridate\helper\umalqura::format_hijri($this->data, $displayformat);
    }
}
