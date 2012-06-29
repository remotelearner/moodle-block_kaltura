window.onload = initialize_page;

function initialize_page() {

    var entry = document.getElementById('id_alltext');

    if (0 == entry.value.length) {
    
        var submit = document.getElementById('id_submitbutton');
        submit.disabled = true;
        
        var submit2 = document.getElementById('id_submitbutton2');
        submit2.disabled = true;
    }
    
    
    
};