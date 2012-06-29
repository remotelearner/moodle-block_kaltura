<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Obtain and initialize the secret, adminsecret and partner_id from the server
 *
 * @param string - email address of account
 * @param string - password
 * @param int - partner_id (optional)
 * @param string - secret (required if partner_id is passed)
 */
function kaltura_init_hosted_account($email, $password, $partner_id = 0, $secret = '') {
    try {
        $ksId = '';

        $config_obj = new KalturaConfiguration(0);
        $config_obj->serviceUrl = KalturaHelpers::getKalturaServerUrl();
        $config_obj->setLogger(new KalturaLogger());

        $kClient = new KalturaClient($config_obj);

        if (!empty($email) && !empty($password)) {
            $ksId = $kClient->adminUser->login($email, $password);
        } elseif (!empty($partner_id) && !empty($secret)) {
            $ksId = $kClient->session->start($secret, '',  KalturaSessionType::ADMIN, $partner_id);
        } else {
            return false;
        }

        $kClient->setKs($ksId);

        $kInfo = $kClient->partner->getInfo();

        // Check if these values already exist in the config table
        $secret         = get_config(KALTURA_PLUGIN_NAME, 'kaltura_secret');
        $adminsecret    = get_config(KALTURA_PLUGIN_NAME, 'kaltura_adminsecret');
        $partnerid      = get_config(KALTURA_PLUGIN_NAME, 'kaltura_partner_id');

        $setsecret      = (empty($secret) or (0 != strcmp($secret, $kInfo->secret)));
        $setadminsecret = (empty($adminsecret) or (0 != strcmp($adminsecret, $kInfo->adminSecret)));
        $setpartnerid   = (empty($partnerid) or (0 != strcmp($partnerid, $kInfo->id)));

        if ($setsecret or $setadminsecret or $setpartnerid) {

            $data = new stdClass;

            // Update everything to be safe

            set_config('kaltura_secret', $kInfo->secret, KALTURA_PLUGIN_NAME);

            set_config('kaltura_adminsecret', $kInfo->adminSecret, KALTURA_PLUGIN_NAME);

            set_config('kaltura_partner_id', $kInfo->id, KALTURA_PLUGIN_NAME);

        }

        return true;
    } catch(Exception $exp) {
        return false;
    }
}

/**
 * Get the username, password, partner id and secret
 * used for the connection type
 *
 * @param bool - true to return the admin secret otherwise false
 * @return array - login, password, partner id and secret
 */
function kaltura_get_credentials($admin = false) {

    $uri = get_config(KALTURA_PLUGIN_NAME, 'kaltura_uri');

    $login = false;
    $password = false;

    $login      = get_config(KALTURA_PLUGIN_NAME, 'kaltura_login');
    $password   = get_config(KALTURA_PLUGIN_NAME, 'kaltura_password');
    $partner_id = get_config(KALTURA_PLUGIN_NAME, 'kaltura_partner_id');

    if ($admin) {
        $secret = get_config(KALTURA_PLUGIN_NAME, 'kaltura_adminsecret');
    } else {
        $secret = get_config(KALTURA_PLUGIN_NAME, 'kaltura_secret');
    }

    return array($login, $password, $partner_id, $secret);
}

/**
 * Login to admin account
 *
 * @param bool - true to use admin session type, otherwise false
 */
function kaltura_login($admin = false) {

    list($username, $password, $partner_id, $secret) = kaltura_get_credentials($admin);

    if (empty($partner_id) && (empty($username) || empty($password))) {
        return false;
    }

    $ksId    = '';
    $kClient = new KalturaClient(KalturaHelpers::getServiceConfiguration());

    if (empty($partner_id)) {

        $ksId = $kClient->adminUser->login($username, $password);
    } else {

        $session_type = (false === $admin) ? KalturaSessionType::USER : KalturaSessionType::ADMIN;
        $ksId = $kClient->session->start($secret, '',  $session_type, $partner_id);
    }

    $kClient->setKs($ksId);

    return $kClient;

}

/**
 * Retrieve a list of all the custom players available to the account
 */

function kaltura_get_players() {

    $kClient = kaltura_login(true);

    $resultObject = $kClient->uiConf->listAction(null, null);

    return $resultObject;
}

/**
 * This method take an video entry id of type "mix" and if the video
 * duration is 0 seconds and finds the "video" type that is related to it.
 *
 * If the entry id is not of type "mix" then the same entry id is returned
 *
 * Resolves KMI-36
 *
 * @param  $entryid - entry id of the video
 */
function kaltura_get_video_type_entry($entryid) {

    $kaltura_client = kaltura_login();
    $object = new stdClass();

    //$entry = $kaltura_client->mixing->get('');
    $entry = $kaltura_client->baseEntry->get($entryid);

    $object->entryid = $entryid;
    $object->type    = $entry->type;

    // If we encounter a entry of type "mix", we must find the regular "video" type and display that for playback
    if (KalturaEntryType::MIX == $entry->type and
        0 >= $entry->duration) {

        // This call returns an array of "video" type entries that exist in the "mix" entry
        $media_entries = $kaltura_client->mixing->getReadyMediaEntries($entryid);

        if (!empty($media_entries)) {
            // Take the first "video" type.  If there is more than one I have no idea what should be done.
            $object->entryid    = $media_entries[0]->id;
            $object->type       = $media_entries[0]->type;

        } else {
            $object->entryid    = 0;
            $object->type       = '1';
        }
    }

    return $object;
}

/**
 * Return the UI Conf ID of a player
 *
 * @param string - the type of player to be used
 * @return int - the ui_conf id of the player
 *
 */
function get_player_uiconf($type = 'player') {
    $uiconf      = 0;
    $config_name = '';

    switch ($type) {
        case 'player_editor':
            $config_name = 'kaltura_player_editor';
            break;
        case 'player_dark':
            $config_name = 'kaltura_player_regular_dark';
            break;
        case 'player_light':
            $config_name = 'kaltura_player_regular_light';
            break;
        case 'player_mix_dark':
            $config_name = 'kaltura_player_mix_dark';
            break;
        case 'player_mix_light':
            $config_name = 'kaltura_player_mix_light';
            break;
        case 'player_presentation':
            $config_name = 'kaltura_player_video_presentation';
            break;
        case 'player_uploader':
            $config_name = 'kaltura_uploader_regular';
            break;
        case 'player_mix_uploader':
            $config_name = 'kaltura_uploader_mix';
            break;
        default:
            break;
    }

    if (!empty($config_name)) {
        $uiconf = get_config(KALTURA_PLUGIN_NAME, $config_name);

        if (empty($uiconf)) {
            $uiconf = get_config(KALTURA_PLUGIN_NAME, "{$config_name}_cust");
        }
    }

    return $uiconf;
}
?>