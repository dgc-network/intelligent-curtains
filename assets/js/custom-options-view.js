jQuery(document).ready(function($) {

    // Row/Record Edit Click
    jQuery('[id^="edit-btn-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(9);
        window.location.replace("?action=edit&id=" + id);
    });

    jQuery('[id^="del-btn-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(8);
        
        if (window.confirm("Are you sure you want to delete this record?")) {
            window.location.replace("?_delete=" + id);
        //} else {
        //    window.location.replace("");
        }
        
    });

    jQuery('["#del-btn"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(8);
        
        if (window.confirm("Are you sure you want to delete this record?")) {
            window.location.replace("?_delete=" + id);
        //} else {
        //    window.location.replace("");
        }
        
    });
/*
    $(document).ready(function() {
        needToConfirm = false; 
        window.onbeforeunload = askConfirm;
    });
     
    function askConfirm() {
        if (needToConfirm) {
            // Put your custom message here 
            return "Your data will be changed."; 
        }
    }
     
    $("#dialog,#commentform,#wpforms-form-170").change(function() {
        needToConfirm = true;
    });
*/    
    $('#qrcode').qrcode({
        text: $("#qrcode_content").text()
    });

    $('#qrcode1').qrcode({
        text: $("#qrcode_content").text()
    });

    $('#qrcode2').qrcode({
        text: $("#qrcode_content").text()
    });

    /* jQuery UI Dialog - Basic dialog */

    $( "#dialog" ).dialog();

    /* jQuery UI Dialog - Modal form */
/*
    $( "#dialog-form-1" ).dialog({
        //autoOpen: false,
        autoOpen: true,
        height: 400,
        width: 350,
        //modal: true,
        buttons: {
            "Create": addUser,
            Cancel: function() {
                dialog.dialog( "close" );
            }
        },
        close: function() {
            form[ 0 ].reset();
            allFields.removeClass( "ui-state-error" );
        }
    });
*/    
    // From http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#e-mail-state-%28type=email%29

});