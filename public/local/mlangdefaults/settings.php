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
require_once(__DIR__ . '/classes/admin_settingspage_tabs.php');

if ($hassiteconfig) {
    // Main settings page with tabs support.
    $settings = new local_mlangdefaults_admin_settingspage_tabs('local_mlangdefaults', get_string('pluginname', 'local_mlangdefaults'));
    $ADMIN->add('localplugins', $settings);

    // Define diagnostics page (hidden from menu but still accessible).
    $diagnosticspage = new admin_externalpage('local_mlangdefaults_diagnostics',
        get_string('diagnostics', 'local_mlangdefaults'),
        new moodle_url('/local/mlangdefaults/diagnostics.php'),
        'local/mlangdefaults:viewdiagnostics',
        true);
    $ADMIN->add('localplugins', $diagnosticspage);

    // Tab 1: General Settings.
    $tabgeneral = new admin_settingpage('local_mlangdefaults_general', get_string('settings', 'local_mlangdefaults'));
    $settings->add_tab($tabgeneral);
    
    // Link to diagnostics page.
    $diagnosticsurl = new moodle_url('/local/mlangdefaults/diagnostics.php');
    $linkshtml = '<a href="' . $diagnosticsurl->out() . '" class="btn btn-secondary">' . 
                 get_string('diagnostics', 'local_mlangdefaults') . '</a>';
    $tabgeneral->add(new admin_setting_description('local_mlangdefaults/links',
        '',
        $linkshtml));

    $tabgeneral->add(new admin_setting_configcheckbox('local_mlangdefaults/enabled',
        get_string('enabled', 'local_mlangdefaults'),
        get_string('enabled_desc', 'local_mlangdefaults'), 1));

    $tabgeneral->add(new admin_setting_configtext('local_mlangdefaults/languages',
        get_string('languages', 'local_mlangdefaults'),
        get_string('languages_desc', 'local_mlangdefaults'), 'kk,ru,en', PARAM_TEXT));

    $tabgeneral->add(new admin_setting_configtext('local_mlangdefaults/fallbacklang',
        get_string('fallbacklang', 'local_mlangdefaults'),
        get_string('fallbacklang_desc', 'local_mlangdefaults'), 'ru', PARAM_TEXT));

    $tabgeneral->add(new admin_setting_configcheckbox('local_mlangdefaults/creationonly',
        get_string('creationonly', 'local_mlangdefaults'),
        get_string('creationonly_desc', 'local_mlangdefaults'), 1));

    $tabgeneral->add(new admin_setting_configcheckbox('local_mlangdefaults/skipifmlangpresent',
        get_string('skipifmlangpresent', 'local_mlangdefaults'),
        get_string('skipifmlangpresent_desc', 'local_mlangdefaults'), 1));

    $tabgeneral->add(new admin_setting_configcheckbox('local_mlangdefaults/showtoast',
        get_string('showtoast', 'local_mlangdefaults'),
        get_string('showtoast_desc', 'local_mlangdefaults'), 1));

    // Tab 2: Course & Section Templates.
    $tabcoursesection = new admin_settingpage('local_mlangdefaults_coursesection', 
        get_string('course', 'core') . ' & ' . get_string('section', 'core'));
    $settings->add_tab($tabcoursesection);

    $tabcoursesection->add(new admin_setting_configtextarea('local_mlangdefaults/template_course_fullname',
        get_string('template_course_fullname', 'local_mlangdefaults'),
        get_string('template_course_fullname_desc', 'local_mlangdefaults'), 'Course title', PARAM_TEXT));

    $tabcoursesection->add(new admin_setting_configtextarea('local_mlangdefaults/template_course_summary',
        get_string('template_course_summary', 'local_mlangdefaults'),
        get_string('template_course_summary_desc', 'local_mlangdefaults'), 'Course description', PARAM_TEXT));

    $tabcoursesection->add(new admin_setting_configtextarea('local_mlangdefaults/template_section_name',
        get_string('template_section_name', 'local_mlangdefaults'),
        get_string('template_section_name_desc', 'local_mlangdefaults'), 'Section title', PARAM_TEXT));

    $tabcoursesection->add(new admin_setting_configtextarea('local_mlangdefaults/template_section_summary',
        get_string('template_section_summary', 'local_mlangdefaults'),
        get_string('template_section_summary_desc', 'local_mlangdefaults'), 'Section description', PARAM_TEXT));

    // Tab 3: Activity Templates (General).
    $tabactivity = new admin_settingpage('local_mlangdefaults_activity', get_string('activities', 'core'));
    $settings->add_tab($tabactivity);

    $tabactivity->add(new admin_setting_heading('local_mlangdefaults/activity_general',
        get_string('general', 'form'),
        get_string('module_templates_desc', 'local_mlangdefaults')));

    $tabactivity->add(new admin_setting_configtextarea('local_mlangdefaults/template_activity_name',
        get_string('template_activity_name', 'local_mlangdefaults'),
        get_string('template_activity_name_desc', 'local_mlangdefaults'), 'Activity title', PARAM_TEXT));

    $tabactivity->add(new admin_setting_configtextarea('local_mlangdefaults/template_activity_intro',
        get_string('template_activity_intro', 'local_mlangdefaults'),
        get_string('template_activity_intro_desc', 'local_mlangdefaults'), 'Activity description', PARAM_TEXT));

    // Module-specific templates.
    $tabactivity->add(new admin_setting_heading('local_mlangdefaults/module_templates',
        get_string('module_templates', 'local_mlangdefaults'),
        ''));

    // Get all installed activity modules.
    $modules = local_mlangdefaults_get_installed_modules();
    foreach ($modules as $modname => $moddisplayname) {
        // Add heading for each module section.
        $tabactivity->add(new admin_setting_heading('local_mlangdefaults/module_' . $modname,
            $moddisplayname,
            ''));

        // Module name template.
        $tabactivity->add(new admin_setting_configtextarea(
            'local_mlangdefaults/template_' . $modname . '_name',
            get_string('template_module_name', 'local_mlangdefaults', $moddisplayname),
            get_string('template_module_name_desc', 'local_mlangdefaults', $moddisplayname),
            '',
            PARAM_TEXT));

        // Module intro template.
        $tabactivity->add(new admin_setting_configtextarea(
            'local_mlangdefaults/template_' . $modname . '_intro',
            get_string('template_module_intro', 'local_mlangdefaults', $moddisplayname),
            get_string('template_module_intro_desc', 'local_mlangdefaults', $moddisplayname),
            '',
            PARAM_TEXT));

        // Assignment-specific activity editor field.
        if ($modname === 'assign') {
            $tabactivity->add(new admin_setting_configtextarea(
                'local_mlangdefaults/template_assign_activityeditor',
                get_string('template_assign_activityeditor', 'local_mlangdefaults'),
                get_string('template_assign_activityeditor_desc', 'local_mlangdefaults'),
                'Activity instructions',
                PARAM_TEXT));
        }
        
        // Page-specific content field.
        if ($modname === 'page') {
            $tabactivity->add(new admin_setting_configtextarea(
                'local_mlangdefaults/template_page_content',
                get_string('template_page_content', 'local_mlangdefaults'),
                get_string('template_page_content_desc', 'local_mlangdefaults'),
                'Page content',
                PARAM_TEXT));
        }
    }

    // Tab 4: Question Templates.
    $tabquestion = new admin_settingpage('local_mlangdefaults_question', get_string('questions', 'question'));
    $settings->add_tab($tabquestion);

    $tabquestion->add(new admin_setting_heading('local_mlangdefaults/question_general',
        get_string('general', 'form'),
        get_string('question_templates_desc', 'local_mlangdefaults')));

    $tabquestion->add(new admin_setting_configtextarea('local_mlangdefaults/template_question_name',
        get_string('template_question_name', 'local_mlangdefaults'),
        get_string('template_question_name_desc', 'local_mlangdefaults'), 'Question name', PARAM_TEXT));

    $tabquestion->add(new admin_setting_configtextarea('local_mlangdefaults/template_question_questiontext',
        get_string('template_question_questiontext', 'local_mlangdefaults'),
        get_string('template_question_questiontext_desc', 'local_mlangdefaults'), 'Question text', PARAM_TEXT));

    $tabquestion->add(new admin_setting_configtextarea('local_mlangdefaults/template_question_generalfeedback',
        get_string('template_question_generalfeedback', 'local_mlangdefaults'),
        get_string('template_question_generalfeedback_desc', 'local_mlangdefaults'), 'General feedback', PARAM_TEXT));

    // Question type-specific templates.
    $tabquestion->add(new admin_setting_heading('local_mlangdefaults/questiontype_templates',
        get_string('question_templates', 'local_mlangdefaults'),
        ''));

    // Get all installed question types.
    $qtypes = local_mlangdefaults_get_installed_question_types();
    foreach ($qtypes as $qtypename => $qtypedisplayname) {
        // Add heading for each question type section.
        $tabquestion->add(new admin_setting_heading('local_mlangdefaults/qtype_' . $qtypename,
            $qtypedisplayname,
            ''));

        // Question type name template.
        $tabquestion->add(new admin_setting_configtextarea(
            'local_mlangdefaults/template_' . $qtypename . '_name',
            get_string('template_questiontype_name', 'local_mlangdefaults', $qtypedisplayname),
            get_string('template_questiontype_name_desc', 'local_mlangdefaults', $qtypedisplayname),
            '',
            PARAM_TEXT));

        // Question type questiontext template.
        $tabquestion->add(new admin_setting_configtextarea(
            'local_mlangdefaults/template_' . $qtypename . '_questiontext',
            get_string('template_questiontype_questiontext', 'local_mlangdefaults', $qtypedisplayname),
            get_string('template_questiontype_questiontext_desc', 'local_mlangdefaults', $qtypedisplayname),
            '',
            PARAM_TEXT));

        // Question type generalfeedback template.
        $tabquestion->add(new admin_setting_configtextarea(
            'local_mlangdefaults/template_' . $qtypename . '_generalfeedback',
            get_string('template_questiontype_generalfeedback', 'local_mlangdefaults', $qtypedisplayname),
            get_string('template_questiontype_generalfeedback_desc', 'local_mlangdefaults', $qtypedisplayname),
            '',
            PARAM_TEXT));
    }
}

