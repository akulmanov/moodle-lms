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

admin_externalpage_setup('local_mlangdefaults_mappings');

$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);

$url = $PAGE->url;

// Handle actions.
if ($action === 'delete' && $id) {
    require_sesskey();
    $DB->delete_records('local_mlangdefaults_mappings', ['id' => $id, 'builtin' => 0]);
    redirect($url, get_string('mappingdeleted', 'local_mlangdefaults'));
}

if ($action === 'toggle' && $id) {
    require_sesskey();
    $mapping = $DB->get_record('local_mlangdefaults_mappings', ['id' => $id]);
    if ($mapping) {
        $mapping->enabled = 1 - $mapping->enabled;
        $mapping->timemodified = time();
        $DB->update_record('local_mlangdefaults_mappings', $mapping);
    }
    redirect($url);
}

// Get all mappings.
$mappings = $DB->get_records('local_mlangdefaults_mappings', null, 'priority DESC, id ASC');

echo $OUTPUT->header();

$table = new html_table();
$table->head = [
    get_string('pagepattern', 'local_mlangdefaults'),
    get_string('fieldselector', 'local_mlangdefaults'),
    get_string('fieldtype', 'local_mlangdefaults'),
    get_string('templatekey', 'local_mlangdefaults'),
    get_string('priority', 'local_mlangdefaults'),
    get_string('enabled_mapping', 'local_mlangdefaults'),
    get_string('builtin', 'local_mlangdefaults'),
    '',
];

foreach ($mappings as $mapping) {
    $row = [];
    $row[] = $mapping->pagepattern;
    $row[] = $mapping->fieldselector;
    $row[] = $mapping->fieldtype;
    $row[] = $mapping->templatekey;
    $row[] = $mapping->priority;
    $row[] = $mapping->enabled ? get_string('yes') : get_string('no');

    if ($mapping->builtin) {
        $row[] = get_string('builtin', 'local_mlangdefaults');
        $actions = [];
        $toggleurl = new moodle_url($url, ['action' => 'toggle', 'id' => $mapping->id, 'sesskey' => sesskey()]);
        $actions[] = html_writer::link($toggleurl, $mapping->enabled ? get_string('disable') : get_string('enable'));
        $row[] = implode(' | ', $actions);
    } else {
        $row[] = get_string('custom', 'local_mlangdefaults');
        $actions = [];
        $editurl = new moodle_url('/local/mlangdefaults/editmapping.php', ['id' => $mapping->id]);
        $actions[] = html_writer::link($editurl, get_string('edit'));
        $toggleurl = new moodle_url($url, ['action' => 'toggle', 'id' => $mapping->id, 'sesskey' => sesskey()]);
        $actions[] = html_writer::link($toggleurl, $mapping->enabled ? get_string('disable') : get_string('enable'));
        $deleteurl = new moodle_url($url, ['action' => 'delete', 'id' => $mapping->id, 'sesskey' => sesskey()]);
        $actions[] = html_writer::link($deleteurl, get_string('delete'));
        $row[] = implode(' | ', $actions);
    }

    $table->data[] = $row;
}

echo html_writer::table($table);

$addurl = new moodle_url('/local/mlangdefaults/editmapping.php');
echo html_writer::link($addurl, get_string('addmapping', 'local_mlangdefaults'), ['class' => 'btn btn-primary']);

echo $OUTPUT->footer();

