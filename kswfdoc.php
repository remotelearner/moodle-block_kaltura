<?php
require_once('../../config.php');

require_login();

require_once('lib.php');

require_js($CFG->wwwroot . '/blocks/kaltura/js/jquery.js');
require_js($CFG->wwwroot . '/blocks/kaltura/js/kvideo.js');
require_js($CFG->wwwroot . '/blocks/kaltura/js/swfobject.js');

require_js($CFG->wwwroot . '/blocks/kaltura/js/flashversion.js');
require_js($CFG->wwwroot . '/blocks/kaltura/js/kdp_flash_ver_tester.js');

// Hide Kampyle feedback button
$CFG->kampyle_hide_button = true;

$meta = '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/blocks/kaltura/styles.php" />'."\n";

print_header('Kaltura Preview','','','',$meta);

$id         = optional_param('entry_id', '', PARAM_TEXT);
$context    = optional_param('context', 0, PARAM_INT);


if (empty($id)) {
    die('missing id');
}

$closeBut   = get_string('close','block_kaltura');

echo embed_kswfdoc($id, 780, 320, $context);

echo '<div style="margin-top:7px; width:780px; text-align:center;"><input type="button"  value="' . $closeBut . '" onclick="window.parent.kalturaCloseModalBox();" /><div>';

?>