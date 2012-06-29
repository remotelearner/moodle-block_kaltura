<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_block_kaltura_upgrade($oldversion = 0) {
    global $CFG, $THEME, $db;

    $result = true;

    if ($oldversion < 2011032801) {

        $table = new XMLDBTable('block_kaltura_entries');

        $field = new XMLDBField('courseid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '1', 'id');

        $result = $result && add_field($table, $field);

        $index = new XMLDBIndex('kaltura_courseid_ix');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('courseid'));

        if(!index_exists($table, $index)) {
            $result = $result && add_index($table, $index);
        }
    }

    return $result;
}

?>