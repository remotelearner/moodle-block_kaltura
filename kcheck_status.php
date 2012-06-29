<?php
require_once('../../config.php');

require_login();

require_once($CFG->dirroot.'/blocks/kaltura/lib.php');

$type = optional_param('type','video', PARAM_NOTAGS);

if (0 == strcmp($type, 'ppt')) {
    $downloadurl    = optional_param('downloadUrl', '', PARAM_URL);
    $docid          = optional_param('docid', 0, PARAM_NOTAGS);


    check_ppt_status($downloadurl, $docid);

} else {
    $entryid = optional_param('entryid', '', PARAM_NOTAGS);
    check_video_status($entryid);
}

function check_video_status($entryId) {
    try {
        $client = KalturaHelpers::getKalturaClient();

        $entry = $client->baseEntry->get($entryId);

        if ($entry->status == KalturaEntryStatus::READY) {
            echo 'y:<img src="'. KalturaHelpers::getThumbnailUrl(null, $entryId, 140, 105) .'" />';
        } else {
            echo 'n:';
        }
    } catch(Exception $exp) {
        die('e:' . $exp->getMessage());
    }
}

function check_ppt_status($url, $document_entry_id) {

////Debug
//$myFile = "/tmp/AKIN.TXT";
//$fh = fopen($myFile, 'w');
//$stringData = $output;
//fwrite($fh, $stringData);
//fwrite($fh, ' ---- ');
//fwrite($fh, $url.'?'.$random_hit);
//fwrite($fh, ' ---- ');
//fwrite($fh, $document_entry_id);
//fclose($fh);
//        echo $info['http_code'];
//    } else {
//
//        if (!$fp = fopen($url.'?'.$random_hit, 'r')) {
//            //echo '500';
//        } else {
//            //echo '200';
//        }
//
//    }

////Debug
//$myFile = "/tmp/kcheck_status.txt";
//$fh = fopen($myFile, 'w');
//fwrite($fh, " -- kcheck_status -- ");
//$stringData = var_export($document_entry_id, true);
//fwrite($fh, $stringData);
//fwrite($fh, ' ---- ');
//fclose($fh);


    $client = KalturaHelpers::getKalturaClient();

    $documentAssets = $client->flavorAsset->getByEntryId($document_entry_id);

    foreach($documentAssets as $asset) {

        if ($asset->fileExt != 'swf') {
            continue;
        }

        if ($asset->fileExt == 'swf' && $asset->status == KalturaFlavorAssetStatus::READY) {
            $params = array('entryId' => $document_entry_id,
                            'flavorAssetId' => $asset->id,
                            'forceProxy' => true);

            //$url = $client->getServeUrl('documents', 'serve', $params);
            $url = $client->getServeUrl('document_documents', 'serve', $params);
////Debug
//$myFile = "/tmp/kcheck_status2.txt";
//$fh = fopen($myFile, 'w');
//fwrite($fh, " -- $document_entry_id -- ");
//$stringData = var_export($url, true);
//fwrite($fh, $stringData);
//fwrite($fh, ' ---- ');
//fclose($fh);
            echo 'y:'.$url;
            die();
        }
    }

    echo 'n:';
    die();
}


?>