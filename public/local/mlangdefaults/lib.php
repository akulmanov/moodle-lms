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

    // Check module override.
    if (!empty($context['moduletype'])) {
        $moduletemplate = get_config('local_mlangdefaults', 'template_' . $context['moduletype'] . '_' . $templatekey);
        if (!empty($moduletemplate)) {
            return $moduletemplate;
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
 * Get field mappings for a page.
 *
 * @param string $pageurl Page URL
 * @return array Array of mapping objects
 */
function local_mlangdefaults_get_mappings_for_page($pageurl) {
    global $DB;

    $mappings = $DB->get_records('local_mlangdefaults_mappings', ['enabled' => 1], 'priority DESC');
    $matched = [];

    foreach ($mappings as $mapping) {
        $pattern = $mapping->pagepattern;
        // Convert simple pattern to regex if needed.
        if (strpos($pattern, '/') === 0 && strpos($pattern, '^') !== 0) {
            $pattern = '#' . $pattern . '#';
        }
        if (preg_match($pattern, $pageurl)) {
            $matched[] = $mapping;
        }
    }

    return $matched;
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

        // Get templates for JavaScript.
        $templatekeys = ['course_fullname', 'course_summary'];
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
 * Hook to inject JavaScript before footer.
 * This is a fallback if the form hook doesn't work.
 *
 * @param \core\hook\output\before_footer_html_generation $hook Hook object
 */
function local_mlangdefaults_before_footer($hook = null) {
    global $PAGE;
    
    // Only run on course edit page
    $url = $PAGE->url->out(false);
    if (strpos($url, '/course/edit.php') === false) {
        return;
    }
    
    // Check if plugin is enabled
    if (!get_config('local_mlangdefaults', 'enabled')) {
        return;
    }
    
    // Check if this is a new course (no id parameter means new course)
    $courseid = optional_param('id', 0, PARAM_INT);
    if ($courseid > 0) {
        $creationonly = get_config('local_mlangdefaults', 'creationonly');
        if ($creationonly) {
            return;
        }
    }
    
    // Load JavaScript for course edit form
    $urlpattern = '/course/edit.php';
    $mappings = local_mlangdefaults_get_mappings_for_page($urlpattern);
    if (empty($mappings)) {
        return;
    }
    
    $context = [];
    if ($courseid > 0) {
        $context['courseid'] = $courseid;
    }
    
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
    
    $templatekeys = ['course_fullname', 'course_summary'];
    foreach ($templatekeys as $key) {
        $template = local_mlangdefaults_resolve_template($key, $context);
        if (empty($template)) {
            $template = local_mlangdefaults_get_default_template($key);
        }
        $jsconfig['templates'][$key] = $template;
    }
    
    // Load inline JavaScript (no AMD build required)
    $js = "
    (function() {
        console.log('MLANGDEFAULTS: Inline script loaded');
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
            var template = config.templates[templatekey];
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
        
        function injectDefaults() {
            console.log('MLANGDEFAULTS: Starting injection');
            var mappings = config.mappings || [];
            var currentUrl = window.location.href;
            
            if (config.creationonly && !isCreationPage(currentUrl)) {
                console.log('MLANGDEFAULTS: Not a creation page, skipping');
                return;
            }
            
            for (var i = 0; i < mappings.length; i++) {
                var mapping = mappings[i];
                console.log('MLANGDEFAULTS: Processing mapping:', mapping.fieldselector);
                
                var field = document.getElementById(mapping.fieldselector);
                if (!field) {
                    console.log('MLANGDEFAULTS: Field not found:', mapping.fieldselector);
                    continue;
                }
                
                if (field.value && field.value.trim() !== '') {
                    console.log('MLANGDEFAULTS: Field already has value, skipping');
                    continue;
                }
                
                if (config.skipifmlangpresent && field.value.indexOf('{mlang') !== -1) {
                    console.log('MLANGDEFAULTS: Field already has {mlang}, skipping');
                    continue;
                }
                
                var template = getTemplate(mapping.templatekey, config);
                if (!template) {
                    console.log('MLANGDEFAULTS: No template for:', mapping.templatekey);
                    continue;
                }
                
                if (mapping.fieldtype === 'editor') {
                    // Try TinyMCE
                    if (typeof M !== 'undefined' && M.editor && M.editor[mapping.fieldselector]) {
                        var editor = M.editor[mapping.fieldselector];
                        if (editor && editor.getContent && editor.getContent() === '') {
                            editor.setContent(template);
                            console.log('MLANGDEFAULTS: Injected into TinyMCE editor');
                        }
                    } else {
                        // Fallback to textarea
                        field.value = template;
                        if (field.dispatchEvent) {
                            field.dispatchEvent(new Event('change', {bubbles: true}));
                        }
                        console.log('MLANGDEFAULTS: Injected into textarea');
                    }
                } else {
                    field.value = template;
                    if (field.dispatchEvent) {
                        field.dispatchEvent(new Event('change', {bubbles: true}));
                    }
                    console.log('MLANGDEFAULTS: Injected into text field');
                }
                
                if (config.showtoast && typeof M !== 'undefined' && M.util && M.util.add_notification) {
                    M.util.add_notification(config.strings.insertedtemplate || 'Inserted multilingual template', {type: 'info'});
                }
            }
        }
        
        // Wait for page to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(injectDefaults, 1000);
            });
        } else {
            setTimeout(injectDefaults, 1000);
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

