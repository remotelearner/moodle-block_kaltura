<?php
require_once('../../config.php');

require_login();

require_once('lib.php');
require_js($CFG->wwwroot.'/blocks/kaltura/js/jquery.js');
require_js($CFG->wwwroot.'/blocks/kaltura/js/kvideo.js');
require_js($CFG->wwwroot.'/blocks/kaltura/js/swfobject.js');
require_js($CFG->wwwroot.'/blocks/kaltura/js/kaltura.main.js');
require_js($CFG->wwwroot.'/blocks/kaltura/js/kaltura.lib.js');

// Hide Kampyle feedback button
$CFG->kampyle_hide_button = true;

$meta = '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/blocks/kaltura/styles.php" />'."\n";

print_header('Kaltura Editor','','','',$meta);

$id     = optional_param('entry_id', '', PARAM_TEXT);
$touch = optional_param('touch', 0, PARAM_INT);

if (empty($id)) {
    die('missing id');
}

// Touch file
if ($touch) {
    $entry = get_record('block_kaltura_entries', 'entry_id', $entry);
    $entry->timemodified = time();
    update_record('block_kaltura_entries', $entry);
}

echo get_se_js_functions(KalturaHelpers::getThumbnailUrl(null, $id, 140, 105));

echo get_se_wizard('divKalturaSe', 890, 546, $id);


?>