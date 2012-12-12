<?php
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
 * The is the block file for the Kaltura Video Extension
 *
 * @package   blocks-kaltura
 * @author    Akinsaya Delamarre <adelamarre@remote-learner.net>
 * @copyright 2011 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_kaltura extends block_base {
    function init() {
        $this->title   = get_string('blockname','block_kaltura');
        $this->version = 2012101200;
        $this->release  = '1.2';

    }

    function get_content() {
        global $CFG;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;

        $this->content = 'kaltura';

        return $this->content;

    }

    function instance_allow_config() {
        return false;
    }

    function instance_allow_multiple() {
        return true;
    }

    /**
     * Allow global configuration
     *
     * @return bool true
     */
    function has_config() {
        return true;
    }

   /**
     * Enable custom instance data section in backup and restore.
     *
     * If return true, then {@link instance_backup()} and
     * {@link instance_restore()} will be called during
     * backup/restore routines.
     *
     * @return boolean
     **/
    function backuprestore_instancedata_used() {
        return true;
    }

    /**
     * Allows the block class to have a backup routine.  Handy
     * when the block has its own tables that have foreign keys to
     * other tables (example: user table).
     *
     * Note: at the time of writing this comment, the indent level
     * for the {@link full_tag()} should start at 5.
     *
     * @param resource $bf Backup File
     * @param object $preferences Backup preferences
     * @return boolean
     **/
    function instance_backup($bf, $preferences) {
        global $CFG;

        $status = true;

        $records = get_records('block_kaltura_entries');

        //Start block_kaltura
        $status = fwrite ($bf,start_tag("BLOCK_KALTURA_ENTRIES", 5, true));

        // If assignment user info is being backed up.  Backup the submissions in the block backup
        if ($preferences->backup_user_info_assignment) {

            foreach ($preferences->assignment_instances as $instance) {

                if (0 == strcmp($instance->assignmenttype, 'kaltura')) {

                    $actid = 'backup_assignment_instance_' . $instance->id;

                    if (1 == $preferences->$actid) {

                        $assign_submit_id = get_field('assignment_submissions', 'id', 'assignment', $instance->id);

                        $record = get_record('block_kaltura_entries', 'context', 'S_'.$assign_submit_id,
                                             'courseid', $instance->course);

                        if ($record) {
                            $status = fwrite ($bf,start_tag("BLOCK_KALTURA_ENTRY", 6, true));

                            fwrite ($bf,full_tag("COURSEID", 7, false, $instance->course));
                            fwrite ($bf,full_tag("ID", 7, false, $record->id));
                            fwrite ($bf,full_tag("ENTRYID", 7, false, $record->entry_id));
                            fwrite ($bf,full_tag("DIMENSIONS", 7, false, $record->dimensions));
                            fwrite ($bf,full_tag("SIZE", 7, false, $record->size));
                            fwrite ($bf,full_tag("CUSTOMWIDTH", 7, false, $record->custom_width));
                            fwrite ($bf,full_tag("DESIGN", 7, false, $record->design));
                            fwrite ($bf,full_tag("TITLE", 7, false, $record->title));
                            fwrite ($bf,full_tag("CONTEXT", 7, false ,'S_' . $assign_submit_id));
                            fwrite ($bf,full_tag("ENTRYTYPE", 7, false, $record->entry_type));
                            fwrite ($bf,full_tag("MEDIATYPE", 7, false, $record->media_type));

                            $status = fwrite ($bf,end_tag("BLOCK_KALTURA_ENTRY", 6, true));
                        }
                    }
                }

            }
        }


        foreach ($preferences->resource_instances as $instance) {

            if (0 == strcmp($instance->type, 'kalturavideo') or
                0 == strcmp($instance->type, 'kalturaswfdoc')) {

                $actid = 'backup_resource_instance_' . $instance->id;

                if (1 == $preferences->$actid) {

                    $record = get_record('block_kaltura_entries', 'context', 'R_'.$instance->id,
                                         'courseid', $instance->course);

                    if ($record) {
                        $status = fwrite ($bf,start_tag("BLOCK_KALTURA_ENTRY", 6, true));

                        fwrite ($bf,full_tag("ID", 7, false, $record->id));
                        fwrite ($bf,full_tag("COURSEID", 7, false, $record->courseid));
                        fwrite ($bf,full_tag("ENTRYID", 7, false, $record->entry_id));
                        fwrite ($bf,full_tag("DIMENSIONS", 7, false, $record->dimensions));
                        fwrite ($bf,full_tag("SIZE", 7, false, $record->size));
                        fwrite ($bf,full_tag("CUSTOMWIDTH", 7, false, $record->custom_width));
                        fwrite ($bf,full_tag("DESIGN", 7, false, $record->design));
                        fwrite ($bf,full_tag("TITLE", 7, false, $record->title));
                        fwrite ($bf,full_tag("CONTEXT", 7, false ,'R_' . $instance->id));
                        fwrite ($bf,full_tag("ENTRYTYPE", 7, false, $record->entry_type));
                        fwrite ($bf,full_tag("MEDIATYPE", 7, false, $record->media_type));

                        $status = fwrite ($bf,end_tag("BLOCK_KALTURA_ENTRY", 6, true));
                    }
                }
            }
        }

        $status = fwrite ($bf,end_tag("BLOCK_KALTURA_ENTRIES", 5, true));

        return $status;
    }


    /**
     * Allows the block class to restore its backup routine.
     *
     * Should not return false if data is empty
     * because old backups would not contain block instance backup data.
     *
     * @param object $restore Standard restore object
     * @param object $data Object from backup_getid for this block instance
     * @return boolean
     **/
    function instance_restore($restore, $data) {


        $instance_obj = $data->info;
        
        if (isset($instance_obj['BLOCK_KALTURA_ENTRIES'][0]['#']['BLOCK_KALTURA_ENTRY'])) {
            $instancedata =
            $instance_obj['BLOCK_KALTURA_ENTRIES'][0]['#']['BLOCK_KALTURA_ENTRY'];
    
            for ($i = 0; $i < count($instancedata); $i++) {
    
                $activityinstance = $instancedata[$i];
    
                $kaltura                = new stdClass();
                $oldid                  = $activityinstance['#']['ID'][0]['#'];
                $kaltura->courseid      = $activityinstance['#']['COURSEID'][0]['#'];
                $kaltura->entry_id      = addslashes($activityinstance['#']['ENTRYID'][0]['#']);
                $kaltura->dimensions    = addslashes($activityinstance['#']['DIMENSIONS'][0]['#']);
                $kaltura->size          = addslashes($activityinstance['#']['SIZE'][0]['#']);
                $kaltura->custom_width  = addslashes($activityinstance['#']['CUSTOMWIDTH'][0]['#']);
                $kaltura->design        = addslashes($activityinstance['#']['DESIGN'][0]['#']);
                $kaltura->title         = addslashes($activityinstance['#']['TITLE'][0]['#']);
    
                if (empty($kaltura->title)) {
                    $kaltura->title = '...';
                }
    
                $kaltura->context       = addslashes($activityinstance['#']['CONTEXT'][0]['#']);
    
                // Determine the activity type and get the new ID of the submission
                $table_name = '';
                $context = '';
                $activity_instance_id = false;
    
                if (false !== strpos($kaltura->context, 'S_')) {
    
                    $activity_instance_id   = substr($kaltura->context, 2);
                    $context                = 'S_';
                    $table_name             = 'assignment_submission';
                } elseif (false !== strpos($kaltura->context, 'R_')) {
    
                    $activity_instance_id   = substr($kaltura->context, 2);
                    $context                = 'R_';
                    $table_name             = 'resource';
                }
    
                if (false !== $activity_instance_id) {
                    $new_instance = get_record('backup_ids',
                                               'backup_code', $restore->backup_unique_code,
                                               'table_name', $table_name,
                                               'old_id', $activity_instance_id,
                                               'new_id');
    
                    if ($new_instance) {
                        $kaltura->context = $context . $new_instance->new_id;
                    }
                } else {
                    return false;
                }
    
                $kaltura->entry_type    = addslashes($activityinstance['#']['ENTRYTYPE'][0]['#']);
                $kaltura->media_type    = addslashes($activityinstance['#']['MEDIATYPE'][0]['#']);
    
    
                $kaltura->courseid = $restore->course_id;
                insert_record('block_kaltura_entries', $kaltura);
    
                if (!defined('RESTORE_SILENTLY')) {
                    echo "<li>".get_string('blockname','block_kaltura')." \"".format_string(stripslashes($kaltura->title), true)."\"</li>";
                }
    
            }
        }

        return true;
    }


    /**
     * Migrate all kaltura entries from the module version of the tables
     * to the block version of the tables
     */
    function after_install() {
        global $CFG;

        $table = new XMLDBTable('kaltura_entries');

        if (table_exists($table)) {
            $sql = "SELECT * ".
                   "FROM {$CFG->prefix}kaltura_entries ".
                   "WHERE context LIKE 'R_%'";

            // Migrate resource kaltura entries over to the block table
            $kaltura_entries = get_recordset_sql($sql);

            if ($kaltura_entries) {

                while (!$kaltura_entries->EOF) {


                    $context = $kaltura_entries->fields['context'];

                    // $context_parts[1] should equal the id of the plugin type (assignment/resource)
                    $context_parts = array();
                    $context_parts = explode('_', $context);

                    if (!empty($context_parts)) {

                    $course = get_field('resource', 'course', 'id', $context_parts[1]);

                        if (!empty($course)) {
                            $newrec = new stdClass();

                            $newrec->courseid       = $course;
                            $newrec->entry_id       = $kaltura_entries->fields['entry_id'];
                            $newrec->dimensions     = $kaltura_entries->fields['dimensions'];;
                            $newrec->size           = $kaltura_entries->fields['size'];;
                            $newrec->custom_width   = $kaltura_entries->fields['custom_width'];;
                            $newrec->design         = $kaltura_entries->fields['design'];;
                            $newrec->title          = $kaltura_entries->fields['title'];;
                            $newrec->context        = $context;
                            $newrec->entry_type     = $kaltura_entries->fields['entry_type'];;
                            $newrec->media_type     = $kaltura_entries->fields['media_type'];

                            $id = insert_record('block_kaltura_entries', $newrec);

                            if ($id) {
                                //
                            }

                        }

                    }

                    $kaltura_entries->MoveNext();
                }
            }

        // Migrate resource kaltura entries over to the block table
            $sql = "SELECT * ".
                   "FROM {$CFG->prefix}kaltura_entries ".
                   "WHERE context LIKE 'S_%'";

            $kaltura_entries = get_recordset_sql($sql);

            if ($kaltura_entries) {

                while (!$kaltura_entries->EOF) {


                    $context = $kaltura_entries->fields['context'];

                    // $context_parts[1] should equal the id of the plugin type (assignment/resource)
                    $context_parts = array();
                    $context_parts = explode('_', $context);

                    if (!empty($context_parts)) {

                        $field = $context_parts[1];
                        $sql = "SELECT assign_sumbit.id, a.course ".
                               "FROM {$CFG->prefix}assignment_submissions assign_sumbit ".
                               "RIGHT JOIN {$CFG->prefix}assignment a ON assign_sumbit.assignment = a.id ".
                               " WHERE assign_sumbit.id = {$field}";

                        $data = get_record_sql($sql);

                        if (!empty($data)) {

                            $newrec = new stdClass();

                            $newrec->courseid       = $data->course;
                            $newrec->entry_id       = $kaltura_entries->fields['entry_id'];
                            $newrec->dimensions     = $kaltura_entries->fields['dimensions'];;
                            $newrec->size           = $kaltura_entries->fields['size'];;
                            $newrec->custom_width   = $kaltura_entries->fields['custom_width'];;
                            $newrec->design         = $kaltura_entries->fields['design'];;
                            $newrec->title          = $kaltura_entries->fields['title'];;
                            $newrec->context        = $context;
                            $newrec->entry_type     = $kaltura_entries->fields['entry_type'];;
                            $newrec->media_type     = $kaltura_entries->fields['media_type'];

                            $id = insert_record('block_kaltura_entries', $newrec);

                            if ($id) {
                                //
                            }
                        }
                    }

                    $kaltura_entries->MoveNext();
                }
            }

            // Migrate old plugin settings
            $this->migrate_old_plugin_settings();
        }
    }

    /**
     * This function searches through the config_plugins table for Kaltura
     * related configuration values.  The logic works as follows:
     * 1. Search the config table where name is like 'kaltura'
     *
     * 2. Copy the kaltura secret, adminsecret username, username and partnerid
     * to the config_plugins table using the current naming convention.
     *
     * This occurs with a certain version of the Kaltura plugin
     *
     * 3. Search the config_plugins table where plugin like
     * 'kaltura'
     *
     * 4. Copy the kaltua secret, adminsecret, username, password and partnerid
     * to the config_plugins table using the current naming convention
     *
     * This occurs with a different version of the Kaltura plugin
     *
     * @param nothing
     * @return nothing
     */
    function migrate_old_plugin_settings() {

        $this->migrate_config_table_settings();

        $this->migrate_config_plugin_table_settings();

    }

    /**
     * This function supports Kaltura's version of the Kaltura module
     * Note: Username and passwords were not stored in the config table
     */
    function migrate_config_plugin_table_settings() {
        global $CFG;

        // Search the config table for Kaltura configuration values
        $sql = "SELECT * FROM {$CFG->prefix}config_plugins " .
               "WHERE plugin = 'kaltura' ";

        $records = get_records_sql($sql);

        if (empty($records)) {
            $records = array();
        }

        foreach ($records as $record) {

            switch ($record->name) {
                case 'server_uri':
                    // If the value is kaltura.com then assume SaaS, otherwise CE
                    if (0 == strcmp($record->value, 'http://www.kaltura.com')) {
                        set_config('kaltura_conn_server', 'hosted', 'block_kaltura');
                    } else {
                        set_config('kaltura_conn_server', 'ce', 'block_kaltura');
                    }

                    set_config('kaltura_uri', $record->value, 'block_kaltura');
                    break;

                case 'secret':
                    set_config('kaltura_secret', $record->value, 'block_kaltura');
                    break;

                case 'adminsecret':
                    set_config('kaltura_adminsecret', $record->value, 'block_kaltura');
                    break;

                case 'partner_id':
                    set_config('kaltura_partner_id', $record->value, 'block_kaltura');
                    break;

                case 'uploader_regular':
                    if (1002217 == $record->value) {
                        set_config('kaltura_uploader_regular', '7632751', 'block_kaltura');
                    } else {
                        set_config('kaltura_uploader_regular', '0', 'block_kaltura');
                        set_config('kaltura_uploader_regular_cust', $record->value, 'block_kaltura');
                    }

                    break;

                case 'uploader_mix':
                    if (1002225 == $record->value) {
                        set_config('kaltura_uploader_mix', '4395701', 'block_kaltura');
                    } else {
                        set_config('kaltura_uploader_mix', '0', 'block_kaltura');
                        set_config('kaltura_uploader_mix_cust', $record->value, 'block_kaltura');
                    }
                    break;

                case 'editor':
                    if (1002226 == $record->value) {
                        set_config('kaltura_player_editor', '4395711', 'block_kaltura');
                    } else {
                        set_config('kaltura_player_editor', '0', 'block_kaltura');
                        set_config('kaltura_player_editor_cust', $record->value, 'block_kaltura');
                    }
                    break;

                case 'player_regular_dark':
                    if (1002712 == $record->value) {
                        set_config('kaltura_player_regular_dark', '4674741', 'block_kaltura');
                    } else {
                        set_config('kaltura_player_regular_dark', '0', 'block_kaltura');
                        set_config('kaltura_player_regular_dark_cust', $record->value, 'block_kaltura');
                    }
                    break;

                case 'player_regular_light':
                    if (1002711 == $record->value) {
                        set_config('kaltura_player_regular_light', '4674731', 'block_kaltura');
                    } else {
                        set_config('kaltura_player_regular_light', '0', 'block_kaltura');
                        set_config('kaltura_player_regular_light_cust', $record->value, 'block_kaltura');
                    }
                    break;

                case 'player_mix_dark':
                    if (1002259 == $record->value) {
                        set_config('kaltura_player_mix_dark', '4860321', 'block_kaltura');
                    } else {
                        set_config('kaltura_player_mix_dark', '0', 'block_kaltura');
                        set_config('kaltura_player_mix_dark_cust', $record->value, 'block_kaltura');
                    }
                    break;

                case 'player_mix_light':
                    if (1002260 == $record->value) {
                        set_config('kaltura_player_mix_light', '4860311', 'block_kaltura');
                    } else {
                        set_config('kaltura_player_mix_light', '0', 'block_kaltura');
                        set_config('kaltura_player_mix_light_cust', $record->value, 'block_kaltura');
                    }
                    break;

                case 'video_presentation':
                    if (1003069 == $record->value) {
                        set_config('kaltura_player_video_presentation', '4860481', 'block_kaltura');
                    } else {
                        set_config('kaltura_player_video_presentation', '0', 'block_kaltura');
                        set_config('kaltura_player_video_presentation_cust', $record->value, 'block_kaltura');
                    }
                    break;
            }
        }

    }

    /**
     * This method supports the original RL version of the Kaltura module
     * Note: the location of the configuration value were stored in a different
     * table
     */
    function migrate_config_table_settings() {
        global $CFG;

        // Search the config table for Kaltura configuration values
        $sql = "SELECT * FROM {$CFG->prefix}config " .
               "WHERE name " . sql_ilike() . " '%kaltura%' ".
               "ORDER BY name DESC";

        $records = get_records_sql($sql);

        if (empty($records)) {
            $records = array();
        }

        foreach ($records as $record) {

            switch ($record->name) {
                case 'kaltura_conn_server':
                    set_config('kaltura_conn_server', $record->value, 'block_kaltura');
                    break;

                case 'kaltura_uri':
                case 'kaltura_hosted_uri':
                case 'kaltura_ce_uri':
                    // Only set the URI if it is empty
                    $uri = get_config('block_kaltura', 'kaltura_uri');

                    if (empty($uri)) {
                        set_config('kaltura_uri', $record->value, 'block_kaltura');
                    }
                    break;

                case 'kaltura_login':
                case 'kaltura_hosted_login':
                case 'kaltura_ce_login': // For a period of time in 2010 separate CE settings existed
                    // Only set the URI if it is empty
                    $uri = get_config('block_kaltura', 'kaltura_login');

                    if (empty($uri)) {
                        set_config('kaltura_login', $record->value, 'block_kaltura');
                    }
                    break;

                case 'kaltura_password':
                case 'kaltura_hosted_password':
                case 'kaltura_ce_password':
                    // Only set the URI if it is empty
                    $uri = get_config('block_kaltura', 'kaltura_password');

                    if (empty($uri)) {
                        set_config('kaltura_password', $record->value, 'block_kaltura');
                    }
                    break;

                case 'kaltura_secret':
                    set_config('kaltura_secret', $record->value, 'block_kaltura');
                    break;

                case 'kaltura_adminsecret':
                    set_config('kaltura_adminsecret', $record->value, 'block_kaltura');
                    break;

                case 'kaltura_partner_id':
                    set_config('kaltura_partner_id', $record->value, 'block_kaltura');
                    break;

                case 'kaltura_uploader_regular':
                    if (1002217 == $record->value) {
                        set_config('kaltura_uploader_regular', '7632751', 'block_kaltura');
                    } else {
                        set_config('kaltura_uploader_regular', '0', 'block_kaltura');
                        set_config('kaltura_uploader_regular_cust', $record->value, 'block_kaltura');
                    }

                    break;

                case 'kaltura_uploader_mix':
                    if (1002225 == $record->value) {
                        set_config('kaltura_uploader_mix', '4395701', 'block_kaltura');
                    } else {
                        set_config('kaltura_uploader_mix', '0', 'block_kaltura');
                        set_config('kaltura_uploader_mix_cust', $record->value, 'block_kaltura');
                    }
                    break;

                case 'kaltura_player_editor':
                    if (1002226 == $record->value) {
                        set_config('kaltura_player_editor', '4395711', 'block_kaltura');
                    } else {
                        set_config('kaltura_player_editor', '0', 'block_kaltura');
                        set_config('kaltura_player_editor_cust', $record->value, 'block_kaltura');
                    }
                    break;

                case 'kaltura_player_regular_dark':
                    if (1002712 == $record->value) {
                        set_config('kaltura_player_regular_dark', '4674741', 'block_kaltura');
                    } else {
                        set_config('kaltura_player_regular_dark', '0', 'block_kaltura');
                        set_config('kaltura_player_regular_dark_cust', $record->value, 'block_kaltura');
                    }
                    break;

                case 'kaltura_player_regular_light':
                    if (1002711 == $record->value) {
                        set_config('kaltura_player_regular_light', '4674731', 'block_kaltura');
                    } else {
                        set_config('kaltura_player_regular_light', '0', 'block_kaltura');
                        set_config('kaltura_player_regular_light_cust', $record->value, 'block_kaltura');
                    }
                    break;

                case 'kaltura_player_mix_dark':
                    if (1002259 == $record->value) {
                        set_config('kaltura_player_mix_dark', '4860321', 'block_kaltura');
                    } else {
                        set_config('kaltura_player_mix_dark', '0', 'block_kaltura');
                        set_config('kaltura_player_mix_dark_cust', $record->value, 'block_kaltura');
                    }
                    break;

                case 'kaltura_player_mix_light':
                    if (1002260 == $record->value) {
                        set_config('kaltura_player_mix_light', '4860311', 'block_kaltura');
                    } else {
                        set_config('kaltura_player_mix_light', '0', 'block_kaltura');
                        set_config('kaltura_player_mix_light_cust', $record->value, 'block_kaltura');
                    }
                    break;

                case 'kaltura_player_video_presentation':
                    if (1003069 == $record->value) {
                        set_config('kaltura_player_video_presentation', '4860481', 'block_kaltura');
                    } else {
                        set_config('kaltura_player_video_presentation', '0', 'block_kaltura');
                        set_config('kaltura_player_video_presentation_cust', $record->value, 'block_kaltura');
                    }
                    break;
            }
        }
    }

}
?>
