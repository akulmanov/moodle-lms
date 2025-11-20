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

$string['pluginname'] = 'Multi-language Defaults';
$string['privacy:metadata'] = 'The Multi-language Defaults plugin does not store any personal data.';

// Capabilities.
$string['mlangdefaults:manage'] = 'Manage multi-language defaults settings';
$string['mlangdefaults:overridecourse'] = 'Override multi-language defaults for courses';
$string['mlangdefaults:viewdiagnostics'] = 'View multi-language defaults diagnostics';

// Settings.
$string['settings'] = 'Settings';
$string['enabled'] = 'Enable plugin';
$string['enabled_desc'] = 'Enable automatic insertion of multi-language placeholders';
$string['languages'] = 'Languages';
$string['languages_desc'] = 'Comma-separated list of language codes in order (e.g., kk,ru,en)';
$string['fallbacklang'] = 'Fallback language';
$string['fallbacklang_desc'] = 'Language code to use as fallback (default: ru)';
$string['creationonly'] = 'Creation pages only';
$string['creationonly_desc'] = 'Only inject defaults on creation pages, never on edit pages';
$string['skipifmlangpresent'] = 'Skip if {mlang} already present';
$string['skipifmlangpresent_desc'] = 'Do not inject if the field already contains {mlang} tags';
$string['showtoast'] = 'Show notification after insertion';
$string['showtoast_desc'] = 'Display a toast notification when defaults are inserted';

// Templates.
$string['templates'] = 'Templates';
$string['template_course_fullname'] = 'Course fullname template';
$string['template_course_fullname_desc'] = 'Template for course fullname field';
$string['template_course_summary'] = 'Course summary template';
$string['template_course_summary_desc'] = 'Template for course summary field';
$string['template_section_name'] = 'Section name template';
$string['template_section_name_desc'] = 'Template for section name field';
$string['template_section_summary'] = 'Section summary template';
$string['template_section_summary_desc'] = 'Template for section summary field';
$string['template_activity_name'] = 'Activity name template';
$string['template_activity_name_desc'] = 'Template for activity name field';
$string['template_activity_intro'] = 'Activity intro template';
$string['template_activity_intro_desc'] = 'Template for activity intro field';
$string['template_assign_activityeditor'] = 'Assignment activity editor template';
$string['template_assign_activityeditor_desc'] = 'Template for assignment activity instructions field';
$string['template_page_content'] = 'Page content template';
$string['template_page_content_desc'] = 'Template for page content field';
$string['template_question_name'] = 'Question name template';
$string['template_question_name_desc'] = 'Template for question name field';
$string['template_question_questiontext'] = 'Question text template';
$string['template_question_questiontext_desc'] = 'Template for question text field';
$string['template_question_generalfeedback'] = 'Question general feedback template';
$string['template_question_generalfeedback_desc'] = 'Template for question general feedback field';

// Module-specific templates.
$string['module_templates'] = 'Module-specific templates';
$string['module_templates_desc'] = 'Templates specific to each activity module type. If not set, the general activity templates above will be used.';
$string['template_module_name'] = '{$a} name template';
$string['template_module_name_desc'] = 'Template for {$a} name field (overrides general activity name template)';
$string['template_module_intro'] = '{$a} intro template';
$string['template_module_intro_desc'] = 'Template for {$a} intro field (overrides general activity intro template)';

// Question type-specific templates.
$string['question_templates'] = 'Question templates';
$string['question_templates_desc'] = 'Templates for question bank. If not set, the general question templates above will be used.';
$string['template_questiontype_name'] = '{$a} name template';
$string['template_questiontype_name_desc'] = 'Template for {$a} question name field (overrides general question name template)';
$string['template_questiontype_questiontext'] = '{$a} question text template';
$string['template_questiontype_questiontext_desc'] = 'Template for {$a} question text field (overrides general question text template)';
$string['template_questiontype_generalfeedback'] = '{$a} general feedback template';
$string['template_questiontype_generalfeedback_desc'] = 'Template for {$a} general feedback field (overrides general feedback template)';

// Field mappings.
$string['fieldmappings'] = 'Field Mappings';
$string['fieldmappings_desc'] = 'Configure which fields on which pages should receive multi-language defaults';
$string['addmapping'] = 'Add custom mapping';
$string['editmapping'] = 'Edit mapping';
$string['deletemapping'] = 'Delete mapping';
$string['pagepattern'] = 'Page pattern';
$string['pagepattern_desc'] = 'Regular expression pattern to match page URLs (e.g., /course/edit\.php)';
$string['fieldselector'] = 'Field selector';
$string['fieldselector_desc'] = 'CSS selector or field ID (e.g., id_fullname)';
$string['fieldtype'] = 'Field type';
$string['fieldtype_desc'] = 'Type of field: text or editor';
$string['templatekey'] = 'Template key';
$string['templatekey_desc'] = 'Key to identify which template to use';
$string['priority'] = 'Priority';
$string['priority_desc'] = 'Higher priority mappings are applied first';
$string['enabled_mapping'] = 'Enabled';
$string['builtin'] = 'Built-in';
$string['custom'] = 'Custom';

// Help strings for form fields.
$string['pagepattern_help'] = 'Regular expression pattern to match page URLs. For example: /course/edit\.php matches the course edit page. Use \. to match a literal dot.';
$string['fieldselector_help'] = 'CSS selector or field ID to target. For example: id_fullname matches the field with id="fullname". For editors, use the editor ID like id_summary_editor.';
$string['templatekey_help'] = 'Optional key to identify which template to use. If empty, the system will try to infer the template from the field name. Examples: course_fullname, course_summary, section_name, activity_name, activity_intro.';
$string['priority_help'] = 'Higher priority mappings are applied first when multiple mappings match the same field. Default is 100.';

// Module overrides.
$string['moduleoverrides'] = 'Module Type Overrides';
$string['moduleoverrides_desc'] = 'Configure templates specific to activity module types';
$string['module'] = 'Module';
$string['enablemodule'] = 'Enable for this module';
$string['template_module_name'] = 'Name template';
$string['template_module_intro'] = 'Intro template';

// Category overrides.
$string['categoryoverrides'] = 'Category Overrides';
$string['categoryoverrides_desc'] = 'Configure templates for courses in specific categories';
$string['category'] = 'Category';
$string['addcategoryoverride'] = 'Add category override';
$string['deletecategoryoverride'] = 'Delete category override';

// Course overrides.
$string['courseoverrides'] = 'Course Overrides';
$string['courseoverrides_desc'] = 'Override templates for this specific course';
$string['usesitedefaults'] = 'Use site defaults';
$string['disabledincourse'] = 'Disable in this course';
$string['template_course_fullname_override'] = 'Course fullname template (override)';
$string['template_course_summary_override'] = 'Course summary template (override)';
$string['template_activity_name_override'] = 'Activity name template (override)';
$string['template_activity_intro_override'] = 'Activity intro template (override)';

// Diagnostics.
$string['diagnostics'] = 'Diagnostics';
$string['filtercheck'] = 'Filter Multilang2 Check';
$string['filterenabled'] = 'Filter Multilang2 is enabled';
$string['filterdisabled'] = 'Filter Multilang2 is NOT enabled';
$string['filterwarning'] = 'Warning: Filter Multilang2 must be enabled for "Content and headings" for multi-language placeholders to work correctly.';
$string['enablefilter'] = 'Enable Filter Multilang2';
$string['recentinjections'] = 'Recent Injections';
$string['noinjections'] = 'No injections logged yet';
$string['pagetype'] = 'Page type';
$string['fieldname'] = 'Field name';
$string['user'] = 'User';
$string['course'] = 'Course';
$string['moduletype'] = 'Module type';
$string['time'] = 'Time';
$string['testinject'] = 'Test Injection';
$string['testinject_desc'] = 'Preview how templates would be resolved for a given context';

// Messages.
$string['insertedtemplate'] = 'Inserted multilingual template—edit kk/ru/en';
$string['mappingsaved'] = 'Mapping saved';
$string['mappingdeleted'] = 'Mapping deleted';
$string['overridesaved'] = 'Override saved';

// Help.
$string['help'] = 'Help';
$string['authorguide'] = 'Author Guide';
$string['authorguide_desc'] = 'Guide for content authors on using multi-language placeholders';

