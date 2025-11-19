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

namespace local_mlangdefaults;

defined('MOODLE_INTERNAL') || die();

/**
 * Course settings form for multi-language defaults overrides.
 */
class course_settings extends \core_form\moodleform {
    /**
     * Form definition.
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $courseid = $this->_customdata['courseid'] ?? 0;

        $mform->addElement('header', 'mlangdefaults', get_string('courseoverrides', 'local_mlangdefaults'));

        $mform->addElement('advcheckbox', 'usesitedefaults',
            get_string('usesitedefaults', 'local_mlangdefaults'),
            '', [], [0, 1]);

        $mform->addElement('advcheckbox', 'disabled',
            get_string('disabledincourse', 'local_mlangdefaults'),
            '', [], [0, 1]);

        $mform->addElement('textarea', 'template_fullname',
            get_string('template_course_fullname_override', 'local_mlangdefaults'),
            ['rows' => 3, 'cols' => 60]);

        $mform->addElement('textarea', 'template_summary',
            get_string('template_course_summary_override', 'local_mlangdefaults'),
            ['rows' => 3, 'cols' => 60]);

        $mform->addElement('textarea', 'template_activity_name',
            get_string('template_activity_name_override', 'local_mlangdefaults'),
            ['rows' => 3, 'cols' => 60]);

        $mform->addElement('textarea', 'template_activity_intro',
            get_string('template_activity_intro_override', 'local_mlangdefaults'),
            ['rows' => 3, 'cols' => 60]);

        // Load existing data.
        if ($courseid) {
            $override = $DB->get_record('local_mlangdefaults_course_overrides', ['courseid' => $courseid]);
            if ($override) {
                $mform->setDefault('usesitedefaults', $override->usesitedefaults);
                $mform->setDefault('disabled', $override->disabled);
                $mform->setDefault('template_fullname', $override->template_fullname);
                $mform->setDefault('template_summary', $override->template_summary);
                $mform->setDefault('template_activity_name', $override->template_activityname);
                $mform->setDefault('template_activity_intro', $override->template_activityintro);
            }
        }

        $this->add_action_buttons();
    }

    /**
     * Form validation.
     *
     * @param array $data Form data
     * @param array $files Files
     * @return array Errors
     */
    public function validation($data, $files) {
        $errors = [];
        return $errors;
    }
}

