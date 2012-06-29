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

print_header('Kaltura Uploader', '', '', '', $meta);

$uploadtype     = optional_param('upload_type', 'video', PARAM_TEXT);
$mod            = optional_param('mod', 'video_resource', PARAM_TEXT);
$id             = optional_param('id', '', PARAM_INT);

if (!empty($id)) {

    $entry = get_record('block_kaltura_entries', 'id', $id);
} else {

    // Get the most recent entry in the block_kaltura_entries
    $sql = "SELECT * FROM {$CFG->prefix}block_kaltura_entries ORDER BY id DESC";
    $entry = get_record_sql($sql, true);

    if (!empty($entry)) {
        // Do nothing
    } else {
        $entry = new kaltura_entry;
    }

}

$type = (0 == strcmp($uploadtype, 'video') ) ? KalturaEntryType::MEDIA_CLIP : KalturaEntryType::MIX;

echo get_cw_wizard('divKalturaCw', 782, 449, $type);

if (0 == strcmp($mod, 'video_resource')) {

    $properties = array('divcw'         => 'divKalturaCw',
                        'updatefield'   => 'id_alltext',
                        'divprops'      => 'kaltura-divClipProps',
                        'mod'           => 'video_resource',
                        );

    echo get_cw_js_functions($type, $properties);
    //echo resource_iframe_resize();

} elseif (0 == strcmp($mod, 'ppt_resource')) {

    $properties = array('divcw'         => 'divKalturaCw',
                        'updatefield'   => 'id_video_input',
                        'mod'           => 'ppt_resource'
                        );

    echo get_cw_js_functions($type, $properties);

} elseif (0 == strcmp($mod, 'assignment')) {

    $properties = array('divcw'         => 'divKalturaCw',
                        'updatefield'   => 'id_widget',
                        'mod'           => 'assignment'
                        );

    echo get_cw_js_functions($type, $properties);
}

if (0 == strcmp($mod, 'video_resource')) {

    echo get_cw_properties_pane($entry, $type);
    echo get_cw_props_player('divClip', $type, 400, 332);

}

?>