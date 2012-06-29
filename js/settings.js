YAHOO.util.Event.onDOMReady(init_configuration_settings);

function init_configuration_settings(e) {
    
    // PLAYER EDITOR
    var dropdown = YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_editor');

    // Check if the drop down is set to custom player
    if ((dropdown.options.length - 1) == dropdown.options.selectedIndex) {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_editor_cust').disabled = false;
    } else {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_editor_cust').disabled = true;
    }

    // REGULAR PLAYER DARK
    dropdown = YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_regular_dark');

    // Check if the drop down is set to custom player
    if ((dropdown.options.length - 1) == dropdown.options.selectedIndex) {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_regular_dark_cust').disabled = false;
    } else {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_regular_dark_cust').disabled = true;
    }

    // REGULAR PLAYER LIGHT
    dropdown = YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_regular_light');

    // Check if the drop down is set to custom player
    if ((dropdown.options.length - 1) == dropdown.options.selectedIndex) {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_regular_light_cust').disabled = false;
    } else {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_regular_light_cust').disabled = true;
    }

    // PLAYER MIX DARK
    dropdown = YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_mix_dark');

    // Check if the drop down is set to custom player
    if ((dropdown.options.length - 1) == dropdown.options.selectedIndex) {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_mix_dark_cust').disabled = false;
    } else {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_mix_dark_cust').disabled = true;
    }

    // PLAYER MIX LIGHT
    dropdown = YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_mix_light');

    // Check if the drop down is set to custom player
    if ((dropdown.options.length - 1) == dropdown.options.selectedIndex) {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_mix_light_cust').disabled = false;
    } else {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_mix_light_cust').disabled = true;
    }

    // PLAYER PRESENTATION
    dropdown = YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_video_presentation');

    // Check if the drop down is set to custom player
    if ((dropdown.options.length - 1) == dropdown.options.selectedIndex) {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_video_presentation_cust').disabled = false;
    } else {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_player_video_presentation_cust').disabled = true;
    }

    // PLAYER UPLOADER
    dropdown = YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_uploader_regular');

    // Check if the drop down is set to custom player
    if ((dropdown.options.length - 1) == dropdown.options.selectedIndex) {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_uploader_regular_cust').disabled = false;
    } else {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_uploader_regular_cust').disabled = true;
    }
    
    //PLAYER MIX UPLOADER
    dropdown = YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_uploader_mix');

    // Check if the drop down is set to custom player
    if ((dropdown.options.length - 1) == dropdown.options.selectedIndex) {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_uploader_mix_cust').disabled = false;
    } else {
        YAHOO.util.Dom.get('id_s_block_kaltura_kaltura_uploader_mix_cust').disabled = true;
    }

    // Add event listener
    var dropdowns = ["id_s_block_kaltura_kaltura_player_editor",
                     "id_s_block_kaltura_kaltura_player_regular_dark",
                     "id_s_block_kaltura_kaltura_player_regular_light",
                     "id_s_block_kaltura_kaltura_player_mix_dark",
                     "id_s_block_kaltura_kaltura_player_mix_light",
                     "id_s_block_kaltura_kaltura_player_video_presentation",
                     "id_s_block_kaltura_kaltura_uploader_regular",
                     "id_s_block_kaltura_kaltura_uploader_mix"
                     ];

    YAHOO.util.Event.addListener(dropdowns, 'change', switch_visibility);
}

/**
 * When play dropdown changes to 'custom player' enable the custom player textfield
 */
function switch_visibility(e) {
    
    dropdown = YAHOO.util.Dom.get(this.id);
    textbox = YAHOO.util.Dom.get(this.id + "_cust");
    
    if ((dropdown.options.length - 1) == dropdown.options.selectedIndex) {
        textbox.disabled = false;
    } else {
        if (!textbox.disabled) {
            textbox.disabled = true;
        }
    }
}
