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

/**
 * Multi-language defaults injection module.
 *
 * @module     local_mlangdefaults/inject
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification', 'core/config'], function($, Ajax, Notification, Config) {
    'use strict';

    /**
     * Initialize injection on page load.
     */
    function init() {
        // Get config from window.M.cfg or Config.
        const config = (typeof M !== 'undefined' && M.cfg && M.cfg.local_mlangdefaults) ?
            M.cfg.local_mlangdefaults : (Config.local_mlangdefaults || {});

        // Check if plugin is enabled.
        if (!config || !config.enabled) {
            return;
        }

        // Check if this is a creation page.
        const currentUrl = window.location.href;
        if (config.creationonly && !isCreationPage(currentUrl)) {
            return;
        }

        // Detect module type from URL if not in config (for modedit pages).
        if (!config.moduletype && currentUrl.indexOf('/course/modedit.php') !== -1) {
            const urlParams = new URLSearchParams(window.location.search);
            const addParam = urlParams.get('add');
            if (addParam) {
                config.moduletype = addParam;
            }
        }

        // Get mappings for this page.
        const mappings = config.mappings || [];
        if (mappings.length === 0) {
            return;
        }

        // Wait for page to be ready.
        $(document).ready(function() {
            // Longer delay to ensure editors (especially TinyMCE) are fully initialized.
            setTimeout(function() {
                injectDefaults(mappings, config);
            }, 1500);
        });
    }

    /**
     * Check if current page is a creation page.
     *
     * @param {string} url Current URL
     * @return {boolean} True if creation page
     */
    function isCreationPage(url) {
        // Check for update parameter in modedit.php.
        if (url.indexOf('/course/modedit.php') !== -1) {
            return url.indexOf('update=') === -1;
        }

        // For course edit, check if id is present (edit) vs not (create).
        if (url.indexOf('/course/edit.php') !== -1) {
            return url.indexOf('id=') === -1;
        }

        // For section edit, check for id parameter.
        if (url.indexOf('/course/editsection.php') !== -1) {
            return url.indexOf('id=') === -1;
        }

        return true;
    }

    /**
     * Inject defaults into fields.
     *
     * @param {Array} mappings Array of mapping objects
     * @param {Object} config Configuration object
     */
    function injectDefaults(mappings, config) {
        mappings.forEach(function(mapping) {
            const field = document.getElementById(mapping.fieldselector);
            if (!field) {
                return;
            }

            // Check if field is empty.
            if (field.value && field.value.trim() !== '') {
                return;
            }

            // Check if skipifmlangpresent is enabled and field already contains {mlang.
            if (config.skipifmlangpresent && field.value.indexOf('{mlang') !== -1) {
                return;
            }

            // Get template.
            const template = getTemplate(mapping.templatekey, config);
            if (!template) {
                return;
            }

            // Inject into field.
            if (mapping.fieldtype === 'editor') {
                injectIntoEditor(mapping.fieldselector, template, config);
            } else {
                injectIntoTextField(field, template, config);
            }
        });
    }

    /**
     * Get template for a template key.
     *
     * @param {string} templatekey Template key
     * @param {Object} config Configuration object
     * @return {string} Template text
     */
    function getTemplate(templatekey, config) {
        if (!config || !config.templates) {
            return '';
        }

        // For activity_name and activity_intro, check for module-specific template first.
        let actualkey = templatekey;
        if (config.moduletype && (templatekey === 'activity_name' || templatekey === 'activity_intro')) {
            const modkey = config.moduletype + '_' + templatekey.replace('activity_', '');
            if (config.templates[modkey]) {
                actualkey = modkey;
            }
        }

        const template = config.templates[actualkey];
        if (!template) {
            return '';
        }

        // If template already has {mlang} tags, return as-is.
        if (template.indexOf('{mlang') !== -1) {
            return template;
        }

        // Build {mlang} structure.
        const languages = config.languages || ['kk', 'ru', 'en'];
        const fallbacklang = config.fallbacklang || 'ru';
        let result = '';

        languages.forEach(function(lang) {
            result += '{mlang ' + lang + '}';
            if (lang === fallbacklang) {
                result += template;
            }
            result += '{mlang}';
        });

        return result;
    }

    /**
     * Inject template into text field.
     *
     * @param {HTMLElement} field Field element
     * @param {string} template Template text
     * @param {Object} config Configuration object
     */
    function injectIntoTextField(field, template, config) {
        if (field.value && field.value.trim() !== '') {
            return;
        }

        field.value = template;
        $(field).trigger('change');

        // Show toast if enabled.
        if (config.showtoast) {
            showToast(config);
        }

        // Log injection.
        logInjection(field.id, template);
    }

    /**
     * Inject template into editor field (TinyMCE/Atto).
     *
     * @param {string} fieldselector Field selector
     * @param {string} template Template text
     * @param {Object} config Configuration object
     */
    function injectIntoEditor(fieldselector, template, config) {
        // Get the actual element ID (remove 'id_' prefix if present).
        let elementId = fieldselector;
        if (fieldselector.startsWith('id_')) {
            elementId = fieldselector.substring(3);
        }

        const textarea = document.getElementById(fieldselector);
        if (!textarea) {
            return;
        }

        // Check if textarea already has content.
        if (textarea.value && textarea.value.trim() !== '') {
            return;
        }

        // Function to try injecting into TinyMCE.
        function tryInjectTinyMCE() {
            if (typeof tinyMCE === 'undefined') {
                return false;
            }

            try {
                const editor = tinyMCE.get(elementId);
                if (editor && editor.initialized) {
                    const currentContent = editor.getContent();
                    if (!currentContent || currentContent.trim() === '') {
                        editor.setContent(template);
                        if (config.showtoast) {
                            showToast(config);
                        }
                        logInjection(fieldselector, template);
                        return true;
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
                const editors = Y.M.editor_atto.get_editors();
                if (editors && editors[elementId]) {
                    const editor = editors[elementId];
                    const currentValue = editor.get('value');
                    if (!currentValue || currentValue.trim() === '') {
                        editor.set('value', template);
                        if (config.showtoast) {
                            showToast(config);
                        }
                        logInjection(fieldselector, template);
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
        let attempts = 0;
        const maxAttempts = 20; // Try for up to 4 seconds (20 * 200ms).
        const pollInterval = setInterval(function() {
            attempts++;
            
            if (tryInjectTinyMCE() || tryInjectAtto()) {
                clearInterval(pollInterval);
                return;
            }

            if (attempts >= maxAttempts) {
                clearInterval(pollInterval);
                // Final fallback: set textarea value and trigger change.
                textarea.value = template;
                $(textarea).trigger('change');
                // Try one more time to sync with editor if it became available.
                setTimeout(function() {
                    tryInjectTinyMCE();
                }, 100);
                if (config.showtoast) {
                    showToast(config);
                }
                logInjection(fieldselector, template);
            }
        }, 200);
    }

    /**
     * Show toast notification.
     *
     * @param {Object} config Configuration object
     */
    function showToast(config) {
        const message = (config.strings && config.strings.insertedtemplate) ?
            config.strings.insertedtemplate : 'Inserted multilingual template—edit kk/ru/en';
        Notification.addNotification({
            message: message,
            type: 'info',
        });
    }

    /**
     * Log injection event.
     *
     * @param {string} fieldname Field name
     * @param {string} template Template text
     */
    function logInjection(fieldname, template) {
        const promises = Ajax.call([{
            methodname: 'local_mlangdefaults_log_injection',
            args: {
                pagetype: getPageType(),
                fieldname: fieldname,
                template: template,
            },
        }]);

        promises[0].fail(function(error) {
            // Silently fail - logging is not critical.
        });
    }

    /**
     * Get current page type.
     *
     * @return {string} Page type
     */
    function getPageType() {
        const url = window.location.href;
        if (url.indexOf('/course/edit.php') !== -1) {
            return 'course_edit';
        }
        if (url.indexOf('/course/editsection.php') !== -1) {
            return 'section_edit';
        }
        if (url.indexOf('/course/modedit.php') !== -1) {
            return 'activity_edit';
        }
        return 'unknown';
    }

    return {
        init: init,
    };
});

