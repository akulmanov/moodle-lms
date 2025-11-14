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
 * Extend user navigation with a restart link for the onboarding tour.
 *
 * @param navigation_node $navigation
 * @param stdClass $user
 * @param stdClass $course
 * @return void
 */
function local_onboarding_extend_navigation_user(navigation_node $navigation, stdClass $user, stdClass $course) {
    global $USER, $CFG;

    if (!isloggedin() || isguestuser() || (int)$user->id !== (int)$USER->id) {
        return;
    }

    $tourid = (int)get_config('local_onboarding', 'tourid');
    if ($tourid <= 0) {
        return;
    }

    require_once($CFG->dirroot . '/lib/navigationlib.php');

    $restarturl = new moodle_url('/local/onboarding/reset.php', ['sesskey' => sesskey()]);
    $navigation->add(
        navigation_node::create(
            get_string('restarttour', 'local_onboarding'),
            $restarturl,
            navigation_node::NODETYPE_LEAF,
            null,
            'local_onboarding_restart',
            new pix_icon('i/reload', '')
        )
    );
}

