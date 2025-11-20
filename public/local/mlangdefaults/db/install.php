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
 * Installation hook to create database tables.
 *
 * @return bool
 */
function xmldb_local_mlangdefaults_install(): bool {
    global $DB;

    $dbman = $DB->get_manager();

    // Define table local_mlangdefaults_logs.
    $table = new xmldb_table('local_mlangdefaults_logs');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('pagetype', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
    $table->add_field('fieldname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('templateid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('moduletype', XMLDB_TYPE_CHAR, '50', null, null, null, null);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
    $table->add_index('courseid', XMLDB_INDEX_NOTUNIQUE, ['courseid']);
    $table->add_index('timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);

    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    // Define table local_mlangdefaults_course_overrides.
    $table = new xmldb_table('local_mlangdefaults_course_overrides');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('usesitedefaults', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
    $table->add_field('disabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('template_fullname', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('template_summary', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('template_activityname', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('template_activityintro', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    $table->add_key('courseid', XMLDB_KEY_UNIQUE, ['courseid']);

    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    // Set default configuration.
    set_config('enabled', 1, 'local_mlangdefaults');
    set_config('languages', 'kk,ru,en', 'local_mlangdefaults');
    set_config('fallbacklang', 'ru', 'local_mlangdefaults');
    set_config('creationonly', 1, 'local_mlangdefaults');
    set_config('skipifmlangpresent', 1, 'local_mlangdefaults');
    set_config('showtoast', 1, 'local_mlangdefaults');

    return true;
}

