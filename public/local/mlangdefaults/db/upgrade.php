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

/**
 * Upgrade script for local_mlangdefaults plugin.
 *
 * @package    local_mlangdefaults
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function to remove mappings table.
 *
 * @param int $oldversion The old version number
 * @return bool True on success
 */
function xmldb_local_mlangdefaults_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Drop mappings table if it exists (removed in favor of hardcoded mappings).
    if ($oldversion < 2024120101) {
        $table = new xmldb_table('local_mlangdefaults_mappings');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        upgrade_plugin_savepoint(true, 2024120101, 'local', 'mlangdefaults');
    }

    return true;
}

