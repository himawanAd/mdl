<?php
function xmldb_monitoring_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025021300) {
        $table = new xmldb_table('monitoring');

        // Tambahkan field
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('student_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('course_module_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('app_name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('detail', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('start_time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('end_time', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Tambahkan primary key
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Tambahkan foreign keys
        $table->add_key('student_fk', XMLDB_KEY_FOREIGN, ['student_id'], 'user', ['id']);
        $table->add_key('course_module_fk', XMLDB_KEY_FOREIGN, ['course_module_id'], 'course_modules', ['id']);

        // Buat tabel baru
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Upgrade savepoint
        upgrade_mod_savepoint(true, 2025021300, 'monitoring');
    }

    if ($oldversion < 20250311001) { 
        $table = new xmldb_table('monitoring_log');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('student_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('course_module_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('logged_at', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        $table->add_key('student_fk', XMLDB_KEY_FOREIGN, ['student_id'], 'user', ['id']);
        $table->add_key('course_module_fk', XMLDB_KEY_FOREIGN, ['course_module_id'], 'course_modules', ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_mod_savepoint(true, 2025031101, 'monitoring');
    }

    return true;
}
