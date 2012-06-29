<?php
class KalturaLogger implements IKalturaLogger {
    public function __construct() {
    }

    public function log($str)  {
        global $CFG;

        /* added option to disable log from moodle's config */
        if(isset($CFG->disableKalturaLog) && $CFG->disableKalturaLog == true) {
            return;
        }

        //$myFile = "/tmp/logger.txt";
        $myFile = $CFG->dataroot.DIRECTORY_SEPARATOR.'kaltura.log';
        $fh = fopen($myFile, 'a');
        $stringData = $str . " \n";
        fwrite($fh, $stringData);

    }
}
?>