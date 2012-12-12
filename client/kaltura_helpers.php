<?php
class KalturaHelpers {

    static $platfromConfig = null;

    function importCE($url, $email, $password, &$secret, &$adminSecret, &$partner) {
        $kConfig                = new KalturaConfiguration(0);
        $kConfig->serviceUrl    = $url;

        $kClient                = new KalturaClient($kConfig);
        $kPartner               = $kClient->partner->getSecrets(1, $email, $password);
        $partner                = 1;
        $secret                 = $kPartner->secret;
        $adminSecret            = $kPartner->adminSecret;
    }

    function register($name, $email, &$secret, &$adminSecret, &$partner, $phone="",
            $description="", $ver="", $describeYourself="", $webSiteUrl="", $contentCategory="",$adultContent=false) {

        $kConfig = new KalturaConfiguration(0);
        $kConfig->serviceUrl = KALTURA_DEFAULT_URI;
        $kClient = new KalturaClient($kConfig);
        $kPartner = new KalturaPartner();
        $kPartner -> name = $name;
        $kPartner -> adminName = $name;
        $kPartner -> adminEmail =  $email;
        $kPartner -> phone = $phone;
        $kPartner -> describeYourself = $describeYourself;
        $kPartner -> website = $webSiteUrl;
        $kPartner -> contentCategories = $contentCategory;
        $kPartner -> adultContent = $adultContent;
        $kPartner -> description = $description . "\n|" . "Moodle|" . $ver;
        $kPartner -> commercialUse = 'non-commercial_use';
        $kPartner -> type = 104;
        $kPartner = $kClient -> partner -> register ($kPartner);

        $partner  = $kPartner -> id;
        $secret = $kPartner -> secret;
        $adminSecret = $kPartner -> adminSecret;
    }

    function getContributionWizardFlashVars($ks, $type = '', $kshowId = -2, $partner_data = '',  $comment = false, $delegate = '') {

        $sessionUserId = '';
        $sessionUser = KalturaHelpers::getSessionUser();
        $config = KalturaHelpers::getServiceConfiguration();

        $flashVars = array();

        $flashVars['userId'] = $sessionUser->userId;
        $flashVars['sessionId'] = $ks;

        if ($sessionUserId == KalturaSettings_ANONYMOUS_USER_ID) {
            $flashVars['isAnonymous'] = true;
        }

        $flashVars['partnerId']     = $config->partnerId;
        $flashVars['kshow_id']      = $kshowId;
        $flashVars['afterAddentry'] = 'onContributionWizardAfterAddEntry';
        $flashVars['close']         = 'onContributionWizardClose';
        $flashVars['partnerData']   = $partner_data;

        if (!$comment) {
            if ($type == KalturaEntryType::MEDIA_CLIP) {
                $flashVars['uiConfId'] = get_player_uiconf('player_uploader');
            } else {
                $flashVars['uiConfId']  = get_player_uiconf('player_mix_uploader');
            }
        } else {
            $flashVars['uiConfId']      = KalturaSettings_CW_COMMENTS_UICONF_ID; //TODO - not sure what this is
            $flashVars['terms_of_use']  = KALTURA_TERMS_OF_USE;
        }

//        if (!empty($delegate)) {
//            $flashVars['Delegate'] = $delegate;
//        }

        return $flashVars;
    }

    function getSimpleEditorFlashVars($ks, $kshowId, $type, $partner_data) {
        $sessionUser = KalturaHelpers::getSessionUser();
        $config = KalturaHelpers::getServiceConfiguration();

        $flashVars = array();

        if($type == 'entry') {
            $flashVars['entry_id'] 		= $kshowId;
            $flashVars['kshow_id'] 		= 'entry-'.$kshowId;
        } else {
            $flashVars['entry_id'] 		= -1;
            $flashVars['kshow_id'] 		= $kshowId;
        }

        $flashVars['partner_id'] 	= $config->partnerId;
        $flashVars['partnerData'] 	= $partner_data;
        $flashVars['subp_id'] 		= $config->partnerId * 100;
        $flashVars['uid'] 			= $sessionUser->userId;
        $flashVars['ks'] 			= $ks;
        $flashVars['backF'] 		= 'onSimpleEditorBackClick';
        $flashVars['saveF'] 		= 'onSimpleEditorSaveClick';
        $flashVars['uiConfId'] 		= get_player_uiconf('player_editor');

        return $flashVars;
    }

	function getKalturaPlayerFlashVars($ks, $kshowId = -1, $entryId = -1) {
        $sessionUser = KalturaHelpers::getSessionUser();
//		$config = KalturaHelpers::getServiceConfiguration();

        $flashVars = array();

//		$flashVars["kshowId"] 		= $kshowId;
//		$flashVars["entryId"] 		= $entryId;
//		$flashVars["partner_id"] 	= $config->partnerId;
//		$flashVars["subp_id"] 		= $config->subPartnerId;
        $flashVars['externalInterfaceDisabled'] = 0;
        $flashVars['uid'] 			= $sessionUser->userId;
//		$flashVars["ks"] 			= $ks;

        return $flashVars;
    }

    function flashVarsToString($flashVars) {

        $flashVarsStr = '';

        foreach($flashVars as $key => $value) {
            $flashVarsStr .= ($key . '=' . urlencode($value) . '&');
        }

        return substr($flashVarsStr, 0, strlen($flashVarsStr) - 1);
	}

    function getSwfUrlForBaseWidget() {
        return KalturaHelpers::getSwfUrlForWidget(KalturaSettings_BASE_WIDGET_ID); // TODO: find out what this ID is and if it be retrieved via API calls
    }

    function getSwfUrlForWidget($widgetId) {
        return KalturaHelpers::getKalturaServerUrl() . '/kwidget/wid/_' . $widgetId;
    }

    function getContributionWizardUrl($type) {

        if ($type == KalturaEntryType::MEDIA_CLIP) {
            return KalturaHelpers::getKalturaServerUrl() .
                  '/kcw/ui_conf_id/' .
                  get_player_uiconf('player_uploader');
        } else {
			  return KalturaHelpers::getKalturaServerUrl() .
                     '/kcw/ui_conf_id/' .
                     get_player_uiconf('player_mix_uploader');
        }
    }

    function getPlayer($type, $design) {

        $full_name = 'kaltura_player_' . ($type == KalturaEntryType::MEDIA_CLIP ? 'regular_' : 'mix_') . $design;

        //$playerid = get_field('config', 'value', 'name', $full_name);
        $playerid = get_config(KALTURA_PLUGIN_NAME, $full_name);

        return $playerid;
    }

    function getDesigns($type) {

        global $CFG;

        $arr    = array();
        $sql    = '';
        $like   = sql_ilike();

        if ($type == KalturaEntryType::MEDIA_CLIP) {


            $arr['dark']  = get_string('kaltura_player_regular_dark', 'block_kaltura');
            $arr['light'] = get_string('kaltura_player_regular_light', 'block_kaltura');

        } else {

            $arr['dark']  = get_string('kaltura_player_mix_dark', 'block_kaltura');
            $arr['light'] = get_string('kaltura_player_mix_light', 'block_kaltura');

        }

        return $arr;
    }

    function getPlayers($type) {

        global $CFG;

        $arr    = array();
        $sql    = '';
        $like   = sql_ilike();

        if ($type == KalturaEntryType::MEDIA_CLIP) {


            $sql = "SELECT id, name, value FROM {$CFG->prefix}config_plugins WHERE plugin = '". KALTURA_PLUGIN_NAME .
                   "' AND name $like 'kaltura_player_regular%'";
            $temp_arr = get_records_sql($sql);
        } else {
            $sql = "SELECT id, name, value FROM {$CFG->prefix}config_plugins WHERE plugin = '". KALTURA_PLUGIN_NAME .
                   "' AND name $like 'kaltura_player_mix%'";
            $temp_arr = get_records_sql($sql);
        }

        foreach($temp_arr as $k=>$v) {

            $parts =  explode ("_", $v->name); // the convention is player_mix_THENAME or player_regular_THENAME
            $arr[$parts[count($parts)-1]] = $v->value;
        }

        return $arr;
    }

    function getSimpleEditorUrl($uiConfId = null) {
        if ($uiConfId) {
            return KalturaHelpers::getKalturaServerUrl() . '/kse/ui_conf_id/' . $uiConfId;
        } else {
            return KalturaHelpers::getKalturaServerUrl() . '/kse/ui_conf_id/' . KALTURA_PLAYER_PLAYEREDITOR;
        }
    }

    function getThumbnailUrl($widgetId = null, $entryId = null, $width = 240, $height= 180) {

        $config = KalturaHelpers::getServiceConfiguration();
        $url = KalturaHelpers::getKalturaServerUrl();
        $url .= "/p/" . $config->partnerId;
        $url .= "/sp/" . $config->partnerId * 100;
        $url .= "/thumbnail";

        if ($widgetId) {
            $url .= "/widget_id/" . $widgetId;
        } elseif ($entryId) {
            $url .= "/entry_id/" . $entryId;
            $url .= "/width/" . $width;
            $url .= "/height/" . $height;
            $url .= "/type/2";
            $url .= "/bgcolor/000000";
            return $url;
        }
    }

    function getPlatformConfig() {
        if (self::$platfromConfig != null) {
            return self::$platfromConfig;
        }

        $activeServices = DekiService::getSiteList(DekiService::TYPE_EXTENSION, true);

        foreach ($activeServices as $aService) {

            if ($aService->getName() == 'Kaltura') {
                self::$platfromConfig = $aService;
                return $aService;
            }
        }

        return null;

    }

    function getPlatformKey($key = '', $default = '') {

        $val = get_config(KALTURA_PLUGIN_NAME, $key);

        if ($val == null ||  strlen($val) == 0) {
            return $default;
        }

        return $val;
    }

    function getServiceConfiguration() {

        $partnerId = KalturaHelpers::getPlatformKey('kaltura_partner_id', '0');

        $config = new KalturaConfiguration($partnerId);
        $config->serviceUrl = KalturaHelpers::getKalturaServerUrl();
        $config->setLogger(new KalturaLogger());

        return $config;
	}

    function getKalturaServerUrl() {
        $url = KalturaHelpers::getPlatformKey('kaltura_uri', KALTURA_DEFAULT_URI);

        if ($url == '') {
            $url = KALTURA_DEFAULT_URI;
        }

        // remove the last slash from the url
        $url = kaltura_format_url($url, false, true);
//        if (substr($url, strlen($url) - 1, 1) == '/') {
//            $url = substr($url, 0, strlen($url) - 1);
//        }

        return $url;
    }

    function getSessionUser() {
        global $USER;

        $kalturaUser = new KalturaUser();

        if ($USER->id) {
            $kalturaUser->userId= $USER->id;
            $kalturaUser->screenName = $USER->username;
        } else {
            $kalturaUser->userId = KalturaSettings_ANONYMOUS_USER_ID; //TODO - Leaving this as it is for now.
        }

        return $kalturaUser;
    }

    function getKalturaClient($isAdmin = false, $privileges = null) {

        // get the configuration to use the kaltura client
        $kalturaConfig = KalturaHelpers::getServiceConfiguration();
        $sessionUser = KalturaHelpers::getSessionUser();

        if(!$privileges) {
            $privileges = 'edit:*';
        }

        // inititialize the kaltura client using the above configurations
        $kalturaClient = new KalturaClient($kalturaConfig);

        // get the current logged in user
        $user = $sessionUser->userId;

        if ($isAdmin) {

            $adminSecret = KalturaHelpers::getPlatformKey('kaltura_adminsecret', '');
            $type = KalturaSessionType::ADMIN;

            $ksId = $kalturaClient->session->start($adminSecret, $user, $type, -1, 86400, $privileges);
        } else {

            $secret = KalturaHelpers::getPlatformKey('kaltura_secret', '');
            $type = KalturaSessionType::USER;
            $partnerid = KalturaHelpers::getPlatformKey('kaltura_partner_id', '');

            $ksId = $kalturaClient->session->start($secret, $user, $type, $partnerid, 86400, $privileges);

        }

        $kalturaClient->setKs($ksId);

        return $kalturaClient;
    }

    function doHttpRequest($url, $params = array(), $files = array()) {
        if (function_exists('curl_init')) {
            return KalturaHelpers::doCurl($url, $params, $files);
        } else {
            return KalturaHelpers::doPostRequest($url, $params, $files);
        }
    }

	/**
	 * Curl HTTP POST Request
	 *
	 * @param string $url
	 * @param array $params
	 * @return array of result and error
	 */
    function doCurl($url, $params = array(), $files = array()) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);

        if (count($files) > 0) {
            foreach($files as &$file) {
                $file = "@".$file; // let curl know its a file
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($params, $files));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, null, "&"));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, '');

        if (count($files) > 0) {
			curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        } else {
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        }

        $result = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        return array($result, $curlError);
	}

    /**
     * HTTP stream context request
     *
     * @param string $url
     * @param array $params
     * @return array of result and error
     */
    function doPostRequest($url, $params = array(), $files = array()) {
        if (count($files) > 0) {
			throw new Exception("Uploading files is not supported with stream context http request, please use curl");
        }

        $formattedData = http_build_query($params , '', '&');
        $params = array('http' => array(
					               'method' => 'POST',
					               "Accept-language: en\r\n".
					               "Content-type: application/x-www-form-urlencoded\r\n",
					               'content' => $formattedData
		                ));

        $ctx = stream_context_create($params);

        $fp = @fopen($url, 'rb', false, $ctx);

        if (!$fp) {
            $phpErrorMsg = '';
            throw new Exception("Problem with $url, $phpErrorMsg");
        }

        $response = @stream_get_contents($fp);

        if ($response === false) {
            throw new Exception("Problem reading data from $url, $phpErrorMsg");
        }

        return array($response, '');

    }

    function kaltura_check_version() {

        //prepare the field values being posted to the service
        $data = array(
            'method' => '"node.get"',
            'nid' => '"65"',
        );

        try {
            $result = KalturaHelpers::doHttpRequest('http://exchange.kaltura.com/services/json', $data);
        } catch (Exception $e) {
            return array('', '');
        }

    //moodle 65
      //make the request
        if (empty($result[1])) {
            $result = json_decode($result[0]);
        } else {
            return array('', '');
        }

        $downloadUrl = "";

        if (substr($result->{"#data"}->field_download_bundle[0]->filepath, 0, 4) != 'http') {
            $downloadUrl = 'http://exchange.kaltura.com/' . $result->{"#data"}->field_download_bundle[0]->filepath;
        } else {
            $downloadUrl = $result->{"#data"}->field_download_bundle[0]->filepath;
        }

        return array($result->{"#data"}->field_application_version[0]->value, $downloadUrl);
    }

}
?>
