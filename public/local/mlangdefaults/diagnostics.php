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
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/lib.php');

global $PAGE, $OUTPUT, $DB;

admin_externalpage_setup('local_mlangdefaults_diagnostics');

echo $OUTPUT->header();

// Check filter_multilang2.
echo html_writer::tag('h3', get_string('filtercheck', 'local_mlangdefaults'));

$filtersettings = local_mlangdefaults_check_filter_settings();
$filterenabled = local_mlangdefaults_is_filter_enabled();

if ($filterenabled && $filtersettings['content'] && $filtersettings['headings']) {
    echo html_writer::div(get_string('filterenabled', 'local_mlangdefaults'), 'alert alert-success');
} else {
    echo html_writer::div(get_string('filterdisabled', 'local_mlangdefaults'), 'alert alert-warning');
    echo html_writer::div(get_string('filterwarning', 'local_mlangdefaults'), 'alert alert-info');
    $filterurl = new moodle_url('/admin/filters.php');
    echo html_writer::link($filterurl, get_string('enablefilter', 'local_mlangdefaults'), ['class' => 'btn btn-primary']);
}

// Recent injections.
echo html_writer::tag('h3', get_string('recentinjections', 'local_mlangdefaults'));

$logs = $DB->get_records('local_mlangdefaults_logs', null, 'timecreated DESC', '*', 0, 50);

if (empty($logs)) {
    echo html_writer::div(get_string('noinjections', 'local_mlangdefaults'), 'alert alert-info');
} else {
    $table = new html_table();
    $table->head = [
        get_string('pagetype', 'local_mlangdefaults'),
        get_string('fieldname', 'local_mlangdefaults'),
        get_string('user', 'local_mlangdefaults'),
        get_string('course', 'local_mlangdefaults'),
        get_string('moduletype', 'local_mlangdefaults'),
        get_string('time', 'local_mlangdefaults'),
    ];

    foreach ($logs as $log) {
        $user = $DB->get_record('user', ['id' => $log->userid]);
        $coursename = '';
        if ($log->courseid) {
            $course = $DB->get_record('course', ['id' => $log->courseid]);
            if ($course) {
                $coursename = $course->shortname;
            }
        }

        $table->data[] = [
            $log->pagetype,
            $log->fieldname,
            $user ? fullname($user) : '-',
            $coursename ?: '-',
            $log->moduletype ?: '-',
            userdate($log->timecreated),
        ];
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();

