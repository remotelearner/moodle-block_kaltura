<?php
require_once('../../config.php');

require_login();

require_once('lib.php');
require_js($CFG->wwwroot.'/blocks/kaltura/js/jquery.js');
require_js($CFG->wwwroot.'/blocks/kaltura/js/kvideo.js');
require_js($CFG->wwwroot.'/blocks/kaltura/js/swfobject.js');

require_js($CFG->wwwroot . '/blocks/kaltura/js/flashversion.js');
require_js($CFG->wwwroot . '/blocks/kaltura/js/kdp_flash_ver_tester.js');

// Hide Kampyle feedback button
$CFG->kampyle_hide_button = true;

$meta = '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/blocks/kaltura/styles.php" />'."\n";

print_header('Kaltura Preview', '', '', '', $meta);

$id = '';

$id         = required_param('entry_id', PARAM_TEXT);
$design     = optional_param('design', 'light', PARAM_TEXT);
$width      = optional_param('width', 400, PARAM_INT);
$dimensions = optional_param('dimentions', 'x', PARAM_TEXT);
$type       = optional_param('type', '1', PARAM_INT);

if (0 == strcmp($dimensions, 'x')) {
    $dimensions = KalturaPlayerSize::LARGE;
}

$entry = new kaltura_entry;
$entry->dimensions = $dimensions;
$entry->custom_width = $width;
$entry->size = KalturaPlayerSize::CUSTOM;

$preview_video_type = (KalturaEntryType::MEDIA_CLIP == $type) ? KalturaEntryType::MEDIA_CLIP : KalturaEntryType::MIX;

echo embed_kaltura($id, get_width($entry), get_height($entry), $preview_video_type, $design);

echo '<div id="kaltura-preview-close">
     <input type="button"  value="' .
     get_string('close','block_kaltura') . '" onclick="window.parent.kalturaCloseModalBox();" />
     </div>';

?>