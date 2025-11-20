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
 * Get all installed activity modules.
 *
 * @return array Array of module name => display name
 */
function local_mlangdefaults_get_installed_modules() {
    global $DB;
    require_once(__DIR__ . '/../../lib/modinfolib.php');
    
    $modules = $DB->get_records('modules', ['visible' => 1], 'name ASC');
    $result = [];
    
    foreach ($modules as $module) {
        $modname = $module->name;
        // Get display name.
        if (get_string_manager()->string_exists('pluginname', 'mod_' . $modname)) {
            $displayname = get_string('pluginname', 'mod_' . $modname);
        } else {
            $displayname = ucfirst($modname);
        }
        $result[$modname] = $displayname;
    }
    
    return $result;
}

/**
 * Get all installed question types.
 *
 * @return array Array of question type name => display name
 */
function local_mlangdefaults_get_installed_question_types() {
    require_once(__DIR__ . '/../../question/engine/bank.php');
    
    $qtypes = question_bank::get_creatable_qtypes();
    $result = [];
    
    foreach ($qtypes as $qtypename => $qtype) {
        $result[$qtypename] = $qtype->local_name();
    }
    
    return $result;
}

/**
 * Generate multi-language template from language list and template key.
 *
 * @param string $templatekey Template key (e.g., 'course_fullname')
 * @param array $context Context data (courseid, moduletype, etc.)
 * @return string Generated template with {mlang} tags
 */
function local_mlangdefaults_generate_template($templatekey, $context = []) {
    global $CFG;

    $enabled = get_config('local_mlangdefaults', 'enabled');
    if (!$enabled) {
        return '';
    }

    $languages = get_config('local_mlangdefaults', 'languages');
    $fallbacklang = get_config('local_mlangdefaults', 'fallbacklang') ?: 'ru';
    $langlist = array_map('trim', explode(',', $languages ?: 'kk,ru,en'));

    // Resolve template by precedence: course override -> module override -> global.
    $template = local_mlangdefaults_resolve_template($templatekey, $context);

    if (empty($template)) {
        // Use default template structure.
        $template = local_mlangdefaults_get_default_template($templatekey);
    }

    // Replace template variables if any.
    $template = local_mlangdefaults_replace_template_variables($template, $context);

    // If template already contains {mlang} tags, return it as-is.
    if (strpos($template, '{mlang') !== false) {
        return $template;
    }

    // Build {mlang} structure.
    $result = '';
    foreach ($langlist as $lang) {
        $result .= '{mlang ' . $lang . '}';
        if ($lang === $fallbacklang) {
            // For fallback language, include placeholder text.
            $result .= $template;
        }
        $result .= '{mlang}';
    }

    return $result;
}

/**
 * Resolve template by precedence.
 *
 * @param string $templatekey Template key
 * @param array $context Context data
 * @return string Template text
 */
function local_mlangdefaults_resolve_template($templatekey, $context = []) {
    global $DB;

    // Check course override first.
    if (!empty($context['courseid'])) {
        $courseoverride = $DB->get_record('local_mlangdefaults_course_overrides', [
            'courseid' => $context['courseid'],
        ]);
        if ($courseoverride && !$courseoverride->usesitedefaults && !$courseoverride->disabled) {
            $field = 'template_' . $templatekey;
            if (!empty($courseoverride->$field)) {
                return $courseoverride->$field;
            }
        }
    }

    // Check module-specific template (e.g., template_assign_name, template_quiz_intro).
    if (!empty($context['moduletype'])) {
        // For activity_name and activity_intro, check module-specific first (e.g., template_assign_name).
        if ($templatekey === 'activity_name' || $templatekey === 'activity_intro') {
            $moduletemplate = get_config('local_mlangdefaults', 'template_' . $context['moduletype'] . '_' . str_replace('activity_', '', $templatekey));
            if (!empty($moduletemplate)) {
                return $moduletemplate;
            }
        }
        // For module-specific template keys (e.g., assign_activityeditor).
        if (strpos($templatekey, $context['moduletype']) === 0) {
            $moduletemplate = get_config('local_mlangdefaults', 'template_' . $templatekey);
            if (!empty($moduletemplate)) {
                return $moduletemplate;
            }
        }
    }

    // Check question type-specific template (e.g., template_multichoice_name, template_essay_questiontext).
    if (!empty($context['questiontype'])) {
        // For question_name, question_questiontext, question_generalfeedback, check question type-specific first.
        if ($templatekey === 'question_name' || $templatekey === 'question_questiontext' || $templatekey === 'question_generalfeedback') {
            $fieldname = str_replace('question_', '', $templatekey);
            $qtypetemplate = get_config('local_mlangdefaults', 'template_' . $context['questiontype'] . '_' . $fieldname);
            if (!empty($qtypetemplate)) {
                return $qtypetemplate;
            }
        }
        // For question type-specific template keys (e.g., multichoice_name).
        if (strpos($templatekey, $context['questiontype']) === 0) {
            $qtypetemplate = get_config('local_mlangdefaults', 'template_' . $templatekey);
            if (!empty($qtypetemplate)) {
                return $qtypetemplate;
            }
        }
    }

    // Use global template.
    $globaltemplate = get_config('local_mlangdefaults', 'template_' . $templatekey);
    return $globaltemplate ?: '';
}

/**
 * Get default template for a template key.
 *
 * @param string $templatekey Template key
 * @return string Default template
 */
function local_mlangdefaults_get_default_template($templatekey) {
    $defaults = [
        'course_fullname' => 'Course title',
        'course_summary' => 'Course description',
        'section_name' => 'Section title',
        'section_summary' => 'Section description',
        'activity_name' => 'Activity title',
        'activity_intro' => 'Activity description',
        'assign_activityeditor' => 'Activity instructions',
        'page_content' => 'Page content',
        'question_name' => 'Question name',
        'question_questiontext' => 'Question text',
        'question_generalfeedback' => 'General feedback',
    ];

    return $defaults[$templatekey] ?? '';
}

/**
 * Replace template variables in template text.
 *
 * @param string $template Template text
 * @param array $context Context data
 * @return string Template with variables replaced
 */
function local_mlangdefaults_replace_template_variables($template, $context = []) {
    if (empty($context)) {
        return $template;
    }

    $replacements = [];
    if (!empty($context['courseshortname'])) {
        $replacements['{courseshortname}'] = $context['courseshortname'];
    }
    if (!empty($context['moduletype'])) {
        $replacements['{moduletype}'] = $context['moduletype'];
    }

    return str_replace(array_keys($replacements), array_values($replacements), $template);
}

/**
 * Log an injection event.
 *
 * @param string $pagetype Page type
 * @param string $fieldname Field name
 * @param int|null $templateid Template ID
 * @param int $userid User ID
 * @param int|null $courseid Course ID
 * @param string|null $moduletype Module type
 */
function local_mlangdefaults_log_injection($pagetype, $fieldname, $templateid, $userid, $courseid = null, $moduletype = null) {
    global $DB;

    $log = (object)[
        'pagetype' => $pagetype,
        'fieldname' => $fieldname,
        'templateid' => $templateid,
        'userid' => $userid,
        'courseid' => $courseid,
        'moduletype' => $moduletype,
        'timecreated' => time(),
    ];

    $DB->insert_record('local_mlangdefaults_logs', $log);
}

/**
 * Check if filter_multilang2 is enabled.
 *
 * @return bool True if enabled
 */
function local_mlangdefaults_is_filter_enabled() {
    global $CFG;
    require_once($CFG->dirroot . '/lib/filterlib.php');

    $filters = filter_get_globally_enabled();
    return in_array('multilang2', $filters);
}

/**
 * Check if filter_multilang2 is enabled for content and headings.
 *
 * @return array ['content' => bool, 'headings' => bool]
 */
function local_mlangdefaults_check_filter_settings() {
    global $CFG;
    require_once($CFG->dirroot . '/lib/filterlib.php');

    // Check if filter is enabled globally
    $filterenabled = filter_is_enabled('multilang2');
    
    // For now, if filter is enabled, assume it works for both content and headings
    // More detailed checking can be added later if needed
    return [
        'content' => $filterenabled,
        'headings' => $filterenabled,
    ];
}

/**
 * Check if page is a creation page (not edit).
 *
 * @param string $url Current URL
 * @return bool True if creation page
 */
function local_mlangdefaults_is_creation_page($url) {
    // Check for update parameter in modedit.php.
    if (strpos($url, '/course/modedit.php') !== false) {
        return strpos($url, 'update=') === false;
    }

    // For course edit, check if id is present (edit) vs not (create).
    if (strpos($url, '/course/edit.php') !== false) {
        return strpos($url, 'id=') === false;
    }

    // For section edit, check for id parameter.
    if (strpos($url, '/course/editsection.php') !== false) {
        return strpos($url, 'id=') === false;
    }

    return true;
}

/**
 * Get hardcoded field mappings for a page.
 *
 * @param string $pageurl Page URL
 * @return array Array of mapping objects with fieldselector, fieldtype, templatekey
 */
function local_mlangdefaults_get_mappings_for_page($pageurl) {
    // Hardcoded mappings based on Moodle 5.1 structure.
    $allmappings = local_mlangdefaults_get_all_hardcoded_mappings();
    $matched = [];

    foreach ($allmappings as $pattern => $fields) {
        if (preg_match('#' . $pattern . '#', $pageurl)) {
            foreach ($fields as $field) {
                $matched[] = (object)$field;
            }
        }
    }

    return $matched;
}

/**
 * Get all hardcoded field mappings.
 *
 * @return array Array of mappings keyed by page pattern
 */
function local_mlangdefaults_get_all_hardcoded_mappings() {
    return [
        // Course edit page.
        '/course/edit\.php' => [
            [
                'fieldselector' => 'id_fullname',
                'fieldtype' => 'text',
                'templatekey' => 'course_fullname',
            ],
            [
                'fieldselector' => 'id_summary_editor',
                'fieldtype' => 'editor',
                'templatekey' => 'course_summary',
            ],
        ],
        // Section edit page.
        '/course/editsection\.php' => [
            [
                'fieldselector' => 'id_name',
                'fieldtype' => 'text',
                'templatekey' => 'section_name',
            ],
            [
                'fieldselector' => 'id_summary_editor',
                'fieldtype' => 'editor',
                'templatekey' => 'section_summary',
            ],
        ],
        // Activity mod edit page - all activities have name and intro.
        '/course/modedit\.php' => [
            [
                'fieldselector' => 'id_name',
                'fieldtype' => 'text',
                'templatekey' => 'activity_name',
            ],
            [
                'fieldselector' => 'id_introeditor',
                'fieldtype' => 'editor',
                'templatekey' => 'activity_intro',
            ],
            // Assignment-specific field.
            [
                'fieldselector' => 'id_activityeditor',
                'fieldtype' => 'editor',
                'templatekey' => 'assign_activityeditor',
            ],
            // Page-specific content field.
            [
                'fieldselector' => 'id_page',
                'fieldtype' => 'editor',
                'templatekey' => 'page_content',
            ],
        ],
        // Question bank edit page - all question types have name, questiontext, and generalfeedback.
        '/question/bank/editquestion/question\.php' => [
            [
                'fieldselector' => 'id_name',
                'fieldtype' => 'text',
                'templatekey' => 'question_name',
            ],
            [
                'fieldselector' => 'id_questiontext',
                'fieldtype' => 'editor',
                'templatekey' => 'question_questiontext',
            ],
            [
                'fieldselector' => 'id_generalfeedback',
                'fieldtype' => 'editor',
                'templatekey' => 'question_generalfeedback',
            ],
        ],
    ];
}

/**
 * Hook into mod_form to populate default values from backend.
 * This is called via get_plugins_with_function('mod_form', 'data_preprocessing').
 *
 * @param array $defaultvalues Form default values (passed by reference)
 * @param object $formwrapper Form wrapper object
 */
function local_mlangdefaults_mod_form_data_preprocessing(&$defaultvalues, $formwrapper) {
    global $DB;
    
    // Check if plugin is enabled.
    if (!get_config('local_mlangdefaults', 'enabled')) {
        return;
    }
    
    // Check if this is a new module (creation).
    $isnew = empty($defaultvalues['instance']);
    
    // Only inject on creation if creationonly is enabled.
    if (!$isnew) {
        $creationonly = get_config('local_mlangdefaults', 'creationonly');
        if ($creationonly) {
            return;
        }
    }
    
    // Get module type.
    $moduletype = $defaultvalues['modulename'] ?? '';
    if (empty($moduletype)) {
        return;
    }
    
    // Get context for template resolution.
    $context = [
        'moduletype' => $moduletype,
    ];
    if (!empty($defaultvalues['course'])) {
        $context['courseid'] = $defaultvalues['course'];
    }
    
    // Populate name field.
    if (empty($defaultvalues['name']) || trim($defaultvalues['name']) === '') {
        $template = local_mlangdefaults_resolve_template('activity_name', $context);
        if (empty($template)) {
            $template = local_mlangdefaults_get_default_template('activity_name');
        }
        if (!empty($template)) {
            $mlangtemplate = local_mlangdefaults_generate_template('activity_name', $context);
            if (!empty($mlangtemplate)) {
                $defaultvalues['name'] = $mlangtemplate;
            }
        }
    }
    
    // Populate intro field.
    if (empty($defaultvalues['intro']) || trim($defaultvalues['intro']) === '') {
        $template = local_mlangdefaults_resolve_template('activity_intro', $context);
        if (empty($template)) {
            $template = local_mlangdefaults_get_default_template('activity_intro');
        }
        if (!empty($template)) {
            $mlangtemplate = local_mlangdefaults_generate_template('activity_intro', $context);
            if (!empty($mlangtemplate)) {
                // For intro, we need to set up the editor format.
                $defaultvalues['intro'] = $mlangtemplate;
                $defaultvalues['introformat'] = FORMAT_HTML;
            }
        }
    }
    
    // Module-specific fields.
    if ($moduletype === 'assign') {
        // Assignment activity editor.
        if (empty($defaultvalues['activity']) || trim($defaultvalues['activity']) === '') {
            $template = local_mlangdefaults_resolve_template('assign_activityeditor', $context);
            if (empty($template)) {
                $template = local_mlangdefaults_get_default_template('assign_activityeditor');
            }
            if (!empty($template)) {
                $mlangtemplate = local_mlangdefaults_generate_template('assign_activityeditor', $context);
                if (!empty($mlangtemplate)) {
                    $defaultvalues['activity'] = $mlangtemplate;
                    $defaultvalues['activityformat'] = FORMAT_HTML;
                }
            }
        }
    } else if ($moduletype === 'page') {
        // Page content.
        if (empty($defaultvalues['content']) || trim($defaultvalues['content']) === '') {
            $template = local_mlangdefaults_resolve_template('page_content', $context);
            if (empty($template)) {
                $template = local_mlangdefaults_get_default_template('page_content');
            }
            if (!empty($template)) {
                $mlangtemplate = local_mlangdefaults_generate_template('page_content', $context);
                if (!empty($mlangtemplate)) {
                    $defaultvalues['content'] = $mlangtemplate;
                    $defaultvalues['contentformat'] = FORMAT_HTML;
                }
            }
        }
    }
}

/**
 * Callback function for course edit form.
 * This is called via get_plugins_with_function.
 *
 * @param course_edit_form $formwrapper Course form wrapper
 * @param MoodleQuickForm $mform Form object
 */
function local_mlangdefaults_course_edit_form_callback($formwrapper, $mform) {
    // This is a wrapper to maintain compatibility with hook system
    // but also work as a callback
    if ($formwrapper instanceof \core_course\hook\after_form_definition_after_data) {
        // Called as hook
        $hook = $formwrapper;
        $formwrapper = $hook->formwrapper;
        $mform = $hook->mform;
    }
    
    local_mlangdefaults_course_edit_form_internal($formwrapper, $mform);
}

/**
 * Internal function to handle course edit form.
 *
 * @param course_edit_form $formwrapper Course form wrapper
 * @param MoodleQuickForm $mform Form object
 */
function local_mlangdefaults_course_edit_form_internal($formwrapper, $mform) {
    global $PAGE, $DB, $CFG;

    // Check if plugin is enabled.
    if (!get_config('local_mlangdefaults', 'enabled')) {
        return;
    }

    $course = $formwrapper->get_course();
    
    // Debug: Log that function was called (remove after testing)
    file_put_contents('/tmp/mlangdefaults_debug.log', 
        date('Y-m-d H:i:s') . ' - Function called - Course ID: ' . (empty($course->id) ? 'NEW' : $course->id) . PHP_EOL, 
        FILE_APPEND);
    
    // Check if this is a new course (creation).
    $isnewcourse = empty($course->id);
    
    // Only inject on creation if creationonly is enabled.
    if (!$isnewcourse) {
        $creationonly = get_config('local_mlangdefaults', 'creationonly');
        if ($creationonly) {
            return;
        }
    }

    // Get context for template resolution.
    $context = [];
    if (!empty($course->id)) {
        $context['courseid'] = $course->id;
    }

    // Get templates and set defaults for course fields.
    // In definition_after_data, we need to set the value directly on the element.
    if ($mform->elementExists('fullname')) {
        $currentvalue = $mform->getElementValue('fullname');
        $isempty = false;
        if ($currentvalue === null || $currentvalue === false) {
            $isempty = true;
        } elseif (is_array($currentvalue)) {
            $isempty = empty($currentvalue[0]) || trim($currentvalue[0]) === '';
        } else {
            $isempty = trim($currentvalue) === '';
        }
        
        if ($isempty) {
            $mlangtemplate = local_mlangdefaults_generate_template('course_fullname', $context);
            if (!empty($mlangtemplate)) {
                // Set the value directly on the element
                $element = $mform->getElement('fullname');
                if ($element) {
                    $element->setValue($mlangtemplate);
                }
            }
        }
    }

    if ($mform->elementExists('summary_editor')) {
        $currentvalue = $mform->getElementValue('summary_editor');
        $isempty = false;
        if ($currentvalue === null || $currentvalue === false) {
            $isempty = true;
        } elseif (is_array($currentvalue)) {
            $isempty = empty($currentvalue['text']) || trim($currentvalue['text']) === '';
        } else {
            $isempty = trim($currentvalue) === '';
        }
        
        if ($isempty) {
            $mlangtemplate = local_mlangdefaults_generate_template('course_summary', $context);
            if (!empty($mlangtemplate)) {
                $editorvalue = ['text' => $mlangtemplate, 'format' => FORMAT_HTML];
                // Set the value directly on the element
                $element = $mform->getElement('summary_editor');
                if ($element) {
                    $element->setValue($editorvalue);
                }
            }
        }
    }

    // Get mappings for course edit page for JavaScript.
    $url = '/course/edit.php';
    $mappings = local_mlangdefaults_get_mappings_for_page($url);
    if (!empty($mappings)) {
        // Prepare JavaScript configuration.
        $jsconfig = [
            'enabled' => true,
            'languages' => array_map('trim', explode(',', get_config('local_mlangdefaults', 'languages') ?: 'kk,ru,en')),
            'fallbacklang' => get_config('local_mlangdefaults', 'fallbacklang') ?: 'ru',
            'creationonly' => get_config('local_mlangdefaults', 'creationonly') ?: 1,
            'skipifmlangpresent' => get_config('local_mlangdefaults', 'skipifmlangpresent') ?: 1,
            'showtoast' => get_config('local_mlangdefaults', 'showtoast') ?: 1,
            'mappings' => [],
            'templates' => [],
            'strings' => [
                'insertedtemplate' => get_string('insertedtemplate', 'local_mlangdefaults'),
            ],
        ];

        // Add mappings for course edit form.
        foreach ($mappings as $mapping) {
            $jsconfig['mappings'][] = [
                'fieldselector' => $mapping->fieldselector,
                'fieldtype' => $mapping->fieldtype,
                'templatekey' => $mapping->templatekey,
            ];
        }

        // Get templates for JavaScript - collect unique template keys from mappings.
        $templatekeys = [];
        foreach ($mappings as $mapping) {
            if (!empty($mapping->templatekey) && !in_array($mapping->templatekey, $templatekeys)) {
                $templatekeys[] = $mapping->templatekey;
            }
        }
        foreach ($templatekeys as $key) {
            $template = local_mlangdefaults_resolve_template($key, $context);
            if (empty($template)) {
                $template = local_mlangdefaults_get_default_template($key);
            }
            $jsconfig['templates'][$key] = $template;
        }

        // Load inline JavaScript (same as before_footer hook)
        // This will be handled by the before_footer hook, so we don't duplicate here
        // Just set the config for the before_footer hook to use
    }
}

/**
 * Hook to populate form fields from backend (PHP) instead of JavaScript.
 * This sets form defaults directly, avoiding JavaScript timing issues.
 *
 * @param \core\hook\output\before_footer_html_generation $hook Hook object
 */
function local_mlangdefaults_before_footer($hook = null) {
    global $PAGE;
    
    // Check if plugin is enabled
    if (!get_config('local_mlangdefaults', 'enabled')) {
        return;
    }
    
    $url = $PAGE->url->out(false);
    $urlpattern = '';
    $context = [];
    $moduletype = null;
    
    // Try to get form from page and set defaults directly (for mod_form).
    if (strpos($url, '/course/modedit.php') !== false) {
        $moduletype = optional_param('add', '', PARAM_PLUGIN);
        if (empty($moduletype)) {
            $update = optional_param('update', 0, PARAM_INT);
            if ($update > 0) {
                global $DB;
                $cm = $DB->get_record('course_modules', ['id' => $update]);
                if ($cm) {
                    $module = $DB->get_record('modules', ['id' => $cm->module]);
                    if ($module) {
                        $moduletype = $module->name;
                    }
                }
            }
        }
        
        if ($moduletype) {
            // Try to access the form and set defaults directly.
            // The form might be in $PAGE->activityform or we need to get it another way.
            // For now, we'll use a simpler approach - set defaults via JavaScript but more reliably.
            // Actually, let's use the data_preprocessing approach via a callback if possible.
        }
    }
    
    // Handle course edit page.
    if (strpos($url, '/course/edit.php') !== false) {
        $urlpattern = '/course/edit.php';
        $courseid = optional_param('id', 0, PARAM_INT);
        if ($courseid > 0) {
            $creationonly = get_config('local_mlangdefaults', 'creationonly');
            if ($creationonly) {
                return;
            }
        }
        if ($courseid > 0) {
            $context['courseid'] = $courseid;
        }
    }
    // Handle section edit page.
    else if (strpos($url, '/course/editsection.php') !== false) {
        $urlpattern = '/course/editsection.php';
        $sectionid = optional_param('id', 0, PARAM_INT);
        if ($sectionid > 0) {
            $creationonly = get_config('local_mlangdefaults', 'creationonly');
            if ($creationonly) {
                return;
            }
        }
    }
    // Handle activity mod edit page.
    else if (strpos($url, '/course/modedit.php') !== false) {
        $urlpattern = '/course/modedit.php';
        // Extract module type from URL (add=assign, add=quiz, etc.).
        $moduletype = optional_param('add', '', PARAM_PLUGIN);
        if (empty($moduletype)) {
            // Try to get from update parameter.
            $update = optional_param('update', 0, PARAM_INT);
            if ($update > 0) {
                global $DB;
                $cm = $DB->get_record('course_modules', ['id' => $update]);
                if ($cm) {
                    $module = $DB->get_record('modules', ['id' => $cm->module]);
                    if ($module) {
                        $moduletype = $module->name;
                    }
                }
            }
        }
        if ($moduletype) {
            $context['moduletype'] = $moduletype;
        }
        $update = optional_param('update', 0, PARAM_INT);
        if ($update > 0) {
            $creationonly = get_config('local_mlangdefaults', 'creationonly');
            if ($creationonly) {
                return;
            }
        }
    }
    // Handle question bank edit page.
    else if (strpos($url, '/question/bank/editquestion/question.php') !== false) {
        $urlpattern = '/question/bank/editquestion/question.php';
        // Extract question type from URL (qtype=multichoice, qtype=essay, etc.).
        $qtype = optional_param('qtype', '', PARAM_COMPONENT);
        if (empty($qtype)) {
            // Try to get from question id.
            $id = optional_param('id', 0, PARAM_INT);
            if ($id > 0) {
                global $DB;
                $question = $DB->get_record('question', ['id' => $id]);
                if ($question) {
                    $qtype = $question->qtype;
                }
            }
        }
        if ($qtype) {
            $context['questiontype'] = $qtype;
        }
        $id = optional_param('id', 0, PARAM_INT);
        if ($id > 0) {
            $creationonly = get_config('local_mlangdefaults', 'creationonly');
            if ($creationonly) {
                return;
            }
        }
    }
    else {
        return;
    }
    
    // Get mappings for this page.
    $mappings = local_mlangdefaults_get_mappings_for_page($urlpattern);
    if (empty($mappings)) {
        return;
    }
    
    // Filter mappings based on module type for modedit pages.
    if ($moduletype && $urlpattern === '/course/modedit.php') {
        $filteredmappings = [];
        foreach ($mappings as $mapping) {
            // Include all standard mappings (name, intro).
            if ($mapping->templatekey === 'activity_name' || $mapping->templatekey === 'activity_intro') {
                $filteredmappings[] = $mapping;
            }
            // Include module-specific mappings (e.g., assign_activityeditor only for assign).
            else if (strpos($mapping->templatekey, $moduletype) === 0) {
                $filteredmappings[] = $mapping;
            }
        }
        $mappings = $filteredmappings;
    }
    
    // For question pages, all mappings are standard (name, questiontext, generalfeedback).
    // Question type-specific templates can be added later if needed.
    
    // Prepare JavaScript configuration
    $jsconfig = [
        'enabled' => true,
        'languages' => array_map('trim', explode(',', get_config('local_mlangdefaults', 'languages') ?: 'kk,ru,en')),
        'fallbacklang' => get_config('local_mlangdefaults', 'fallbacklang') ?: 'ru',
        'creationonly' => get_config('local_mlangdefaults', 'creationonly') ?: 1,
        'skipifmlangpresent' => get_config('local_mlangdefaults', 'skipifmlangpresent') ?: 1,
        'showtoast' => get_config('local_mlangdefaults', 'showtoast') ?: 1,
        'mappings' => [],
        'templates' => [],
        'strings' => [
            'insertedtemplate' => get_string('insertedtemplate', 'local_mlangdefaults'),
        ],
    ];
    
    foreach ($mappings as $mapping) {
        $jsconfig['mappings'][] = [
            'fieldselector' => $mapping->fieldselector,
            'fieldtype' => $mapping->fieldtype,
            'templatekey' => $mapping->templatekey,
        ];
    }
    
    // Get templates for JavaScript - collect unique template keys from mappings.
    $templatekeys = [];
    foreach ($mappings as $mapping) {
        if (!empty($mapping->templatekey) && !in_array($mapping->templatekey, $templatekeys)) {
            $templatekeys[] = $mapping->templatekey;
        }
    }
    
    // For activity pages, also load module-specific templates.
    if (!empty($context['moduletype'])) {
        // Add module-specific name and intro templates.
        $modnamekey = $context['moduletype'] . '_name';
        $modintrokey = $context['moduletype'] . '_intro';
        $templatekeys[] = $modnamekey;
        $templatekeys[] = $modintrokey;
    }
    
    // For question pages, also load question type-specific templates.
    if (!empty($context['questiontype'])) {
        // Add question type-specific name, questiontext, and generalfeedback templates.
        $qtypenamekey = $context['questiontype'] . '_name';
        $qtypequestiontextkey = $context['questiontype'] . '_questiontext';
        $qtypegeneralfeedbackkey = $context['questiontype'] . '_generalfeedback';
        $templatekeys[] = $qtypenamekey;
        $templatekeys[] = $qtypequestiontextkey;
        $templatekeys[] = $qtypegeneralfeedbackkey;
    }
    
    // Pass question type to JavaScript for dynamic template resolution.
    if (!empty($context['questiontype'])) {
        $jsconfig['questiontype'] = $context['questiontype'];
    }
    
    foreach ($templatekeys as $key) {
        // Resolve template - for module-specific keys, the resolve function will check module-specific config.
        $resolvkey = $key;
        if (!empty($context['moduletype']) && ($key === $context['moduletype'] . '_name' || $key === $context['moduletype'] . '_intro')) {
            // Map to activity_name or activity_intro for resolution (resolve function checks module-specific).
            $resolvkey = str_replace($context['moduletype'] . '_', 'activity_', $key);
        }
        // For question type-specific keys, map to question_* for resolution.
        if (!empty($context['questiontype']) && 
            ($key === $context['questiontype'] . '_name' || 
             $key === $context['questiontype'] . '_questiontext' || 
             $key === $context['questiontype'] . '_generalfeedback')) {
            // Map to question_name, question_questiontext, or question_generalfeedback for resolution.
            $resolvkey = str_replace($context['questiontype'] . '_', 'question_', $key);
        }
        $template = local_mlangdefaults_resolve_template($resolvkey, $context);
        if (empty($template)) {
            $template = local_mlangdefaults_get_default_template($resolvkey);
        }
        // Store with both keys so JavaScript can find it.
        $jsconfig['templates'][$key] = $template;
        if ($resolvkey !== $key) {
            $jsconfig['templates'][$resolvkey] = $template;
        }
    }
    
    // Pass module type to JavaScript for dynamic template resolution.
    if (!empty($context['moduletype'])) {
        $jsconfig['moduletype'] = $context['moduletype'];
    }
    
    // Load simplified JavaScript that uses MutationObserver for reliable field detection
    $js = "
    (function() {
        var config = " . json_encode($jsconfig) . ";
        window.M = window.M || {};
        window.M.cfg = window.M.cfg || {};
        window.M.cfg.local_mlangdefaults = config;
        
        function isCreationPage(url) {
            if (url.indexOf('/course/modedit.php') !== -1) {
                return url.indexOf('update=') === -1;
            }
            if (url.indexOf('/course/edit.php') !== -1) {
                return url.indexOf('id=') === -1;
            }
            if (url.indexOf('/course/editsection.php') !== -1) {
                return url.indexOf('id=') === -1;
            }
            return true;
        }
        
        function getTemplate(templatekey, config) {
            if (!config || !config.templates) return '';
            
            // For activity_name and activity_intro, check for module-specific template first.
            var actualkey = templatekey;
            if (config.moduletype && (templatekey === 'activity_name' || templatekey === 'activity_intro')) {
                var modkey = config.moduletype + '_' + templatekey.replace('activity_', '');
                if (config.templates[modkey]) {
                    actualkey = modkey;
                }
            }
            
            var template = config.templates[actualkey];
            if (!template) return '';
            if (template.indexOf('{mlang') !== -1) return template;
            
            var languages = config.languages || ['kk', 'ru', 'en'];
            var fallbacklang = config.fallbacklang || 'ru';
            var result = '';
            for (var i = 0; i < languages.length; i++) {
                var lang = languages[i];
                result += '{mlang ' + lang + '}';
                if (lang === fallbacklang) {
                    result += template;
                }
                result += '{mlang}';
            }
            return result;
        }
        
        // Simple function to set value in textarea (works for both text fields and editor textareas)
        function setFieldValue(fieldselector, value) {
            var field = document.getElementById(fieldselector);
            if (field && (!field.value || field.value.trim() === '')) {
                field.value = value;
                // Trigger change event
                var event = new Event('change', {bubbles: true});
                field.dispatchEvent(event);
                // Also trigger input event for better compatibility
                var inputEvent = new Event('input', {bubbles: true});
                field.dispatchEvent(inputEvent);
                return true;
            }
            return false;
        }
        
        // Simple function to inject into editor - just set textarea value and let editor sync
        function injectIntoEditorSimple(fieldselector, template) {
            var elementId = fieldselector;
            if (fieldselector.indexOf('id_') === 0) {
                elementId = fieldselector.substring(3);
            }
            
            // First, try to set the textarea value directly
            if (setFieldValue(fieldselector, template)) {
                // Then try to sync with TinyMCE if available
                if (typeof tinyMCE !== 'undefined') {
                    setTimeout(function() {
                        try {
                            var editor = tinyMCE.get(elementId);
                            if (editor && editor.setContent) {
                                editor.setContent(template);
                            }
                        } catch (e) {
                            // Ignore errors
                        }
                    }, 100);
                }
                // Try to sync with Atto if available
                if (typeof Y !== 'undefined' && Y.M && Y.M.editor_atto) {
                    setTimeout(function() {
                        try {
                            var editors = Y.M.editor_atto.get_editors();
                            if (editors && editors[elementId]) {
                                editors[elementId].set('value', template);
                            }
                        } catch (e) {
                            // Ignore errors
                        }
                    }, 100);
                }
                return true;
            }
            return false;
        }
        
        // Use MutationObserver to watch for form fields and inject when they appear
        function setupFieldInjection() {
            var mappings = config.mappings || [];
            var currentUrl = window.location.href;
            var injectedFields = {}; // Track which fields we've already injected
            
            // Detect module type from URL if not in config
            if (!config.moduletype && currentUrl.indexOf('/course/modedit.php') !== -1) {
                var urlParams = new URLSearchParams(window.location.search);
                var addParam = urlParams.get('add');
                if (addParam) {
                    config.moduletype = addParam;
                }
            }
            
            // Detect question type from URL if not in config
            if (!config.questiontype && currentUrl.indexOf('/question/bank/editquestion/question.php') !== -1) {
                var urlParams = new URLSearchParams(window.location.search);
                var qtypeParam = urlParams.get('qtype');
                if (qtypeParam) {
                    config.questiontype = qtypeParam;
                }
            }
            
            if (config.creationonly && !isCreationPage(currentUrl)) {
                return;
            }
            
            function tryInjectField(mapping) {
                // Skip if already injected
                if (injectedFields[mapping.fieldselector]) {
                    return;
                }
                
                var field = document.getElementById(mapping.fieldselector);
                if (!field) {
                    return false;
                }
                
                // Check if field already has content
                if (field.value && field.value.trim() !== '') {
                    injectedFields[mapping.fieldselector] = true;
                    return false;
                }
                
                // Check if skipifmlangpresent
                if (config.skipifmlangpresent && field.value.indexOf('{mlang') !== -1) {
                    injectedFields[mapping.fieldselector] = true;
                    return false;
                }
                
                var template = getTemplate(mapping.templatekey, config);
                if (!template) {
                    return false;
                }
                
                // Inject the template
                if (mapping.fieldtype === 'editor') {
                    if (injectIntoEditorSimple(mapping.fieldselector, template)) {
                        injectedFields[mapping.fieldselector] = true;
                        if (config.showtoast && typeof M !== 'undefined' && M.util && M.util.add_notification) {
                            M.util.add_notification(config.strings.insertedtemplate || 'Inserted multilingual template', {type: 'info'});
                        }
                        return true;
                    }
                } else {
                    if (setFieldValue(mapping.fieldselector, template)) {
                        injectedFields[mapping.fieldselector] = true;
                        if (config.showtoast && typeof M !== 'undefined' && M.util && M.util.add_notification) {
                            M.util.add_notification(config.strings.insertedtemplate || 'Inserted multilingual template', {type: 'info'});
                        }
                        return true;
                    }
                }
                return false;
            }
            
            // Try to inject immediately for fields that already exist
            for (var i = 0; i < mappings.length; i++) {
                tryInjectField(mappings[i]);
            }
            
            // Use MutationObserver to watch for fields that appear later
            var observer = new MutationObserver(function(mutations) {
                for (var i = 0; i < mappings.length; i++) {
                    tryInjectField(mappings[i]);
                }
            });
            
            // Start observing the document body for added nodes
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            // Also try injecting after a delay to catch editors that initialize late
            setTimeout(function() {
                for (var i = 0; i < mappings.length; i++) {
                    tryInjectField(mappings[i]);
                }
            }, 2000);
        }
        
        // Start injection when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupFieldInjection);
        } else {
            setupFieldInjection();
        }
    })();
    ";
    
    $PAGE->requires->js_amd_inline($js);
}

/**
 * Hook to add course settings link.
 *
 * @param navigation_node $navigation Navigation node
 * @param stdClass $course Course object
 * @param context $context Course context
 */
function local_mlangdefaults_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('local/mlangdefaults:overridecourse', $context)) {
        $url = new moodle_url('/local/mlangdefaults/course_settings.php', ['id' => $course->id]);
        $navigation->add(
            get_string('courseoverrides', 'local_mlangdefaults'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'mlangdefaults_course_settings'
        );
    }
}

