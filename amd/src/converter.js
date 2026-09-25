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
 * Client-side auto-converter from Gregorian date to Umm al-Qura Hijri date.
 *
 * @module     profilefield_hijridate/converter
 * @copyright  2026 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    'use strict';

    /**
     * Converts a Gregorian year, month, and day into Hijri Umm al-Qura components using Intl.
     *
     * @param {number} year Gregorian full year.
     * @param {number} month Gregorian month (1-12).
     * @param {number} day Gregorian day of month (1-31).
     * @returns {Object|null} Hijri year, month, and day object or null on failure.
     */
    function convertGregorianToHijri(year, month, day) {
        if (!year || !month || !day) {
            return null;
        }

        try {
            var date = new Date(Date.UTC(year, month - 1, day, 12, 0, 0));
            if (isNaN(date.getTime())) {
                return null;
            }

            var formatter = new Intl.DateTimeFormat('en-u-ca-islamic-umalqura', {
                year: 'numeric',
                month: 'numeric',
                day: 'numeric',
                timeZone: 'UTC'
            });

            var parts = formatter.formatToParts(date);
            var result = {};

            parts.forEach(function(part) {
                if (part.type === 'year') {
                    result.year = parseInt(part.value, 10);
                } else if (part.type === 'month') {
                    result.month = parseInt(part.value, 10);
                } else if (part.type === 'day') {
                    result.day = parseInt(part.value, 10);
                }
            });

            if (result.year && result.month && result.day) {
                return result;
            }
        } catch (e) {
            // Intl calendar unsupported or invalid date.
            return null;
        }

        return null;
    }

    /**
     * Finds form elements by possible name variations.
     *
     * @param {string} baseName Field shortname.
     * @returns {Object} Found DOM elements.
     */
    function locateSourceElements(baseName) {
        var prefixes = ['profile_field_' + baseName, baseName];

        for (var i = 0; i < prefixes.length; i++) {
            var prefix = prefixes[i];
            var dayEl = document.querySelector('select[name="' + prefix + '[day]"]');
            var monthEl = document.querySelector('select[name="' + prefix + '[month]"]');
            var yearEl = document.querySelector('select[name="' + prefix + '[year]"]');

            if (dayEl && monthEl && yearEl) {
                return {
                    type: 'selects',
                    day: dayEl,
                    month: monthEl,
                    year: yearEl
                };
            }

            var inputEl = document.querySelector('input[name="' + prefix + '"]');
            if (inputEl) {
                return {
                    type: 'input',
                    input: inputEl
                };
            }
        }

        return null;
    }

    /**
     * Initializes the client-side converter listener.
     *
     * @param {Object} config Configuration containing sourceField, targetField, and mode.
     */
    function init(config) {
        if (!config || !config.sourceField || !config.targetField) {
            return;
        }

        var targetPrefix = config.targetField;
        var targetDay = document.querySelector('select[name="' + targetPrefix + '[day]"]');
        var targetMonth = document.querySelector('select[name="' + targetPrefix + '[month]"]');
        var targetYear = document.querySelector('select[name="' + targetPrefix + '[year]"]');

        if (!targetDay || !targetMonth || !targetYear) {
            return;
        }

        var source = locateSourceElements(config.sourceField);
        if (!source) {
            return;
        }

        /**
         * Reads the Gregorian value and updates the Hijri fields.
         */
        function updateHijri() {
            var gYear = 0;
            var gMonth = 0;
            var gDay = 0;

            if (source.type === 'selects') {
                gDay = parseInt(source.day.value, 10);
                gMonth = parseInt(source.month.value, 10);
                gYear = parseInt(source.year.value, 10);
            } else if (source.type === 'input') {
                var val = source.input.value.trim();
                var match = val.match(/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})/);
                if (match) {
                    gYear = parseInt(match[1], 10);
                    gMonth = parseInt(match[2], 10);
                    gDay = parseInt(match[3], 10);
                }
            }

            if (!gYear || !gMonth || !gDay) {
                return;
            }

            var hijri = convertGregorianToHijri(gYear, gMonth, gDay);
            if (!hijri) {
                return;
            }

            // In Mode 1 (editable override), don't overwrite if user has already modified the values manually
            // unless the source field was just changed.
            targetDay.value = hijri.day;
            targetMonth.value = hijri.month;
            targetYear.value = hijri.year;

            // Trigger change event for any dependent UI.
            targetDay.dispatchEvent(new Event('change', {bubbles: true}));
            targetMonth.dispatchEvent(new Event('change', {bubbles: true}));
            targetYear.dispatchEvent(new Event('change', {bubbles: true}));
        }

        if (source.type === 'selects') {
            source.day.addEventListener('change', updateHijri);
            source.month.addEventListener('change', updateHijri);
            source.year.addEventListener('change', updateHijri);
        } else if (source.type === 'input') {
            source.input.addEventListener('change', updateHijri);
            source.input.addEventListener('input', updateHijri);
        }

        // Auto-populate on load if target is empty and source has a value.
        var targetEmpty = (!targetDay.value || targetDay.value === '0') &&
                          (!targetMonth.value || targetMonth.value === '0') &&
                          (!targetYear.value || targetYear.value === '0');

        if (targetEmpty || config.mode === 2) {
            updateHijri();
        }
    }

    return {
        init: init
    };
});
