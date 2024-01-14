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
 * Bulk Course Reset
 *
 * @package    tool_bulkreset
 * @copyright  2020 Ponlawat Weerapanpisit, Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_tool_bulkreset_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019072500) {
        $table = new xmldb_table('tool_bulkreset_schedules');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('starttime', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0', 'id');
        $table->add_field('status', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0', 'starttime');
        $table->add_field('data', XMLDB_TYPE_TEXT, null, null, null, null, null, 'status');
        $table->add_field('result', XMLDB_TYPE_TEXT, null, null, null, null, null, 'data');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('status_idx', XMLDB_INDEX_NOTUNIQUE, ['status']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2019072500, 'tool', 'bulkreset');
    }

    return true;
}
