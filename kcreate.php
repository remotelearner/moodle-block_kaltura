<?php
require_once('../../config.php');

require_login();

require_once('lib.php');
require_once('client/KalturaPlugins/KalturaDocumentClientPlugin.php');

function convert_ppt($entryId) {
    try {
        $kClient = KalturaHelpers::getKalturaClient();

        $document_client = KalturaDocumentClientPlugin::get($kClient);

        $document_url = $document_client->documents->convertPptToSwf($entryId);

////Debug
//$myFile = "/tmp/kcreate.txt";
//$fh = fopen($myFile, 'a');
//fwrite($fh, " -- convert_ppt  -- ");
//fwrite($fh, $document_url);
//fwrite($fh, ' ---- ');
//fclose($fh);

        return 'y:'. $document_url;  // TODO: another hard coded URL.  Need to use the configurable options
    } catch(Exception $exp) {
        return 'n:' . $exp->getMessage();
    }
}


function create_swfdoc($ppt_id, $video_id, $name, $path, $path2 = '') {
    global $USER;

////Debug
//$myFile = "/tmp/kcreate.txt";
//$fh = fopen($myFile, 'a');
//fwrite($fh, " -- create_swfdoc 1st -- ");
//fwrite($fh, ' ---- ');
//fclose($fh);

    $kClient = KalturaHelpers::getKalturaClient();

    $real_path = $kClient->getConfig()->serviceUrl .
                 '/index.php/extwidget/raw/entry_id/' .
                 $ppt_id . '/p/' . $kClient->getConfig()->partnerId .
                 '/sp/' . $kClient->getConfig()->partnerId*100 .
                 '/type/download/format/swf/direct_serve/1';

    $entry_id = $video_id;

    if (strpos($kClient->getConfig()->serviceUrl, 'www.kaltura.com') &&
        strpos($path, 'www.kaltura.com')) {

        $real_path = str_replace('www.kaltura.com', 'cdn.kaltura.com', $real_path);
    }

    $xml = '<sync><video><entryId>'.$entry_id.'</entryId></video><slide><path>'.$real_path.'</path></slide>';
    $xml .= '<times></times></sync>';

    $entry = new KalturaDataEntry();
    $entry->dataContent = $xml;
    $entry->mediaType = KalturaEntryType::DOCUMENT;
    $result = $kClient->data->add($entry);

////Debug
//$myFile = "/tmp/kcreate.txt";
//$fh = fopen($myFile, 'a');
//fwrite($fh, " -- create_swfdoc -- ");
//$stringData = var_export($result, true);
//fwrite($fh, $stringData);
//fwrite($fh, ' ---- ');
//fclose($fh);

    return $result->id;
}

// TODO: use optiona_param and clean this stuff up!
$action = optional_param('kaction', '', PARAM_TEXT);
$ppt    = optional_param('ppt', '', PARAM_TEXT);

////Debug
//$myFile = "/tmp/kcreate.txt";
//$fh = fopen($myFile, 'w');
//fwrite($fh, ' -- action -- ');
//$stringData = $action;
//fwrite($fh, $stringData);
//fwrite($fh, ' -- ppt -- ');
//$stringData = var_export($ppt, true);
//fwrite($fh, $stringData);
//fwrite($fh, ' ---- ');
//fclose($fh);

if (0 == strcmp($action, 'ppt')) {

    if (!empty($ppt)) {

        $entry_id = $ppt;
        die(convert_ppt($entry_id));

    } else {
        die('n:' . get_string('missingfile','kaltura'));
    }

} elseif (0 == strcmp($action, 'swfdoc')) { /*$_POST['action'] == 'swfdoc'*/

    if (!empty($ppt)) {

        $video          = optional_param('video', '', PARAM_TEXT);
        $entryname      = optional_param('name', '', PARAM_TEXT);
        $downloadurl    = optional_param('downloadUrl', '', PARAM_URL);
        $downloadurl2   = optional_param('downloadUrl2', '', PARAM_URL);


        $ppt_id     = $ppt;
        $video_id   = $video;
        $name       = $entryname;
        $url        = $downloadurl;
        $url2       = $downloadurl2;

        $entry_id = create_swfdoc($ppt_id, $video_id, $name, $url, $url2);
////Debug
//$myFile = "/tmp/kcreate.txt";
//$fh = fopen($myFile, 'w');
//fwrite($fh, " -- swfdoc -- ");
//$stringData = var_export($entry_id, true);
//fwrite($fh, $stringData);
//fwrite($fh, ' ---- ');
//fclose($fh);


        echo $entry_id;

    } else {
////Debug
//$myFile = "/tmp/kcreate.txt";
//$fh = fopen($myFile, 'w');
//fwrite($fh, " -- swfdoc -- ");
//$stringData = var_export('no go', true);
//fwrite($fh, $stringData);
//fwrite($fh, ' ---- ');
//fclose($fh);

        echo 'n:' . get_string('missingfile','kaltura');
    }
}
?>