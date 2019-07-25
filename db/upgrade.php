<?php

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
