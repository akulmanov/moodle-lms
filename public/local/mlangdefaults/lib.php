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
    ];
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
 * Hook to inject JavaScript before footer.
 * This is a fallback if the form hook doesn't work.
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
    
    foreach ($templatekeys as $key) {
        // Resolve template - for module-specific keys, the resolve function will check module-specific config.
        $resolvkey = $key;
        if (!empty($context['moduletype']) && ($key === $context['moduletype'] . '_name' || $key === $context['moduletype'] . '_intro')) {
            // Map to activity_name or activity_intro for resolution (resolve function checks module-specific).
            $resolvkey = str_replace($context['moduletype'] . '_', 'activity_', $key);
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
    
    // Load inline JavaScript (no AMD build required)
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
        
        function injectIntoEditor(fieldselector, template, config) {
            // Get the actual element ID (remove 'id_' prefix if present).
            var elementId = fieldselector;
            if (fieldselector.indexOf('id_') === 0) {
                elementId = fieldselector.substring(3);
            }
            
            var textarea = document.getElementById(fieldselector);
            if (!textarea) {
                return;
            }
            
            // Check if textarea already has content.
            if (textarea.value && textarea.value.trim() !== '') {
                return;
            }
            
            var injected = false;
            
            // Function to try injecting into TinyMCE.
            function tryInjectTinyMCE() {
                if (typeof tinyMCE === 'undefined') {
                    return false;
                }
                
                try {
                    var editor = tinyMCE.get(elementId);
                    if (editor) {
                        // Check if editor is initialized - try multiple ways
                        var isReady = false;
                        if (editor.initialized !== undefined) {
                            isReady = editor.initialized;
                        } else if (editor.getContent) {
                            // If getContent exists, try to use it to check readiness
                            try {
                                editor.getContent();
                                isReady = true;
                            } catch (e) {
                                isReady = false;
                            }
                        }
                        
                        if (isReady) {
                            var currentContent = editor.getContent();
                            // Check if content is empty or just whitespace/empty tags
                            if (!currentContent || currentContent.trim() === '' || currentContent.trim() === '<p></p>' || currentContent.trim() === '<p><br></p>') {
                                editor.setContent(template);
                                if (config.showtoast && typeof M !== 'undefined' && M.util && M.util.add_notification) {
                                    M.util.add_notification(config.strings.insertedtemplate || 'Inserted multilingual template', {type: 'info'});
                                }
                                return true;
                            }
                        }
                    }
                } catch (e) {
                    // Silently handle TinyMCE access errors.
                }
                return false;
            }
            
            // Function to try injecting into Atto.
            function tryInjectAtto() {
                if (typeof Y === 'undefined' || !Y.M || !Y.M.editor_atto) {
                    return false;
                }
                
                try {
                    var editors = Y.M.editor_atto.get_editors();
                    if (editors && editors[elementId]) {
                        var editor = editors[elementId];
                        var currentValue = editor.get('value');
                        if (!currentValue || currentValue.trim() === '') {
                            editor.set('value', template);
                            if (config.showtoast && typeof M !== 'undefined' && M.util && M.util.add_notification) {
                                M.util.add_notification(config.strings.insertedtemplate || 'Inserted multilingual template', {type: 'info'});
                            }
                            return true;
                        }
                    }
                } catch (e) {
                    // Silently handle Atto access errors.
                }
                return false;
            }
            
            // Try immediate injection.
            if (tryInjectTinyMCE() || tryInjectAtto()) {
                return;
            }
            
            // If editor not ready, wait and retry with polling.
            var attempts = 0;
            var maxAttempts = 30; // Try for up to 6 seconds (30 * 200ms).
            var pollInterval = setInterval(function() {
                attempts++;
                
                if (tryInjectTinyMCE() || tryInjectAtto()) {
                    clearInterval(pollInterval);
                    injected = true;
                    return;
                }
                
                if (attempts >= maxAttempts) {
                    clearInterval(pollInterval);
                    // Final fallback: set textarea value and trigger change.
                    textarea.value = template;
                    if (textarea.dispatchEvent) {
                        textarea.dispatchEvent(new Event('change', {bubbles: true}));
                    }
                    // Try one more time to sync with editor if it became available.
                    setTimeout(function() {
                        tryInjectTinyMCE();
                    }, 100);
                    if (config.showtoast && typeof M !== 'undefined' && M.util && M.util.add_notification) {
                        M.util.add_notification(config.strings.insertedtemplate || 'Inserted multilingual template', {type: 'info'});
                    }
                }
            }, 200);
        }
        
        function injectDefaults() {
            var mappings = config.mappings || [];
            var currentUrl = window.location.href;
            
            // Detect module type from URL if not in config (for modedit pages).
            if (!config.moduletype && currentUrl.indexOf('/course/modedit.php') !== -1) {
                var urlParams = new URLSearchParams(window.location.search);
                var addParam = urlParams.get('add');
                if (addParam) {
                    config.moduletype = addParam;
                }
            }
            
            if (config.creationonly && !isCreationPage(currentUrl)) {
                return;
            }
            
            for (var i = 0; i < mappings.length; i++) {
                var mapping = mappings[i];
                
                var field = document.getElementById(mapping.fieldselector);
                if (!field) {
                    continue;
                }
                
                if (field.value && field.value.trim() !== '') {
                    continue;
                }
                
                if (config.skipifmlangpresent && field.value.indexOf('{mlang') !== -1) {
                    continue;
                }
                
                var template = getTemplate(mapping.templatekey, config);
                if (!template) {
                    continue;
                }
                
                if (mapping.fieldtype === 'editor') {
                    injectIntoEditor(mapping.fieldselector, template, config);
                } else {
                    field.value = template;
                    if (field.dispatchEvent) {
                        field.dispatchEvent(new Event('change', {bubbles: true}));
                    }
                    if (config.showtoast && typeof M !== 'undefined' && M.util && M.util.add_notification) {
                        M.util.add_notification(config.strings.insertedtemplate || 'Inserted multilingual template', {type: 'info'});
                    }
                }
            }
        }
        
        // Wait for page to be ready and editors to initialize
        // Use longer delay to ensure TinyMCE/Atto editors are fully initialized
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(injectDefaults, 1500);
            });
        } else {
            setTimeout(injectDefaults, 1500);
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

