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

defined('MOODLE_INTERNAL') || die();

require_once('lib.php');

require_js(array('yui_yahoo', 'yui_event', 'yui_dom'));
require_js($CFG->wwwroot.'/blocks/kaltura/js/settings.js');

// Test connection status
$conntype   = get_config(KALTURA_PLUGIN_NAME, 'kaltura_conn_server');
$uri        = get_config(KALTURA_PLUGIN_NAME, 'kaltura_uri');
$login      = get_config(KALTURA_PLUGIN_NAME, 'kaltura_login');
$password   = get_config(KALTURA_PLUGIN_NAME, 'kaltura_password');
$partner_id = get_config(KALTURA_PLUGIN_NAME, 'kaltura_partner_id');
$secret     = get_config(KALTURA_PLUGIN_NAME, 'kaltura_adminsecret');

$connected = false;

if (!empty($uri) &&
    ( !empty($partner_id) ) ||
    ( !empty($login) and !empty($password) ) ) {

    $connected = kaltura_init_hosted_account($login, $password, $partner_id, $secret);

}

$validconfig = false;

if ($connected) {
    // Print connection status message
    $settings->add(new admin_setting_heading('kaltur_conn_status', get_string('conn_status_title', 'block_kaltura'),
                   get_string('init'.$conntype, 'block_kaltura')));

} else {
    // Print connection status message
    $blurb = $blurb = get_string('errorinit'.$conntype, 'block_kaltura') . '<br /><br />' . get_string('kalturaregister', 'block_kaltura');
    $settings->add(new admin_setting_heading('kaltur_conn_status', get_string('conn_status_title', 'block_kaltura'),
                   $blurb));

}

// Connection selection
$settings->add(new admin_setting_heading('kaltura_conn_heading', get_string('conn_heading_title', 'block_kaltura'),
                   get_string('conn_heading_desc', 'block_kaltura')));

// Server Connection
$choices = array('hosted' => get_string('hostedconn', 'block_kaltura'),
                 'ce' => get_string('ceconn', 'block_kaltura')
                 );

$adminsetting = new admin_setting_configselect('kaltura_conn_server', get_string('conn_server', 'block_kaltura'),
                            get_string('conn_server_desc', 'block_kaltura'), 'hosted', $choices);
$adminsetting->plugin = KALTURA_PLUGIN_NAME;
$settings->add($adminsetting);

// Connection URI
$adminsetting = new admin_setting_configtext('kaltura_uri', get_string('server_uri', 'block_kaltura'),
                   get_string('server_uri_desc', 'block_kaltura'), KALTURA_DEFAULT_URI, PARAM_URL);
$adminsetting->plugin = KALTURA_PLUGIN_NAME;
$settings->add($adminsetting);


// Hosted connection settings

// Kaltura login
$adminsetting = new admin_setting_configtext('kaltura_login', get_string('hosted_login', 'block_kaltura'),
                   get_string('hosted_login_desc', 'block_kaltura'), 'kaltura_hosted@email.com', PARAM_TEXT);
$adminsetting->plugin = KALTURA_PLUGIN_NAME;
$settings->add($adminsetting);

// Kaltura password
$adminsetting = new admin_setting_configpasswordunmask('kaltura_password', get_string('hosted_password', 'block_kaltura'),
                   get_string('hosted_password_desc', 'block_kaltura'), '');
$adminsetting->plugin = KALTURA_PLUGIN_NAME;
$settings->add($adminsetting);

if ($connected) {

    // Get all custom players
    $players = kaltura_get_players();

    $custplayers = array();

    foreach ($players->objects as $playerobj) {

        $custplayers[$playerobj->id] = format_string($playerobj->name);
    }

    $manualplayer[0] = get_string('custom_player', 'block_kaltura');
    $custplayers += $manualplayer;


    // Connection selection
    if ($validconfig) {
        $blurb = get_string('custplayer_heading_desc', 'block_kaltura') . '<br /><br />' . get_string('disclaimer', 'block_kaltura');
    } else {
        $blurb = get_string('custplayer_heading_desc', 'block_kaltura');
    }

    $settings->add(new admin_setting_heading('custplayer_heading', get_string('custplayer_heading', 'block_kaltura'),
                       $blurb));

    // Print players drop down menus

    /// PLAYER EDITOR
    $choices = array(KALTURA_PLAYER_PLAYEREDITOR        => get_string('player_editor', 'block_kaltura'),
                     KALTURA_LEGACY_PLAYER_PLAYEREDITOR => get_string('legacy_player_editor', 'block_kaltura')) + $custplayers;

    $adminsetting = new admin_setting_configselect('kaltura_player_editor', get_string('kaltura_player_editor', 'block_kaltura'),
                       get_string('kaltura_player_editor_desc', 'block_kaltura'), KALTURA_PLAYER_PLAYEREDITOR, $choices);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);

    $adminsetting = new admin_setting_configtext('kaltura_player_editor_cust', get_string('custom_ui_conf', 'block_kaltura'),
                       get_string('player_editor_cust_desc', 'block_kaltura'), '', PARAM_INT);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);


    /// UPLOADER REGULAR
    $choices = array(KALTURA_PLAYER_UPLOADERREGULAR         => get_string('player_uploader_regular', 'block_kaltura'),
                     KALTURA_LEGACY_PLAYER_UPLOADERREGULAR  => get_string('legacy_player_uploader_regular', 'block_kaltura')) + $manualplayer;

    $adminsetting = new admin_setting_configselect('kaltura_uploader_regular', get_string('kaltura_uploader_regular', 'block_kaltura'),
                       get_string('kaltura_uploader_regular_desc', 'block_kaltura'), KALTURA_PLAYER_UPLOADERREGULAR, $choices);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);

    $adminsetting = new admin_setting_configtext('kaltura_uploader_regular_cust', get_string('custom_ui_conf', 'block_kaltura'),
                       get_string('uploader_regular_desc', 'block_kaltura'), '', PARAM_INT);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);


    /// UPLOADER MIX
    $choices = array(KALTURA_PLAYER_UPLOADERMIX         => get_string('player_uploader_mix', 'block_kaltura'),
                     KALTURA_LEGACY_PLAYER_UPLOADERMIX  => get_string('legacy_player_uploader_mix', 'block_kaltura')) + $manualplayer;

    $adminsetting = new admin_setting_configselect('kaltura_uploader_mix', get_string('kaltura_uploader_mix', 'block_kaltura'),
                       get_string('kaltura_uploader_mix', 'block_kaltura'), KALTURA_PLAYER_UPLOADERMIX, $choices);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);

    $adminsetting = new admin_setting_configtext('kaltura_uploader_mix_cust', get_string('custom_ui_conf', 'block_kaltura'),
                       get_string('kaltura_uploader_mix_desc', 'block_kaltura'), '', PARAM_INT);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);

    /// REGULAR PLAYER DARK
    $choices = array(KALTURA_PLAYER_PLAYERREGULARDARK           => get_string('player_regular_dark', 'block_kaltura'),
                     KALTURA_LEGACY_PLAYER_PLAYERREGULARDARK    => get_string('legacy_player_regular_dark', 'block_kaltura')) + $custplayers;

    $adminsetting = new admin_setting_configselect('kaltura_player_regular_dark', get_string('kaltura_player_regular_dark', 'block_kaltura'),
                       get_string('kaltura_player_regular_dark_desc', 'block_kaltura'), KALTURA_PLAYER_PLAYERREGULARDARK, $choices);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);

    $adminsetting = new admin_setting_configtext('kaltura_player_regular_dark_cust', get_string('custom_ui_conf', 'block_kaltura'),
                       get_string('player_regular_dark_cust_desc', 'block_kaltura'), '', PARAM_INT);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);

    /// REGULAR PLAYER LIGHT
    $choices = array(KALTURA_PLAYER_PLAYERREGULARLIGHT          => get_string('player_regular_light', 'block_kaltura'),
                     KALTURA_LEGACY_PLAYER_PLAYERREGULARLIGHT   => get_string('legacy_player_regular_light', 'block_kaltura')) + $custplayers;

    $adminsetting = new admin_setting_configselect('kaltura_player_regular_light', get_string('kaltura_player_regular_light', 'block_kaltura'),
                       get_string('kaltura_player_regular_light_desc', 'block_kaltura'), KALTURA_PLAYER_PLAYERREGULARLIGHT, $choices);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);

    $adminsetting = new admin_setting_configtext('kaltura_player_regular_light_cust', get_string('custom_ui_conf', 'block_kaltura'),
                       get_string('player_regular_light_cust_desc', 'block_kaltura'), '', PARAM_INT);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);

    /// PLAYER MIX DARK
    $choices = array(KALTURA_PLAYER_PLAYERMIXDARK           => get_string('player_mix_dark', 'block_kaltura'),
                     KALTURA_LEGACY_PLAYER_PLAYERMIXDARK    => get_string('legacy_player_mix_dark', 'block_kaltura')) + $manualplayer;

    $adminsetting = new admin_setting_configselect('kaltura_player_mix_dark', get_string('kaltura_player_mix_dark', 'block_kaltura'),
                       get_string('kaltura_player_mix_dark_desc', 'block_kaltura'), KALTURA_PLAYER_PLAYERMIXDARK, $choices);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);

    $adminsetting = new admin_setting_configtext('kaltura_player_mix_dark_cust', get_string('custom_ui_conf', 'block_kaltura'),
                       get_string('player_mix_dark_desc', 'block_kaltura'), '', PARAM_INT);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);

    // PLAYER MIX LIGHT
    $choices = array(KALTURA_PLAYER_PLAYERMIXLIGHT                  => get_string('player_mix_light', 'block_kaltura'),
                     KALTURA_LEGACY_LEGACY_PLAYER_PLAYERMIXLIGHT    => get_string('legacy_player_mix_light', 'block_kaltura')) + $manualplayer;

    $adminsetting = new admin_setting_configselect('kaltura_player_mix_light', get_string('kaltura_player_mix_light', 'block_kaltura'),
                       get_string('kaltura_player_mix_light_desc', 'block_kaltura'), KALTURA_PLAYER_PLAYERMIXLIGHT, $choices);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);

    $adminsetting = new admin_setting_configtext('kaltura_player_mix_light_cust', get_string('custom_ui_conf', 'block_kaltura'),
                       get_string('player_mix_light_desc', 'block_kaltura'), '', PARAM_INT);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);

    /// PLAYER VIDEO PRESENTATION
    $choices = array(KALTURA_PLAYER_PLAYERVIDEOPRESENTATION         => get_string('video_presentation', 'block_kaltura'),
                     KALTURA_LEGACY_PLAYER_PLAYERVIDEOPRESENTATION  => get_string('legacy_video_presentation', 'block_kaltura')) + $manualplayer;

    $adminsetting = new admin_setting_configselect('kaltura_player_video_presentation', get_string('kaltura_player_video_presentation', 'block_kaltura'),
                       get_string('kaltura_player_video_presentation_desc', 'block_kaltura'), KALTURA_PLAYER_PLAYERVIDEOPRESENTATION, $choices);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);

    $adminsetting = new admin_setting_configtext('kaltura_player_video_presentation_cust', get_string('custom_ui_conf', 'block_kaltura'),
                       get_string('player_video_presentation_cust_desc', 'block_kaltura'), '', PARAM_INT);
    $adminsetting->plugin = KALTURA_PLUGIN_NAME;
    $settings->add($adminsetting);

}
?>