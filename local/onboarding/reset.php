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

require_once(__DIR__ . '/../../config.php');

require_login();
require_sesskey();

$tourid = (int)get_config('local_onboarding', 'tourid');
$nexturl = new moodle_url('/my/');

if ($tourid <= 0) {
    redirect($nexturl, get_string('tourmissing', 'local_onboarding'), 5, \core\output\notification::NOTIFY_WARNING);
}

try {
    $tour = \tool_usertours\helper::get_tour($tourid);
    $tour->request_user_reset();
    redirect($nexturl, get_string('tourresetcomplete', 'local_onboarding'), 2, \core\output\notification::NOTIFY_SUCCESS);
} catch (\moodle_exception $exception) {
    redirect($nexturl, get_string('tourmissing', 'local_onboarding'), 5, \core\output\notification::NOTIFY_ERROR);
}

