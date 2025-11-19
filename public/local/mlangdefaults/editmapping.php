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
require_once($CFG->libdir . '/formslib.php');
require_once(__DIR__ . '/lib.php');

global $PAGE, $OUTPUT, $DB;

admin_externalpage_setup('local_mlangdefaults_mappings');

$id = optional_param('id', 0, PARAM_INT);
$url = new moodle_url('/local/mlangdefaults/editmapping.php', ['id' => $id]);
$returnurl = new moodle_url('/local/mlangdefaults/mappings.php');

$PAGE->set_url($url);
$PAGE->set_title($id ? get_string('editmapping', 'local_mlangdefaults') : get_string('addmapping', 'local_mlangdefaults'));
$PAGE->set_heading($id ? get_string('editmapping', 'local_mlangdefaults') : get_string('addmapping', 'local_mlangdefaults'));

// Get existing mapping if editing.
$mapping = null;
if ($id) {
    $mapping = $DB->get_record('local_mlangdefaults_mappings', ['id' => $id, 'builtin' => 0], '*', MUST_EXIST);
}

// Define form.
class local_mlangdefaults_editmapping_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $mapping = $this->_customdata['mapping'] ?? null;
        $isedit = $this->_customdata['is_edit'] ?? false;

        $mform->addElement('text', 'pagepattern', get_string('pagepattern', 'local_mlangdefaults'), ['size' => 60]);
        $mform->setType('pagepattern', PARAM_TEXT);
        $mform->addRule('pagepattern', null, 'required');
        $mform->addHelpButton('pagepattern', 'pagepattern', 'local_mlangdefaults');

        $mform->addElement('text', 'fieldselector', get_string('fieldselector', 'local_mlangdefaults'), ['size' => 60]);
        $mform->setType('fieldselector', PARAM_TEXT);
        $mform->addRule('fieldselector', null, 'required');
        $mform->addHelpButton('fieldselector', 'fieldselector', 'local_mlangdefaults');

        $fieldtypes = ['text' => get_string('text'), 'editor' => get_string('editor', 'editor')];
        $mform->addElement('select', 'fieldtype', get_string('fieldtype', 'local_mlangdefaults'), $fieldtypes);
        $mform->setDefault('fieldtype', 'text');

        $mform->addElement('text', 'templatekey', get_string('templatekey', 'local_mlangdefaults'), ['size' => 60]);
        $mform->setType('templatekey', PARAM_TEXT);
        $mform->addHelpButton('templatekey', 'templatekey', 'local_mlangdefaults');

        $mform->addElement('text', 'priority', get_string('priority', 'local_mlangdefaults'), ['size' => 10]);
        $mform->setType('priority', PARAM_INT);
        $mform->setDefault('priority', 100);
        $mform->addHelpButton('priority', 'priority', 'local_mlangdefaults');

        $mform->addElement('advcheckbox', 'enabled', get_string('enabled_mapping', 'local_mlangdefaults'));
        $mform->setDefault('enabled', 1);

        if ($mapping) {
            $mform->setDefaults([
                'pagepattern' => $mapping->pagepattern,
                'fieldselector' => $mapping->fieldselector,
                'fieldtype' => $mapping->fieldtype,
                'templatekey' => $mapping->templatekey,
                'priority' => $mapping->priority,
                'enabled' => $mapping->enabled,
            ]);
        }

        $this->add_action_buttons(true, $isedit ? get_string('savechanges') : get_string('add'));
    }
}

$form = new local_mlangdefaults_editmapping_form($url->out(false), ['mapping' => $mapping, 'is_edit' => (bool)$id]);

if ($form->is_cancelled()) {
    redirect($returnurl);
}

if ($data = $form->get_data()) {
    if ($id) {
        $mapping->pagepattern = $data->pagepattern;
        $mapping->fieldselector = $data->fieldselector;
        $mapping->fieldtype = $data->fieldtype;
        $mapping->templatekey = $data->templatekey;
        $mapping->priority = $data->priority;
        $mapping->enabled = $data->enabled;
        $mapping->timemodified = time();
        $DB->update_record('local_mlangdefaults_mappings', $mapping);
    } else {
        $mapping = (object)[
            'pagepattern' => $data->pagepattern,
            'fieldselector' => $data->fieldselector,
            'fieldtype' => $data->fieldtype,
            'templatekey' => $data->templatekey,
            'priority' => $data->priority,
            'enabled' => $data->enabled,
            'builtin' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ];
        $DB->insert_record('local_mlangdefaults_mappings', $mapping);
    }
    redirect($returnurl, get_string('mappingsaved', 'local_mlangdefaults'));
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();

