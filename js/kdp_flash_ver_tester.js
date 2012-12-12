kdpFlashTest.getFlashVersion = function() {
	// ie
	try {
		try {
			// avoid fp6 minor version lookup issues
			// see: http://blog.deconcept.com/2006/01/11/getvariable-setvariable-crash-internet-explorer-flash-6/
			var axo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash.6');
			try { axo.AllowScriptAccess = 'always'; }
			catch(e) { return '6,0,0'; }
		} catch(e) {}
		return new ActiveXObject('ShockwaveFlash.ShockwaveFlash').GetVariable('$version').replace(/\D+/g, ',').match(/^,?(.+),?$/)[1];
		// other browsers
	} catch(e) {
		try {
			if(navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin){
				return (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]).description.replace(/\D+/g, ",").match(/^,?(.+),?$/)[1];
			}
		} catch(e) {}
	}
	return '0,0,0';
}

kdpFlashTest.addWrongFlashVerNotice = function() {
	//window.console && console.log("in kdpFlashTest.addWrongFlashVerNotice");
	jQuery.noConflict();
	jQuery(function(){
		//window.console && console.log("in jQuery dom ready");
		jQuery("embed").each(function() {
			if(jQuery(this).attr("src").indexOf("/kwidget/") != -1) {
				kdpFlashTest.wrong_version_notice = kdpFlashTest.wrong_version_notice.replace("{{MIN_VER}}",kdpFlashTest.min_flash_ver)
				jQuery('<div class="kdpFlashNotice">' + kdpFlashTest.wrong_version_notice + '</div>').insertAfter(this);
			}
		});
	});
}

kdpFlashTest.required_flash_ver = kdpFlashTest.min_flash_ver.split(".",2);
if(kdpFlashTest.required_flash_ver.length == 1) kdpFlashTest.required_flash_ver.push("0");
kdpFlashTest.required_flash_ver = kdpFlashTest.required_flash_ver[0] + kdpFlashTest.required_flash_ver[1];
kdpFlashTest.has_flash_ver = kdpFlashTest.getFlashVersion().split(",",2);
kdpFlashTest.has_flash_ver = kdpFlashTest.has_flash_ver[0] + kdpFlashTest.has_flash_ver[1];
//window.console && console.log("required_flash_ver = ",kdpFlashTest.required_flash_ver);
//window.console && console.log("has_flash_ver = ",kdpFlashTest.has_flash_ver);
if(kdpFlashTest.has_flash_ver < kdpFlashTest.required_flash_ver) {
	//window.console && console.log("has_flash_ver is smaller then required_flash_ver");
	if(typeof jQuery === "undefined") {
		//window.console && console.log("jQuery not present");
		var script_tag = document.createElement('script');
		script_tag.setAttribute("type","text/javascript");
		script_tag.setAttribute("src","http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js");
		script_tag.onload = kdpFlashTest.addWrongFlashVerNotice; // Run this function once jQuery has loaded
		script_tag.onreadystatechange = function () { // Same thing but for IE
			if(document.body.style.opacity == undefined) { // not ie9+ (which is ok with script_tag.onload)
				if (this.readyState == 'complete' || this.readyState == 'loaded') {
					//window.console && console.log("IE");
					kdpFlashTest.addWrongFlashVerNotice();
				}
			}
		}
		document.getElementsByTagName("head")[0].appendChild(script_tag);
	} else {
		kdpFlashTest.addWrongFlashVerNotice();
	}
}
//window.console && console.log("done");