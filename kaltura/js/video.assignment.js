function do_on_wait() {

    var url = main.params['wwwroot'] + '/blocks/kaltura/keditor.php?entry_id=' + document.getElementById("id_widget").value;

    document.getElementById("btn_remix").onclick = new Function("kalturaInitModalBox(" + url + ", {width:890, height:546})");
    document.getElementById("btn_remix").style.display = "inline";   
    document.getElementById("btn_addvid").value = main.params['replacesubmission'];

}