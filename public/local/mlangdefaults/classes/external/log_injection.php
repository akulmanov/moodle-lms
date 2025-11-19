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

namespace local_mlangdefaults\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * External API for logging injections.
 */
class log_injection extends \external_api {
    /**
     * Returns description of method parameters.
     *
     * @return \external_function_parameters
     */
    public static function log_injection_parameters() {
        return new \external_function_parameters([
            'pagetype' => new \external_value(PARAM_TEXT, 'Page type'),
            'fieldname' => new \external_value(PARAM_TEXT, 'Field name'),
            'template' => new \external_value(PARAM_TEXT, 'Template text'),
        ]);
    }

    /**
     * Log injection event.
     *
     * @param string $pagetype Page type
     * @param string $fieldname Field name
     * @param string $template Template text
     * @return array Result
     */
    public static function log_injection($pagetype, $fieldname, $template) {
        global $USER, $DB;

        $params = self::validate_parameters(self::log_injection_parameters(), [
            'pagetype' => $pagetype,
            'fieldname' => $fieldname,
            'template' => $template,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);

        // Extract course ID and module type from URL if possible.
        $courseid = null;
        $moduletype = null;

        // Try to get from URL parameters.
        $url = $_SERVER['REQUEST_URI'] ?? '';
        if (preg_match('/course=(\d+)/', $url, $matches)) {
            $courseid = (int)$matches[1];
        }
        if (preg_match('/type=([^&]+)/', $url, $matches)) {
            $moduletype = $matches[1];
        }

        local_mlangdefaults_log_injection(
            $params['pagetype'],
            $params['fieldname'],
            null,
            $USER->id,
            $courseid,
            $moduletype
        );

        return ['success' => true];
    }

    /**
     * Returns description of method result value.
     *
     * @return \external_single_structure
     */
    public static function log_injection_returns() {
        return new \external_single_structure([
            'success' => new \external_value(PARAM_BOOL, 'Success'),
        ]);
    }
}

