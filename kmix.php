<?php
// //Debug
//$myFile = "/tmp/kmix.txt";
//$fh = fopen($myFile, 'w');
//fwrite($fh, " -- TESTING -- ");
//$stringData = 'okay';
//fwrite($fh, $stringData);
//fwrite($fh, ' ---- ');
//fclose($fh);

require_once("../../config.php");

require_login();

require_once('lib.php');


try {

    $kClient = new KalturaClient(KalturaHelpers::getServiceConfiguration());
    $kalturaUser = KalturaHelpers::getPlatformKey('user','');
    $kalturaSecret = KalturaHelpers::getPlatformKey('kaltura_secret','');

    $ksId = $kClient->session->start($kalturaSecret, $kalturaUser, KalturaSessionType::USER);
    $kClient->setKs($ksId);

    $mix                = new KalturaMixEntry();
    $mix->name          = optional_param('name', 'Editable video', PARAM_TEXT);
    $mix->editorType    = KalturaEditorType::ADVANCED;
    $mix                = $kClient->mixing->add($mix);

    $entries = optional_param('entries', '', PARAM_NOTAGS); // Might be able to use PARAM_SEQUENCE here
    $arrEntries = explode(',', $entries);

    foreach($arrEntries as $index => $entryId) {

        if (!empty($entryId)) {
            $kClient->mixing->appendMediaEntry($mix->id, $entryId);
        }
    }
    echo 'y:' . $mix->id;

} catch(Exception $exp) {

    die('n:' . $exp->getMessage());
}

?>