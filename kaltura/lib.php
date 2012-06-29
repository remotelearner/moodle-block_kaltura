<?php
defined('MOODLE_INTERNAL') || die();

define('KALTURA_PLUGIN_NAME', 'block_kaltura');
define('KALTURA_TERMS_OF_USE', 'http://corp.kaltura.com/tandc');
define('KALTURA_DEFAULT_URI', 'http://www.kaltura.com/');

// Constants related to kaltura players
define('KALTURA_PLAYER_COUNT', 8);

// Legacy Player IDs
define('KALTURA_LEGACY_PLAYER_PLAYEREDITOR',            1002226);
define('KALTURA_LEGACY_PLAYER_UPLOADERREGULAR',         1002217);
define('KALTURA_LEGACY_PLAYER_UPLOADERMIX',             1002225);
define('KALTURA_LEGACY_PLAYER_PLAYERREGULARDARK',       1002712);
define('KALTURA_LEGACY_PLAYER_PLAYERREGULARLIGHT',      1002711);
define('KALTURA_LEGACY_PLAYER_PLAYERMIXDARK',           1002259);
define('KALTURA_LEGACY_LEGACY_PLAYER_PLAYERMIXLIGHT',   1002260);
define('KALTURA_LEGACY_PLAYER_PLAYERVIDEOPRESENTATION', 1003069);

// New Player IDs
define('KALTURA_PLAYER_PLAYEREDITOR',                   4395711);
define('KALTURA_PLAYER_UPLOADERREGULAR',                7632751);
define('KALTURA_PLAYER_UPLOADERMIX',                    4395701);
define('KALTURA_PLAYER_PLAYERREGULARDARK',              4674741);
define('KALTURA_PLAYER_PLAYERREGULARLIGHT',             4674731);
define('KALTURA_PLAYER_PLAYERMIXDARK',                  4860321);
define('KALTURA_PLAYER_PLAYERMIXLIGHT',                 4860311);
define('KALTURA_PLAYER_PLAYERVIDEOPRESENTATION',        4860481);

define('KALTURA_CACHE_REFRESH', 12); // Minutes before the Kaltura cache refreshes

require_once('client/kaltura_settings.php');
require_once('client/KalturaClientBase.php');
require_once('client/KalturaClient.php');
require_once('client/kaltura_logger.php');
require_once('client/kaltura_helpers.php');
require_once('locallib.php');
require_once('jsportal.php');

class KalturaPlayerSize {
    const LARGE = 1;
    const SMALL = 2;
    const CUSTOM = 3;
}

class kaltura_entry {

  public $id = 0;
  public $entry_id = '';
  public $dimensions = KalturaAspectRatioType::ASPECT_4_3;
  public $size = KalturaPlayerSize::LARGE;
  public $custom_width = 0;
  public $design = 'light';
  public $title = '';
  public $context = '';
  public $entry_type = KalturaEntryType::MEDIA_CLIP;
  public $media_type = KalturaMediaType::VIDEO;
}

function kaltura_get_types() {//this prevent from the blocks/kaltua itself to appear as an activity
  return array();
}

function get_cw_wizard($div, $width, $height, $type) {

  $client       = KalturaHelpers::getKalturaClient();
  $swfUrl       = KalturaHelpers::getContributionWizardUrl($type);
  $flashVars    = KalturaHelpers::getContributionWizardFlashVars($client->getKS(), $type); // Returns an array of parameters to pass
  $flashVarsStr = KalturaHelpers::flashVarsToString($flashVars);

  $flash_embed = '
    <div id="' . $div . '"></div>
    <script type="text/javascript">
      // This block of code is responsible for changing the id of the "page" and "content"
      // div elements within the iframe in order to prevent Moodle themese from applying
      // theme style to the popup window
      var iframe = get_field_obj("kaltura_modal_iframe");
      var iframedoc = "";

      if (iframedoc.contentDocument) {
          iframedoc = iframe.contentDocument;
      } else {
          iframedoc = iframe.contentWindow.document;
      }

      var iframediv = iframedoc.getElementById("page");
      iframediv.id = "kaltura-iframe-page";

      var iframediv = iframedoc.getElementById("content");
      iframediv.id = "kaltura-iframe-content";

      // Setup SWFObject and write it to the div element
      var kso = new SWFObject("'. $swfUrl .'", "KalturaCW", "'. $width .'", "'. $height .'", "9", "#ffffff");
      kso.addParam("flashVars", "'. $flashVarsStr .'");
      kso.addParam("allowScriptAccess", "always");
      kso.addParam("allowFullScreen", "TRUE");
      kso.addParam("allowNetworking", "all");
      if(kso.installedVer.major >= 9) {
        kso.write("' . $div . '");
      } else {
        document.getElementById("' . $div . '").innerHTML = "Flash player version 9 and above is required. <a href=\"http://get.adobe.com/flashplayer/\">Upgrade your flash version</a>";
      }
    </script>
  ';

    return $flash_embed;
}

function get_se_wizard($div, $width, $height,$entryId) {

    $params = "''";
    $url = "''";
    $platformUser = "\"" . KalturaHelpers::getSessionUser()->userId . "\"";
    $kalturaSecret = KalturaHelpers::getPlatformKey('kaltura_secret', '');

    if ($kalturaSecret != null && strlen($kalturaSecret) > 0) {
        try {
            $kClient        = new KalturaClient(KalturaHelpers::getServiceConfiguration());
            $kalturaUser    = KalturaHelpers::getPlatformKey('user', '');
            $ksId           = $kClient->session->start($kalturaSecret, $kalturaUser, KalturaSessionType::USER, null, 86400, '*');
            $kClient->setKs($ksId );
            $player         = get_player_uiconf('player_editor');
            $url            = KalturaHelpers::getSimpleEditorUrl($player);
            $params         = KalturaHelpers::flashVarsToString(KalturaHelpers::getSimpleEditorFlashVars($ksId,$entryId, 'entry', ''));
        } catch(Exception $exp) {
          $flash_embed = $exp->getMessage();
        }

        $flash_embed = '
            <div id="'. $div .'" style="width:'.$width.'px;height:'.$height.'px;">
            <script type="text/javascript">
            // This block of code is responsible for changing the id of the "page" and "content"
            // div elements within the iframe in order to prevent Moodle themese from applying
            // theme style to the popup window
            var iframe = get_field_obj("kaltura_modal_iframe");
            var iframedoc = "";

            if (iframedoc.contentDocument) {
                iframedoc = iframe.contentDocument;
            } else {
                iframedoc = iframe.contentWindow.document;
            }

            var iframediv = iframedoc.getElementById("page");
            iframediv.id = "kaltura-iframe-page";

            var iframediv = iframedoc.getElementById("content");
            iframediv.id = "kaltura-iframe-content";

            var kso = new SWFObject("'. $url .'", "KalturaSW", "'. $width .'", "'. $height .'", "9", "#ffffff");
            kso.addParam("flashVars", "'. $params .'");
            kso.addParam("allowScriptAccess", "always");
            kso.addParam("allowFullScreen", "TRUE");
            kso.addParam("allowNetworking", "all");

            if(kso.installedVer.major >= 9) {
                kso.write("' . $div . '");
            } else {
                document.getElementById("' . $div . '").innerHTML = "Flash player version 9 and above is required. <a href=\"http://get.adobe.com/flashplayer/\">Upgrade your flash version</a>";
            }
             </script>';

        return $flash_embed;
    }
}

function get_se_js_functions($thumbUrl) {
   global $CFG, $USER;

   $kalturaprotal = new kaltura_jsportal();
   $output = $kalturaprotal->print_javascript(
                             array(
                               'wwwroot'  => $CFG->wwwroot,
                               'ssskey'   => $USER->sesskey,
                               'userid'   => $USER->id,
                               'thumburl' => $thumbUrl,
                             ),
                             false,
                             false);

      return '';
}

function get_cw_js_functions($type, $props = array() /*$divCW, $updateField, $divProps=''*/) {
    global $CFG, $USER;

    $kalturaprotal = new kaltura_jsportal();
    $javaprops = array('wwwroot'        => $CFG->wwwroot,
                       'ssskey'         => $USER->sesskey,
                       'userid'         => $USER->id,
                       'entrymixtype'   => KalturaEntryType::MIX,
                       'entrymediatype' => KalturaEntryType::MEDIA_CLIP,
                       'type'           => $type);

    // Add extra properties
    foreach ($props as $key => $value) {
        $javaprops[$key] = $value;
    }

    $output = $kalturaprotal->print_javascript(
                             $javaprops,
                             false,
                             false);

 $javascript = '<script type="text/javascript">
    type = ' . $type . ';
    local_entry_id = "";
    </script>';

    return $javascript;
}

function get_cw_props_player($div, $type, $width, $height) {

    $players    = array();
    $player = new stdClass();

    if (KalturaEntryType::MEDIA_CLIP == $type) {

        $players[0] = new stdClass();
        $players[0]->index = 'dark';
        $players[0]->uiconf = get_player_uiconf('player_dark');

        $players[1] = new stdClass();
        $players[1]->index = 'light';
        $players[1]->uiconf = get_player_uiconf('player_light');

    } else {

        $players[0] = new stdClass();
        $players[0]->index = 'dark';
        $players[0]->uiconf = get_player_uiconf('player_mix_dark');

        $players[1] = new stdClass();
        $players[1]->index = 'light';
        $players[1]->uiconf = get_player_uiconf('player_mix_light');

    }

    $partnerId  = KalturaHelpers::getPlatformKey('kaltura_partner_id', '0');
    $swfUrl     = KalturaHelpers::getSwfUrlForWidget($partnerId);
    $swfUrl     .=  '/uiconf_id/';

    $flash_embed = '
        <script type="text/javascript">
        function show_entry_player(entryId, design) {

            var playerId = 0;';

    $flash_embed .= '
            switch (design) {';

                foreach($players as $data) {
                    $flash_embed .= '      case "' . $data->index . '": playerId=' . $data->uiconf . ';break;';
                }

      $flash_embed .= '      }

            var kso = new SWFObject("'. $swfUrl .'" + playerId + "/entry_id/" + entryId, "' . $div. '", "'. $width .'", "'. $height .'", "9", "#ffffff");
            kso.addParam("allowScriptAccess", "always");
            kso.addParam("allowFullScreen", "TRUE");
            kso.addParam("allowNetworking", "all");

            if (kso.installedVer.major >= 9) {
                kso.write("' . $div . '");
            } else {
                document.getElementById("' . $div . '").innerHTML = "Flash player version 9 and above is required. <a href=\"http://get.adobe.com/flashplayer/\">Upgrade your flash version</a>";
            }
    }
    </script>';

    return $flash_embed;
}

function get_cw_properties_pane($entry, $type) {

    $designs = KalturaHelpers::getDesigns($type);
    $javascript= '
        <style type="text/css">
            #slctDesign {
                width: 121px;
            }

            #inpCustomWidth {
                width: 60px;
            }

            #inpTitle {
                width: 238px;
            }

    </style>

    <!-- <div id="kaltura-divClipProps" style="display:none;margin-top:-20px"> -->
    <div id="kaltura-divClipProps" style="display:none;">
        <div id="divClip">
        </div>
        <div id="divUserSlected">
            <p>
                <span style="font-weight:bold;">' . get_string('title', 'block_kaltura') . '</span>&nbsp;<input id="inpTitle" title="Title:" type="text" value=""/></p>
                <script type="text/javascript">
                if (document.getElementById("inpTitle").value == "") {
                    document.getElementById("inpTitle").value = get_field("id_name");
                }
                </script>

            <!-- RL - EDIT -->
            <div id="divRefresh">
                <span style="font-weight:bold;">' . get_string('playerrefreshscreen', 'block_kaltura') . '</span>
                <input type="submit" name="refreshpage" onclick="change_entry_player();" value="'. get_string('playerrefresh', 'block_kaltura') .'">
            </div>
            <!-- RL - EDIT END -->
            <div id="divDesign">
                <span style="font-weight:bold;">' . get_string('playerdesign', 'block_kaltura') . '</span>
                <select id="slctDesign" name="slctDesign" onchange="change_entry_player();">';
                    foreach ($designs as $desKey => $desValue) {

                      $javascript .= '<option value="' . $desKey . '"' . ( $desKey == 'light' ? 'selected' : '' ) . '>' . $desValue .'</option>';

                    }
        $javascript .= '         </select>
            </div>
            <div id="divDim">
                <table><tr><td valign="top" style="font-weight:bold;">' . get_string('playerdimensions', 'block_kaltura') . '</td><td valign="top">
                    <input id="dimNorm" ' . ( $entry->dimensions == KalturaAspectRatioType::ASPECT_4_3 ? 'checked="checked"' : '' ) . ' name="grpDimension" type="radio" onclick="update_field(\'id_dimensions\',\'' . KalturaAspectRatioType::ASPECT_4_3 .'\', false, \'\');document.getElementById(\'lrgPlayer\').innerHTML=\'365\';document.getElementById(\'smlPlayer\').innerHTML=\'260\'" />' . get_string('normal', 'block_kaltura') . '
                    <p><input id="dimWide" ' . ( $entry->dimensions == KalturaAspectRatioType::ASPECT_16_9 ? 'checked="checked"' : '' ) . ' name="grpDimension" type="radio" onclick="update_field(\'id_dimensions\',\'' .KalturaAspectRatioType::ASPECT_16_9 .'\', false, \'\');document.getElementById(\'lrgPlayer\').innerHTML=\'290\';document.getElementById(\'smlPlayer\').innerHTML=\'211\'" />' . get_string('widescreen', 'block_kaltura') . '</p></td></tr></table>
             </div>
            <div id="divSize">
                <table><tr><td valign="top" style="font-weight:bold;">' . get_string('playersize', 'block_kaltura') . '</td><td valign="top">

                    <input id="sizeLarge" ' . ( $entry->size == KalturaPlayerSize::LARGE ? 'checked="checked"' : '' ) . ' name="grpSize" type="radio" onclick="update_field(\'id_size\',\'' . KalturaPlayerSize::LARGE .'\', false, \'\')" />' . get_string('largeplayer', 'block_kaltura') . '
                <p>
                    <input id="sizeSmall" ' . ( $entry->size == KalturaPlayerSize::SMALL ? 'checked="checked"' : '' ) . ' name="grpSize" type="radio"  onclick="update_field(\'id_size\',\'' . KalturaPlayerSize::SMALL .'\', false, \'\')" />' . get_string('smallplayer', 'block_kaltura') . '</p>
                <p>
                    <input id="sizeCustom" ' . ( $entry->size == KalturaPlayerSize::CUSTOM ? 'checked="checked"' : '' ) . ' name="grpSize" type="radio" onclick="update_field(\'id_size\',\'' . KalturaPlayerSize::CUSTOM .'\', false, \'\')" />' . get_string('customwidth', 'block_kaltura')
                    . '&nbsp;<input id="inpCustomWidth" type="text" value="' . ( $entry->size == KalturaPlayerSize::CUSTOM ?  $entry->custom_width : '' ) . '" onfocus="document.getElementById(\'sizeCustom\').checked=true;update_field(\'id_size\',\'' . KalturaPlayerSize::CUSTOM .'\', false, \'\')"/></p></td></tr></table>
            </div>
        </div>
        <div id="divButtons">
                <input id="btnInserResource" type="button" value="' . get_string('insertintopost', 'block_kaltura') . '" onclick="disable_add_vid_button(); insert_into_post();"/> <input id="btnCancelResource" type="button" value="' . get_string('cancelpost', 'block_kaltura') . '" onclick="setTimeout(\'window.parent.kalturaCloseModalBox();\',0);"/>
        </div>
    </div>';

    return $javascript;
}

/**
 * This functions initializes the kaltura main javascript class
 * and includes the kaltura.lib.js script.
 *
 * @param string $div - div ID attribute that will have it's inner HTML re-written
 *
 * @param string $field - element ID that includes the widget ID
 *
 * @param bool $funccall - true to call the javascript function after the kaltura
 * main javascript class variables have been initialized.  False to not make the call.
 * This sometimes needed because of how the original author was originally using this method.
 * Example value passed: show_wait();
 *
 * @param bool $return - true to return the javascript or false to echo it directly
 */
function get_wait_image($div, $field, $funccall = false, $return = false) {
    global $CFG, $USER;

    require_js($CFG->wwwroot . '/blocks/kaltura/js/kaltura.lib.js');

    if ($funccall) {
        $funccall = 'show_wait();';
    } else {
        $funccall = '';
    }

   $kalturaprotal = new kaltura_jsportal();
   $output = $kalturaprotal->print_javascript(
                             array(
                               'wwwroot' => $CFG->wwwroot,
                               'ssskey' => $USER->sesskey,
                               'userid' => $USER->id,
                               'param1' => $div,
                               'param2' => $field,
                               'videoconversion' => get_string('videoconversion', 'resource_kalturavideo'),
                               'clickhere' => get_string('clickhere', 'resource_kalturavideo'),
                               'convcheckdone' => get_string('convcheckdone', 'resource_kalturavideo'),
                             ),
                             $funccall,
                             $return);

   // TODO: Look for all call of this method and make sure they aren't expecting javascript to be returned
   return $output;

}

function embed_kaltura($entryId, $width, $height, $type, $design, $show_links = false) {

    global $CFG;

    if (KalturaEntryType::MEDIA_CLIP == $type) {

        $playerId = get_player_uiconf('player_' . $design);
    } else {

        $playerId = get_player_uiconf('player_mix_' . $design);
    }

    $partnerId          = KalturaHelpers::getPlatformKey('kaltura_partner_id', '0');
    $swfUrl             = KalturaHelpers::getSwfUrlForWidget($partnerId);
    $swfUrl             .=  "/uiconf_id/$playerId/entry_id/" . $entryId;
    $flashVarsStr       = KalturaHelpers::flashVarsToString(KalturaHelpers::getKalturaPlayerFlashVars(/*$client->getKS()*/ '', -1, $entryId));
    $div_id             = 'kaltura_wrapper_' . $entryId;
    $kaltura_poweredby  = '<div style="width:' . $width . 'px;padding-top:6px;font-size:9px;text-align:right"'.
                            '><a href="http://corp.kaltura.com/technology/video_player" target="_blank">Video Player'.
                            '</a> by <a href="http://corp.kaltura.com" target="_blank">Kaltura</a></div>';

    if ($show_links == false) {

        $kaltura_poweredby = '';
    }

   require_js($CFG->wwwroot . '/blocks/kaltura/js/kaltura.main.js');

   $kalturaprotal = new kaltura_jsportal();
   $output = $kalturaprotal->print_javascript(
                             array(
                               'wwwroot' => $CFG->wwwroot,
                             ),
                             false,
                             false);

    $align = '';
    $custom_style = '';
    $links = '<a href=" http://corp.kaltura.com/solutions/education">education video</a><a href="http://corp.kaltura.com/userzone/tutorials">video tutorials</a><a href="http://corp.kaltura.com/technology/video_player">flv player</a>';

    $html = '<div id="'. $div_id .'" class="kaltura_wrapper" style="'. $align . $custom_style .'"'
            .'>'. $links .'</div>'. $kaltura_poweredby;
    $html .= '<script type="text/javascript">
                  // This block of code is responsible for changing the id of the "page" and "content"
                  // div elements within the iframe in order to prevent Moodle themese from applying
                  // theme style to the popup window
                  var iframe = get_field_obj("kaltura_modal_iframe");
                  var iframedoc = "";

                  if (iframe) {
                      if (iframedoc.contentDocument) {
                          iframedoc = iframe.contentDocument;
                      } else {
                          iframedoc = iframe.contentWindow.document;
                      }

                      var iframediv = iframedoc.getElementById("page");
                      iframediv.id = "kaltura-iframe-page";

                      var iframediv = iframedoc.getElementById("content");
                      iframediv.id = "kaltura-iframe-content";

                  }

                  var kaltura_swf = new SWFObject("'. $swfUrl .'", "'. $playerId .'", "'. $width .'", "'. $height .'", "9", "#ffffff");
                  kaltura_swf.addParam("wmode", "opaque");
                  kaltura_swf.addParam("allowScriptAccess", "always");
                  kaltura_swf.addParam("allowFullScreen", "TRUE");
                  kaltura_swf.addParam("allowNetworking", "all");
                  kaltura_swf.addParam("flashVars", "'. $flashVarsStr .'");
                  if(kaltura_swf.installedVer.major >= 9) {
                      kaltura_swf.write("'. $div_id .'");
                  } else {
                      document.getElementById("'. $div_id .'").innerHTML = "Flash player version 9 and above is required. <a href=\'http://get.adobe.com/flashplayer/\'>Upgrade your flash version</a>";
                  }</script>';

    return $html;
}


function embed_kswfdoc($entryId, $width, $height, $context_id) {
    global $CFG, $USER;

    $client               = KalturaHelpers::getKalturaClient();
    $kswf_player          = get_player_uiconf('player_presentation');
    $partnerId            = KalturaHelpers::getPlatformKey('kaltura_partner_id','0');
    $swfUrl               = KalturaHelpers::getSwfUrlForWidget($partnerId);
    $div_id               = 'kaltura_wrapper_' . $entryId;
    $div_container_id     = 'kaltura_container_' . $entryId;
    $kaltura_poweredby    = '';
    $align                = '';//'margin-top:-20px';
    $custom_style         = '';
    $links                = '';
    $config               = $client->getConfig();


    require_js($CFG->wwwroot . '/blocks/kaltura/js/kaltura.main.js');

    $kalturaprotal = new kaltura_jsportal();
    $output = $kalturaprotal->print_javascript(
                             array(
                               'wwwroot' => $CFG->wwwroot,
                             ),
                             false,
                             false);

    $context = get_context_instance(CONTEXT_COURSE, $context_id);

    if (has_capability('moodle/course:manageactivities', $context)) { //check if admin of this widget
        //is admin

        $entryparam = optional_param('entry', '', PARAM_NOTAGS);
        $kc = KalturaHelpers::getKalturaClient(0, 'edit:'. $entryparam);
        $adminvars = '"&adminMode=true"+"&partnerid='.$config->partnerId.'"+"&subpid='.$config->partnerId*100 . '"+"&ks='.$client->getKs().'"+"&uid='.$USER->id.'"';
    } else {
          //is student
        $kc = KalturaHelpers::getKalturaClient(0,0);
        $adminvars = '"&adminMode=false"+"&partnerid='.$config->partnerId.'"+"&subpid='.$config->partnerId*100 . '"+"&ks='.$client->getKs().'"+"&uid='.$USER->id.'"';
    }

    // TODO: This should be using constants
    $host = 'www.kaltura.com';

    if ($client->getConfig()->serviceUrl != 'http://www.kaltura.com') {
        $host = str_replace('http://', '', $kc->config->serviceUrl);
    }

    $flashVarsStr = '"showCloseButton=false"+"&close=onContributionWizardClose"+"&host='.$host.'"+'.$adminvars.'+"&debugMode=1" +"&kshowId=-1"+"&pd_sync_entry='.$entryId.'"';//.

    $html = '
      <div id="'. $div_container_id .'" class="kaltura_wrapper" style="'. $align . $custom_style .'"' .'>'. $links .'</div>'.
      $kaltura_poweredby;
      $html .= '<script type="text/javascript">
                  // This block of code is responsible for changing the id of the "page" and "content"
                  // div elements within the iframe in order to prevent Moodle themese from applying
                  // theme style to the popup window
                  var iframe = get_field_obj("kaltura_modal_iframe");
                  var iframedoc = "";

                  if (iframe) {
                      if (iframedoc.contentDocument) {
                          iframedoc = iframe.contentDocument;
                      } else {
                          iframedoc = iframe.contentWindow.document;
                      }

                      var iframediv = iframedoc.getElementById("page");
                      iframediv.id = "kaltura-iframe-page";

                      var iframediv = iframedoc.getElementById("content");
                      iframediv.id = "kaltura-iframe-content";

                  }

                  var kaltura_swf = new SWFObject("'.$swfUrl.'/uiconf_id/'.$kswf_player.'", "' .$div_id . '", "'. $width .'", "'. $height .'", "9", "#ffffff");
                  kaltura_swf.addParam("flashVars", '. $flashVarsStr .');
                  kaltura_swf.addParam("wmode", "opaque");
                  kaltura_swf.addParam("allowScriptAccess", "always");
                  kaltura_swf.addParam("allowFullScreen", "TRUE");
                  kaltura_swf.addParam("allowNetworking", "all");

                  if(kaltura_swf.installedVer.major >= 9) {
                    kaltura_swf.write("'. $div_container_id .'");
                  } else {
                    document.getElementById("'. $div_id .'").innerHTML = "Flash player version 9 and above is required. <a href=\'http://get.adobe.com/flashplayer/\'>Upgrade your flash version</a>";
                  }</script>';

    return $html;
}

function get_height($entry) {

    if ($entry->dimensions == KalturaAspectRatioType::ASPECT_4_3) {

        switch($entry->size) {
            case KalturaPlayerSize::LARGE:
                return 365;
                break;
            case KalturaPlayerSize::SMALL:
                return 260;
                break;
            case KalturaPlayerSize::CUSTOM:
                return $entry->custom_width*3/4 + 65;
                break;
            default:
                return 365;
                break;
        }
    } else {

        switch($entry->size) {
            case KalturaPlayerSize::LARGE:
                return 290;
                break;
            case KalturaPlayerSize::SMALL:
                return 211;
                break;
            case KalturaPlayerSize::CUSTOM:
                return $entry->custom_width*9/16 + 65;
                break;
            default:
                return 290;
                break;
        }

    }
}

function get_width($entry) {

    switch($entry->size) {
        case KalturaPlayerSize::LARGE:
            return 400;
            break;
        case KalturaPlayerSize::SMALL:
            return 260;
            break;
        case KalturaPlayerSize::CUSTOM:
            return $entry->custom_width;
            break;
        default:
            return 400;
            break;
    }
}

/**
 * Converts a URL to include the protocol or removes a trailing slash
 * @param string url - URL to convert
 * @param boolean includeprototl - true to include the protocol HTTP://, false to do nothing
 * @param boolea remoteslash - true to remove the trailing slash from the URL, false to do nothing
 *
 * @return string - returns the converted URL
 */
function kaltura_format_url($url, $includeprotocol = true, $remveslash = true) {
    // Verify if the protolcol is included in the URL
    $hasprotocol = strpos($url, 'http://');

    if ($includeprotocol and false === $hasprotocol) {
        $url = 'http://' . $url;
    }

    // Verify if the trialing slash is included in the url
    $hastrailslash = strrpos($url, '/');

    if ($remveslash and $hastrailslash) {
        if (strlen($url) == $hastrailslash + 1) {
            $url = rtrim($url, '/');
        }
    }

    return $url;
}

/**
 * This function determine how many minutes are left
 * before the kaltura cache refreshes
 *
 * @param int timestamp - The time the video was modified
 * @return array - the first element is a boolean - true for success. The secton
 * element is an int that represents how many minutes until the Kaltura server
 * cache is refreshed
 */
function is_video_cached($video_modified = 0) {

    if (empty($video_modified)) {
        return false;
    }

    $time = time();

    $tenminutes = KALTURA_CACHE_REFRESH * 60;

    $difference = $time - $video_modified;

    if ($difference <= $tenminutes) {

        // Find out how many minutes have elapsed
        $elapsed = $difference / 60;

        $minutes_to_wait = KALTURA_CACHE_REFRESH - $elapsed;

        $minutes_to_wait = round($minutes_to_wait);

        return array(true, $minutes_to_wait);
    } else {
        return array(false, '');
    }
}

/**
 * This function prints javascript used to resize the iframe
 * to accomodate all of the video property settings
 */
function resource_iframe_resize()  {
    $javascript = '<script type="text/javascript">
            // Change the size of the iframe now otherwise the properties pane will be cut off
            var iframe_obj = get_field_obj("kaltura_modal_iframe");
            iframe_obj.style.height = "445px";
        </script>';

    echo $javascript;
}
?>