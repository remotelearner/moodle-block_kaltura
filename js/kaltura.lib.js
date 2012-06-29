function show_wait() {
    
    var url_pleasewait = main.params['wwwroot'] + "/blocks/kaltura/images/Pleasewait.swf";
    var url_checkstatus = main.params['wwwroot'] + "/blocks/kaltura/kcheck_status.php";

    var param1 = main.params['param1'];
    var param2 = main.params['param2'];
    
    var ksoa = new SWFObject(url_pleasewait, "kwait", "140", "105", "9", "#ffffff");

    var txt_document = "<br />" + main.params['videoconversion'] + ".<br /><br /> <a href=\"javascript:show_wait()\">" +
                       main.params['clickhere'] + "</a> " + main.params['convcheckdone'];

    var entryId = document.getElementById(param2).value;

    ksoa.addParam("allowScriptAccess", "always");
    ksoa.addParam("allowFullScreen", "TRUE");
    ksoa.addParam("allowNetworking", "all");
    ksoa.addParam("wmode", "transparent");

    document.getElementById(param1).style.display = "block";

    if (ksoa.installedVer.major >= 9) {
        
        ksoa.write(param1);
    } else {
        
        document.getElementById(param1).innerHTML = "Flash player version 9 and above is required. " +
                                                    "<a href=\"http://get.adobe.com/flashplayer/\">" +
                                                    "Upgrade your flash version</a>";
    }

        $.ajax({
            type: "POST",
            url: url_checkstatus,
            data: "entryid=" + entryId,
            success: function(msg) {
  
                if (msg.substr(0,2) == "y:") {
      
                    document.getElementById(param1).innerHTML = msg.substr(2);
                    do_on_wait();
      
                }
                else {
                    document.getElementById(param1).innerHTML = txt_document;
                }
  
            },
            error: function(msg) {
                // do nothing
            }
        });
}


function set_entry_type(type) {

    document.getElementById("id_entry_type").value = type;

}

function get_height() {

    var aspectratio = main.params['aspecttype'];
    var sizelarge   = main.params['sizelarge'];
    var sizesmall   = main.params['sizesmall'];
    var sizecustom  = main.params['sizecustom'];
    
    if (get_field("id_dimensions") == aspectratio) {

        switch(get_field("id_size")) {
            case sizelarge:
              return 445;
              break;
            case sizesmall:
              return 340;
              break;
            case sizecustom:
              return parseInt(get_field("id_custom_width"))*3/4 + 65 + 80;
              break;
            default:
              return 445;
             break;
  
        }

    } else {
        switch(get_field("id_size")) {
  
            case sizelarge:
              return 370;
             break;
            case sizesmall:
              return 291;
              break;
            case sizecustom:
              return parseInt(get_field("id_custom_width"))*9/16 + 65 + 80;
              break;
            default:
              return 370;
              break;
        }

    }
}

function get_width() {

    var sizelarge   = main.params['sizelarge'];
    var sizesmall   = main.params['sizesmall'];
    var sizecustom  = main.params['sizecustom'];
  
    switch(get_field("id_size")) {
  
        case sizelarge:
          return 450;
          break;
        case sizesmall:
          return 310;
          break;
        case sizecustom:
          return parseInt(get_field("id_custom_width")) + 50;
          break;
        default:
          return 450;
          break;
  }
}


function do_on_wait() {

    var mediaclip   = main.params['mediaclip'];
    var preview_url = main.params['preview_url'];
    var edit_url    = main.params['edit_url'];
    var cw_url      = main.params['cw_url'];
    
    
    var entryId = document.getElementById("id_alltext").value;

    document.getElementById("id_addvideo").style.display  = "none";
    document.getElementById("id_addeditablevideo").style.display  = "none";
    document.getElementById("id_replace").style.display = "inline";

    if (document.getElementById("spanExplain") != null) {

        document.getElementById("spanExplain").style.display = "none";
    }

    if (document.getElementById("id_entry_type").value == mediaclip) {

        var design      = get_field("id_design");
        var width       = get_width();
        var dimensions  = get_field("id_dimensions");

        document.getElementById("id_preview").style.display = "inline";

        document.getElementById("id_preview").onclick = new Function("kalturaInitModalBox(\'" + preview_url +
                                "entry_id=" + entryId + "&design=" + design + "&width=" + width + 
                                "&dimensions=" + dimensions + "\', {width:get_width()+15, height:get_height()+30})"); //width:get_width()+10

    } else {

        document.getElementById("id_preview_edit").style.display = "inline";
  
        document.getElementById("id_preview_edit").onclick = 
                                new Function("kalturaInitModalBox(" + edit_url + "entry_id=" + 
                                entryId + ", {width:890, height:546})");
  
        document.getElementById("id_replace").onclick = 
                                          new Function("kalturaInitModalBox(\'" + cw_url + 
                                          "&upload_type=mix + \', {width:760, height:442})");
    }
}


function onSimpleEditorBackClick(param) {

    var thumburl = main.params['thumburl'];
    
    ts = new Date().getTime();

    try {
        update_img('id_thumb', thumburl + '?t=' + ts, false, '' );
    } catch (err){
        // not doing anything
    }
    
    setTimeout("window.parent.kalturaCloseModalBox();",0);
}


function change_entry_player() {
    var local_entry_id = get_field("id_alltext");
  
    var design = document.getElementById("slctDesign");

    show_entry_player(local_entry_id, design.options[design.selectedIndex].value);
}

function get_video_resource_name() {

    var mod   = main.params['mod'];
    var videoname = 'Video Submission';
    
    if ('assignment' != mod) {

    	var name = get_field("id_name");
        var timestamp = Number(new Date());

        
        if (name) {
            videoname = name;
        } else {
      	    videoname = 'No Name - ' + timestamp;
        }
        
    }
    
    return videoname;
}


function onContributionWizardAfterAddEntry(param) {

    var local_entry_id  = '';
    var wwwroot         = main.params['wwwroot'];
    var type            = main.params['type'];
    var entrymixtype    = main.params['entrymixtype'];
    var divprops        = main.params['divprops'];
    var divcw           = main.params['divcw'];
    var updatefield     = main.params['updatefield'];
    var entrymediatype  = main.params['entrymediatype'];
    
    // This is needed because all plug-ins use this function however only assignment types do now have divprops defined
    var hasprops = typeof divprops != 'undefined' && divprops !=  '';
//alert('uniqueID :' + param[0].uniqueID);
//alert('mediaType :' + param[0].mediaType);
//alert('sourceLink :' + param[0].sourceLink);
//alert('thumbURL :' + param[0].thumbURL);
//
//alert('type ' + type); //Show all properties and its value
//alert('entrymixtype ' + entrymixtype);
//alert('entrymediatype ' + entrymediatype);

//    var str = '';
//    for(prop in param[0]) {
//        
//        str+=prop + " value :"+ param[prop]+"\n";//Concate prop and its value from object
//        
//        if (typeof param[prop] == "object") {
//            for(prop2 in param[prop]) {
//                str += prop2 + " value :"+ param[prop2]+"\n";//Concate prop and its value from object
//            }
//        }
//    }
    
    if (type == entrymixtype) {

        var entries = "";
        var videoname = get_video_resource_name();
        
        if (hasprops) {

            document.getElementById(divcw).style.display = "none";
            document.getElementById(divprops).style.display = "block";
        }

        for (i=0; i < param.length; i++) {

            entryId = (param[i].uniqueID == null ? param[i].entryId : param[i].uniqueID);
            entries += entryId + ",";
        }

        $.ajax({
            type: "POST",
            url: wwwroot + '/blocks/kaltura/kmix.php',
            data: "entries="+entries+ "&name=" + videoname,
            success: function(msg) {
        
                if (msg.substr(0,2) == "y:") {
    
                    entryId = msg.substr(2);
                    local_entry_id = entryId;
        
                    if (hasprops) {
        
                        show_entry_player(entryId, "light");
                        update_field(updatefield, entryId, false, '');
                    } else {
        
                        setTimeout("window.parent.kalturaCloseModalBox();", 0);
                        update_field(updatefield, entryId, false, 'show_wait');
                    }
                      
                } else {
                    alert(msg.substr(2));
                }
            },
            error: function(msg) {
            }
        });
    } else if (type == entrymediatype) {
        entryId = (param[0].uniqueID == null ? param[0].entryId : param[0].uniqueID);


        if (hasprops) {

            document.getElementById(divcw).style.display = "none";
            document.getElementById(divprops).style.display = "block";
            

            local_entry_id = entryId;

            show_entry_player(entryId, "light");
            update_field(updatefield, entryId, false, '');

        } else {
            setTimeout("window.parent.kalturaCloseModalBox();", 0);
            update_field(updatefield, entryId, false, 'show_wait');
        }
    }
    
}


function onContributionWizardClose(modified) {

    if (modified[0] == 0) {

      setTimeout("window.parent.kalturaCloseModalBox();",0);

    }
}

function gotoEditorWindow(param1) {
    onPlayerEditClick(param1);
}

function onPlayerEditClick(param1) {
    var wwwroot = main.params['wwwroot'];
    kalturaInitModalBox(wwwroot + "/blocks/kaltura/keditor.php?entry_id=" + param1, {width:890, height:546});
}



//------------ Javascript functions mostly used by kalturaswfdoc
function check_ready(theType) {
    //var ppt_input   = main.params['ppt_input'];
    var pptIdHolder = document.getElementById('id_ppt_input');
    
    //var thumb_doc_holder = main.params['thumb_doc_holder'];
    var pptThumbHolder   = document.getElementById('thumb_doc_holder');

	//var ppt_dnld_url     = main.params['ppt_dnld_url'];
	var pptDnldUrlHolder = document.getElementById('id_ppt_dnld_url');
	
	// Need another place holder for another download URL
	var pptDnldUrlHolder2 = document.getElementById('id_ppt_dnld_url2');
	
	var has_ppt = document.getElementById('id_has_ppt');
	var has_video = document.getElementById('id_has_video');

	var id_debug =  document.getElementById('id_debug');
	
	var txt_document = main.params['txt_document'];
	
	var pleasewait      = main.params['wwwroot'] + "/blocks/kaltura/images/Pleasewait.swf";
	var url_checkstatus = main.params['wwwroot'] + "/blocks/kaltura/kcheck_status.php";
	var docthumbnail    = main.params['wwwroot'] + "/blocks/kaltura/images/V_ico.png";
	
	if (theType == "ppt") {
          theId     = pptIdHolder.value;
          theThumb  = pptThumbHolder;
          theUrl    = encodeURI(pptDnldUrlHolder.value);
    }
    
    var ksoa = new SWFObject(pleasewait, "kwait", "140", "105", "9", "#ffffff");
    
    ksoa.addParam("allowScriptAccess", "always");
    ksoa.addParam("allowFullScreen", "TRUE");
    ksoa.addParam("allowNetworking", "all");
    ksoa.addParam("wmode","transparent");
    
    if(ksoa.installedVer.major >= 9) {
        ksoa.write("thumb_doc_holder");
    }

    $.ajax({
      type: "POST",
      url: url_checkstatus,
      data: "type=ppt&downloadUrl="+theUrl+"&docid="+theId,
      success: function(msg) {
    
//          if (msg == "200") {
    	  	if (msg.substring(0,2) == "y:") {
    	  		id_debug.value = msg.substring(2)
    	  		pptDnldUrlHolder2.value = msg.substring(2);
              if (theType != "ppt") {
                  //theThumb.innerHTML = "<img src=\"'.$kaltura_cdn_url.'/p/'.$partner_id.'/sp/'.$sub_partner_id.'/thumbnail/entry_id/"+theId+"/width/140/height/105/type/3/bgcolor/ffffff\">";
                  has_video.value = 1;
    
                  if (parseInt(has_ppt.value)) {
                      document.getElementById("id_sync_btn").disabled = false;
                  }
              } else {
                  theThumb.innerHTML = "<img src=\"" + docthumbnail + "\" style=\"margin:12px;\">";
                  has_ppt.value = 1;
    
                  if (parseInt(has_video.value)) {
                      document.getElementById("id_sync_btn").disabled = false;
                  }
              }
    
          } else {
              //document.getElementById("thumb_doc_holder").innerHTML = txt_document;
        	  pptThumbHolder.innerHTML = txt_document;
          }
      }
    });
 
} // end of - check_ready()


function set_has_swfdoc(val) {
	var has_swfdoc = document.getElementById('id_has_swfdoc');
	has_swfdoc.value = val;
	//has_swfdoc = val;
} // end of - set_has_swfdoc()


function create_swfdoc() {

	var has_swfdoc = document.getElementById('id_has_swfdoc');
	var wwwroot = main.params['wwwroot'];
    var entry_id = document.getElementById("id_alltext").value;
    var courseid = main.params['courseid'];
    
	if (parseInt(has_swfdoc.value)) {

		url = wwwroot + "/blocks/kaltura/kswfdoc.php?entry_id=" + entry_id + "&context=" + courseid;
	    kalturaInitModalBox(url, {width:780, height:400});
	} else {
		
		var ppt_input = document.getElementById("id_ppt_input");
		var video_input = document.getElementById("id_video_input");
		var idname = document.getElementById("id_name");
		var pptDnldUrlHolder = document.getElementById('id_ppt_dnld_url');
		var pptDnldUrlHolder2 = document.getElementById('id_ppt_dnld_url2');

	    $.ajax({
		    type: "POST",
		    url: wwwroot + "/blocks/kaltura/kcreate.php",
		    data: "kaction=swfdoc&ppt=" + ppt_input.value + 
		    	  "&video=" + video_input.value + 
		    	  "&name=" + idname.value + 
		    	  "&downloadUrl="+pptDnldUrlHolder.value+
		    	  "&downloadURL2="+pptDnldUrlHolder2.value,
		    success: function(entry_id){
			    if (null != entry_id &&
			        entry_id.length > 0) {

			    	set_has_swfdoc(true);

			    	document.getElementById("id_alltext").value = entry_id;
			        url = wwwroot + "/blocks/kaltura/kswfdoc.php?entry_id=" +
			        	  entry_id + "&context=" + courseid;
			        kalturaInitModalBox(url, {width:780, height:400});
			    }
	        }
	    });
	}
}  // end of - create_swfdoc()

// Gets called when the 'Sync Keypoints' button is pressed
function save_sync() {

    create_swfdoc();
    document.getElementById("id_btn_uploaddoc").disabled = true;
    document.getElementById("id_btn_selectvideo").disabled = true;
    document.getElementById("divKalturaKupload").innerHTML = "";
}

// "uploader" gets created by SWFObject() when it calls
function user_selected() {
    document.getElementById("uploader").upload();
}

function uploaded() {
    document.getElementById("uploader").addEntries();
}

function uploading() {
	var wwwroot = main.params['wwwroot'];
	var has_ppt = document.getElementById('id_has_ppt');
	has_ppt.value = 0;
	//has_ppt = 0;

	var ksoa = new SWFObject(wwwroot + "/blocks/kaltura/images/Pleasewait.swf", "kwait", "140", "105", "9", "#ffffff");
	ksoa.addParam("allowScriptAccess", "always");
	ksoa.addParam("allowFullScreen", "TRUE");
	ksoa.addParam("allowNetworking", "all");
	ksoa.addParam("wmode","transparent");

	if(ksoa.installedVer.major >= 9) {
  		ksoa.write("thumb_doc_holder");
	}

} // end of - uploading

function entries_added(obj) {

	var txt_document = main.params['txt_document'];
	var ppt_input = document.getElementById("id_ppt_input");
	var ppt_download_url = document.getElementById('id_ppt_dnld_url');
	var wwwroot = main.params['wwwroot'];
	
	document.getElementById("thumb_doc_holder").innerHTML = txt_document;
    myobj = obj[0];
    document.getElementById("id_ppt_input").value = myobj.entryId;

    $.ajax({
    	type: "POST",
        url: wwwroot + "/blocks/kaltura/kcreate.php",
        data: "kaction=ppt&ppt=" + ppt_input.value,
        //data: "kaction=swfdoc&ppt=" + ppt_input.value,
        success: function(url) {

        	if( url.substring(0,2) == "y:") {

        		ppt_download_url.value = url.substring(2);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
        	alert(xhr.statusText);
        }
	});

    document.getElementById("uploader").removeFiles(0,0);

} //end of - entries_added()

function do_on_wait() {
	var has_video = document.getElementById('id_has_video');
	
    if (has_video != null) {
        has_video.value = 1;
    }

	var has_ppt = document.getElementById('id_has_ppt');

    if (has_ppt != null) {
        if (parseInt(has_ppt.value) && parseInt(has_video.value)) {
            document.getElementById("id_sync_btn").disabled = false;
        }
    }
}

/*
function wizzard_entries_added() {

	var savebtn = document.getElementById('newsave');

    if (savebtn != null) {
        savebtn.disabled = false;
    }

}

var cwwizzarddel = {
		progressHandler: wizzard_entries_added
}; // Delegate variable for get_cw_wizard in blocks/kaltura/lib.php
*/

//This line must be before add_swf_uploader() because it contains all the handler information
var delegate = { selectHandler: user_selected,
				 progressHandler: uploading,
				 allUploadsCompleteHandler: uploaded,
				 entriesAddedHandler: entries_added
			    };

/**
 * This function overwrites the inner HTML of the upload button, so that the upload
 * file dialog box can execute flash code to upload the document to Kaltura
 * 
 * Make sure this function is the last function in the file.  Due to the way 
 * video resource document uploading is handled
 */
function add_swf_uploader() {
	var serviceurl = main.params['kserviceurl'];
	var ksession = main.params['ksession'];
	var userid = main.params['userid'];
	var kpartnerid = main.params['kpartnerid'];
	var ksubpartnerid = main.params['ksubpartnerid'];

	var kso = new SWFObject(serviceurl + "/kupload/ui_conf_id/1002613",
							"uploader", "110", "25", "9", "#ffffff");
//	kso.addParam("flashVars", "ks=" + ksession + "&uid=" + userid +
//				 "&partnerId=" + kpartnerid + "&subPId=" + ksubpartnerid + 
//				 "&entryId=-2&conversionProfile=5&maxUploads=10&maxFileSize=128" +
//				 "&maxTotalSize=200&uiConfId=1002613&jsDelegate=delegate");
	kso.addParam("flashVars", "ks=" + ksession + "&uid=" + userid +
	 "&partnerId=" + kpartnerid + "&subPId=" + ksubpartnerid + 
	 "&entryId=-1&conversionProfile=5&maxUploads=10&maxFileSize=128" +
	 "&maxTotalSize=200&uiConfId=1002613&jsDelegate=delegate");
	kso.addParam("allowScriptAccess", "always");
	kso.addParam("allowFullScreen", "TRUE");
	kso.addParam("allowNetworking", "all");
	kso.addParam("wmode","transparent");

	if(kso.installedVer.major >= 9) {
		kso.write("divKalturaKupload");
	} else {
		document.getElementById("divKalturaKupload").innerHTML = "Flash player version 9 and above is required. <a href=\"http://get.adobe.com/flashplayer/\">Upgrade your flash version</a>";
	}

}

/**
 * This function is used by the video resource plug-in and changes the CSS properties
 * for one of the two video resource plug-in buttons
 * 
 */
function disable_add_vid_button() {

    var entry_type = get_field("id_entry_type");
    var field = null;
    
    if (1 == entry_type) {

        // Hide the add editable button
        field = get_field_obj('id_addeditablevideo');
        field.style.display = 'none';
        
        // Hide the edtiable video column of text
        field = get_field_obj('edit_col');
        if (field !== null) {
            field.style.display = 'none';
        }
        
    } else {

        // Hide add non-editable button
        field = get_field_obj('id_addvideo');
        field.style.display = 'none';
        
        // Hide the non-editable column of text
        field = get_field_obj('non_edit_col');
        if (field !== null) {
            field.style.display = 'none';
        }

        // Remove the left margin from the add editable video
        field = get_field_obj('id_addeditablevideo');
        field.style.marginLeft = '0px';
        
        // Remove left padding from editable column of text
        field = get_field_obj('edit_col');
        if (field !== null) {
            field.style.paddingLeft = '0px';
        }


    }
}
