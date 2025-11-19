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
        console.log('MLANGDEFAULTS: JavaScript init called');
        
        // Get config from window.M.cfg or Config.
        const config = (typeof M !== 'undefined' && M.cfg && M.cfg.local_mlangdefaults) ?
            M.cfg.local_mlangdefaults : (Config.local_mlangdefaults || {});

        console.log('MLANGDEFAULTS: Config:', config);

        // Check if plugin is enabled.
        if (!config || !config.enabled) {
            console.log('MLANGDEFAULTS: Plugin disabled or no config');
            return;
        }

        // Check if this is a creation page.
        const currentUrl = window.location.href;
        if (config.creationonly && !isCreationPage(currentUrl)) {
            return;
        }

        // Get mappings for this page.
        const mappings = config.mappings || [];
        if (mappings.length === 0) {
            return;
        }

        // Wait for page to be ready.
        $(document).ready(function() {
            console.log('MLANGDEFAULTS: Document ready, mappings:', mappings);
            // Small delay to ensure editors are initialized.
            setTimeout(function() {
                console.log('MLANGDEFAULTS: Starting injection');
                injectDefaults(mappings, config);
            }, 500);
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
        console.log('MLANGDEFAULTS: injectDefaults called with', mappings.length, 'mappings');
        mappings.forEach(function(mapping) {
            console.log('MLANGDEFAULTS: Looking for field:', mapping.fieldselector);
            const field = document.getElementById(mapping.fieldselector);
            if (!field) {
                console.log('MLANGDEFAULTS: Field not found:', mapping.fieldselector);
                return;
            }
            console.log('MLANGDEFAULTS: Field found:', mapping.fieldselector, 'value:', field.value);

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

        const template = config.templates[templatekey];
        if (!template) {
            return '';
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
        // Try TinyMCE first.
        if (typeof M !== 'undefined' && M.editor && M.editor[fieldselector]) {
            const editor = M.editor[fieldselector];
            if (editor && editor.getContent && editor.getContent() === '') {
                editor.setContent(template);
                if (config.showtoast) {
                    showToast(config);
                }
                logInjection(fieldselector, template);
                return;
            }
        }

        // Try Atto.
        if (typeof Y !== 'undefined' && Y.M.editor_atto) {
            const editor = Y.M.editor_atto.get_editors()[fieldselector];
            if (editor && editor.get('value') === '') {
                editor.set('value', template);
                if (config.showtoast) {
                    showToast(config);
                }
                logInjection(fieldselector, template);
                return;
            }
        }

        // Fallback to hidden textarea.
        const textarea = document.getElementById(fieldselector);
        if (textarea && (!textarea.value || textarea.value.trim() === '')) {
            textarea.value = template;
            $(textarea).trigger('change');
            if (config.showtoast) {
                showToast(config);
            }
            logInjection(fieldselector, template);
        }
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

