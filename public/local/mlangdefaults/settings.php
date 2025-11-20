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

require_once(__DIR__ . '/lib.php');

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_mlangdefaults', get_string('pluginname', 'local_mlangdefaults'));
    $ADMIN->add('localplugins', $settings);

    // Define external pages (required for the pages to work) but hide them from menu.
    $mappingspage = new admin_externalpage('local_mlangdefaults_mappings',
        get_string('fieldmappings', 'local_mlangdefaults'),
        new moodle_url('/local/mlangdefaults/mappings.php'),
        'local/mlangdefaults:manage',
        true); // Hidden from menu but still accessible.
    $ADMIN->add('localplugins', $mappingspage);
    
    $diagnosticspage = new admin_externalpage('local_mlangdefaults_diagnostics',
        get_string('diagnostics', 'local_mlangdefaults'),
        new moodle_url('/local/mlangdefaults/diagnostics.php'),
        'local/mlangdefaults:viewdiagnostics',
        true); // Hidden from menu but still accessible.
    $ADMIN->add('localplugins', $diagnosticspage);

    // Links to additional pages inside the settings page.
    $mappingsurl = new moodle_url('/local/mlangdefaults/mappings.php');
    $diagnosticsurl = new moodle_url('/local/mlangdefaults/diagnostics.php');
    $linkshtml = '<a href="' . $mappingsurl->out() . '" class="btn btn-secondary">' . 
                 get_string('fieldmappings', 'local_mlangdefaults') . '</a> ' .
                 '<a href="' . $diagnosticsurl->out() . '" class="btn btn-secondary">' . 
                 get_string('diagnostics', 'local_mlangdefaults') . '</a>';
    $settings->add(new admin_setting_description('local_mlangdefaults/links',
        '',
        $linkshtml));

    // General settings.
    $settings->add(new admin_setting_heading('local_mlangdefaults/general',
        get_string('settings', 'local_mlangdefaults'), ''));

    $settings->add(new admin_setting_configcheckbox('local_mlangdefaults/enabled',
        get_string('enabled', 'local_mlangdefaults'),
        get_string('enabled_desc', 'local_mlangdefaults'), 1));

    $settings->add(new admin_setting_configtext('local_mlangdefaults/languages',
        get_string('languages', 'local_mlangdefaults'),
        get_string('languages_desc', 'local_mlangdefaults'), 'kk,ru,en', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_mlangdefaults/fallbacklang',
        get_string('fallbacklang', 'local_mlangdefaults'),
        get_string('fallbacklang_desc', 'local_mlangdefaults'), 'ru', PARAM_TEXT));

    $settings->add(new admin_setting_configcheckbox('local_mlangdefaults/creationonly',
        get_string('creationonly', 'local_mlangdefaults'),
        get_string('creationonly_desc', 'local_mlangdefaults'), 1));

    $settings->add(new admin_setting_configcheckbox('local_mlangdefaults/skipifmlangpresent',
        get_string('skipifmlangpresent', 'local_mlangdefaults'),
        get_string('skipifmlangpresent_desc', 'local_mlangdefaults'), 1));

    $settings->add(new admin_setting_configcheckbox('local_mlangdefaults/showtoast',
        get_string('showtoast', 'local_mlangdefaults'),
        get_string('showtoast_desc', 'local_mlangdefaults'), 1));

    // Templates.
    $settings->add(new admin_setting_heading('local_mlangdefaults/templates',
        get_string('templates', 'local_mlangdefaults'), ''));

    $settings->add(new admin_setting_configtextarea('local_mlangdefaults/template_course_fullname',
        get_string('template_course_fullname', 'local_mlangdefaults'),
        get_string('template_course_fullname_desc', 'local_mlangdefaults'), 'Course title', PARAM_TEXT));

    $settings->add(new admin_setting_configtextarea('local_mlangdefaults/template_course_summary',
        get_string('template_course_summary', 'local_mlangdefaults'),
        get_string('template_course_summary_desc', 'local_mlangdefaults'), 'Course description', PARAM_TEXT));

    $settings->add(new admin_setting_configtextarea('local_mlangdefaults/template_section_name',
        get_string('template_section_name', 'local_mlangdefaults'),
        get_string('template_section_name_desc', 'local_mlangdefaults'), 'Section title', PARAM_TEXT));

    $settings->add(new admin_setting_configtextarea('local_mlangdefaults/template_section_summary',
        get_string('template_section_summary', 'local_mlangdefaults'),
        get_string('template_section_summary_desc', 'local_mlangdefaults'), 'Section description', PARAM_TEXT));

    $settings->add(new admin_setting_configtextarea('local_mlangdefaults/template_activity_name',
        get_string('template_activity_name', 'local_mlangdefaults'),
        get_string('template_activity_name_desc', 'local_mlangdefaults'), 'Activity title', PARAM_TEXT));

    $settings->add(new admin_setting_configtextarea('local_mlangdefaults/template_activity_intro',
        get_string('template_activity_intro', 'local_mlangdefaults'),
        get_string('template_activity_intro_desc', 'local_mlangdefaults'), 'Activity description', PARAM_TEXT));
}

