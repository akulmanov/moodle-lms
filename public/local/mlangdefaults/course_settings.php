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
require_once(__DIR__ . '/classes/course_settings.php');

$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);

require_login($course);
$context = context_course::instance($courseid);
require_capability('local/mlangdefaults:overridecourse', $context);

$url = new moodle_url('/local/mlangdefaults/course_settings.php', ['id' => $courseid]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('courseoverrides', 'local_mlangdefaults'));
$PAGE->set_heading($course->fullname);

$form = new \local_mlangdefaults\course_settings(null, ['courseid' => $courseid]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/course/edit.php', ['id' => $courseid]));
}

if ($data = $form->get_data()) {
    global $DB;

    $override = $DB->get_record('local_mlangdefaults_course_overrides', ['courseid' => $courseid]);
    if (!$override) {
        $override = (object)[
            'courseid' => $courseid,
            'timecreated' => time(),
        ];
        $override->id = $DB->insert_record('local_mlangdefaults_course_overrides', $override);
    }

    $override->usesitedefaults = $data->usesitedefaults ?? 1;
    $override->disabled = $data->disabled ?? 0;
    $override->template_fullname = $data->template_fullname ?? '';
    $override->template_summary = $data->template_summary ?? '';
    $override->template_activityname = $data->template_activity_name ?? '';
    $override->template_activityintro = $data->template_activity_intro ?? '';
    $override->timemodified = time();

    $DB->update_record('local_mlangdefaults_course_overrides', $override);

    redirect(new moodle_url('/course/edit.php', ['id' => $courseid]),
        get_string('overridesaved', 'local_mlangdefaults'));
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();

