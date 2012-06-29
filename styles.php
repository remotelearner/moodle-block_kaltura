/*
This file is part of the Kaltura Collaborative Media Suite which allows users
to do with audio, video, and animation what Wiki platfroms allow them to do with
text.

Copyright (C) 2006-2008  Kaltura Inc.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

html,body {
    height:100%;
}

#kaltura-modalbox {
    position: fixed;
    left: 50%;
    top: 50%;
    margin:-180px 0 0 -340px;
    background: transparent;
    /*border:3px solid #666;*/
    width: 680px; z-index: 200;
}

#kaltura-overlay {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 199;
    width: 100%;
    height: 100%;
    background: url('<?php echo $CFG->wwwroot.'/blocks/kaltura/'; ?>images/trans-bg.png') 0 0 repeat;
    cursor: wait;
}


#kaltura-modalbox.white_bg {
    background:#ffffff;
}

/* Fixed posistioning emulation for IE6, currently no need because its being set via the JQM js to offset the wizard in the middle */
* html #kaltura-overlay {
    position: absolute;
    background:#000;
    filter: alpha(opacity=40);
    top: expression((document.documentElement.scrollTop || document.body.scrollTop) + Math.round(0 * (document.documentElement.offsetHeight || document.body.clientHeight) / 100) + 'px');
 }

* html #kaltura-modalbox {
    position: absolute;
    top: expression((document.documentElement.scrollTop || document.body.scrollTop) + Math.round((document.documentElement.offsetHeight || document.body.clientHeight) / 2) + 'px');
}

#kaltura-modalbox iframe {
    overflow:hidden;
}

#kaltura-modalbox iframe.remove_overflow {
    overflow:auto;
}

.poweredByKaltura {
    font-family: 'Lucida Grande',Verdana,Arial,Sans-Serif;
    font-size: 9px;
    height:12px;
    line-height:11px;
    overflow: hidden;
    text-align: right;
}

body#kaltura-kcw,
body#kaltura-kse,
body#kaltura-kdp {
    margin:0px;
    padding:0px;
}
body#kaltura-kdp #page #container,
body#kaltura-kdp #page #container #content,
body#kaltura-kcw #page #container,
body#kaltura-kcw #page #container #content,
body#kaltura-kse #page #container,
body#kaltura-kse #page #container #content,
body#kaltura-kcw #page #container {
    padding:0px;
    margin:0px;
    border:0px;
}

#blocks-kaltura-kcw div#kaltura-divClipProps {
    font-size: 13px;
}

#blocks-kaltura-keditor {
  margin-top: 0px;
  margin-bottom: 0px;
  margin-left: 0px;
  margin-right: 0px;

  padding-top: 0px;
  padding-bottom: 0px;
  padding-left: 0px;
  padding-right: 0px

}

#blocks-kaltura-kswfdoc div#kaltura-iframe-page {
  margin-top: 0px;
  margin-bottom: 0px;
  margin-left: 0px;
  margin-right: 0px;

  padding-top: 0px;
  padding-bottom: 0px;
  padding-left: 0px;
  padding-right: 0px

  border-top: 0px;
  border-bottom: 0px;
  border-left: 0px;
  border-right: 0px;

}

#blocks-kaltura-kcw #kaltura-iframe-page #kaltura-iframe-content,
#blocks-kaltura-kswfdoc #kaltura-iframe-page #kaltura-iframe-content,
#blocks-kaltura-preview #kaltura-iframe-page #kaltura-iframe-content,
#blocks-kaltura-keditor #kaltura-iframe-page #kaltura-iframe-content,
#blocks-kaltura-kmix #kaltura-iframe-page #kaltura-iframe-content, {
  margin-top: 0px;
  margin-bottom: 0px;
  margin-left: 0px;
  margin-right: 0px;

}

/* Must do this for popup windows. Otherwise the regular Moodle theme
 will be affected */
#blocks-kaltura-kcw #kaltura-iframe-page,
#blocks-kaltura-kswfdoc #kaltura-iframe-page,
#blocks-kaltura-preview #kaltura-iframe-page,
#blocks-kaltura-keditor #kaltura-iframe-page,
#blocks-kaltura-kmix #kaltura-iframe-page, {
  margin-top: 0px;
  margin-bottom: 0px;
  margin-left: 0px;
  margin-right: 0px;

/*  padding-top: 0px;
  padding-bottom: 0px;
  padding-left: 0px;
  padding-right: 0px
*/

  border-top-width: 0px;
  border-bottom-width: 0px;
  border-left-width: 0px;
  border-right-width: 0px;
}

#kaltura_modal_iframe {
  margin-top: 0px;
  margin-bottom: 0px;
  margin-left: 0px;
  margin-right: 0px;

  padding-top: 0px;
  padding-bottom: 0px;
  padding-left: 0px;
  padding-right: 0px

}

#blocks-kaltura-kpreview {
  margin-top: 0px;
  margin-bottom: 0px;
  margin-left: 0px;
  margin-right: 0px;

  padding-top: 0px;
  padding-bottom: 0px;
  padding-left: 0px;
  padding-right: 0px


}


#blocks-kaltura-kcw {
  margin-top: 0px;
  margin-bottom: 0px;
  margin-left: 0px;
  margin-right: 0px;

  padding-top: 0px;
  padding-bottom: 0px;
  padding-left: 0px;
  padding-right: 0px;
}

#kaltura-iframe-page {
  background-color:#FFFFFF;
}
#blocks-kaltura-kswfdoc {
  margin-top: 0px;
  margin-bottom: 0px;
  margin-left: 0px;
  margin-right: 0px;

  padding-top: 0px;
  padding-bottom: 0px;
  padding-left: 0px;
  padding-right: 0px

}


#kaltura_close_modal {
    height: 12px;
    font-size:10px;
}

.kaltura-obj {
    width: 150px;
    padding:10px;
    border: 1px solid #666666;
    margin: 5px;
    float: left;
    height:150px;
}

div.kaltura-obj.active {
    background-color:#cccccc;
}

.kaltura-obj div span {
    display:block;
}

#static_library_player_div {
    height: 364px;
    width: 410px;
    overflow: hidden;
}

.poweredByKaltura {
    display: none;
}

#kaltura-divClipProps #divUserSlected {
    margin-left: 416px;
    margin-top: -350px;
}

#kaltura-divClipProps #divDesign {
    width: 350px;
    margin-top: 40px;
}

#kaltura-divClipProps #divDim {
    margin: 40px 0px 0px 0px;
    width: 350px;
}

#kaltura-divClipProps #divSize{
    margin: 40px 0px 0px 0px;
    width: 350px;
}

#kaltura-divClipProps #divButtons {
    height: 100px;
    margin-top: 13px;
    width: 760px;
    text-align: center;
}

#kaltura-preview-close {
    text-align: center;
    padding-bottom: 20px;
}

/** CSS definitions not used by Kaltura - consider removing
a.current {
    color: red;
    font-weight: bold;
}
a.arrow_left,
a.arrow_right {
    display:block;
    float: left;
    width: 40px;
    margin-top: 15px;
    height: 150px;
    background:url('<?php echo $CFG->wwwroot.'/blocks/kaltura/'; ?>images/right_arrow.gif');
}

a.arrow_left {
    background:url('<?php echo $CFG->wwwroot.'/blocks/kaltura/'; ?>images/left_arrow.gif');
}
*/