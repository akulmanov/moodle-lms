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

defined('MOODLE_INTERNAL') || die();

/**
 * Installation hook to provision the onboarding user tour.
 *
 * @return bool
 * @throws moodle_exception
 */
function xmldb_local_onboarding_install(): bool {
    global $CFG;

    require_once($CFG->dirroot . '/local/onboarding/classes/tour_manager.php');

    \local_onboarding\tour_manager::ensure_tour_exists();

    return true;
}


