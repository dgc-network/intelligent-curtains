jQuery(document).ready(function($) {

    $('#qrcode').qrcode({
        text: $("#qrcode_content").text()
    });

/* jQuery UI Dialog - Basic dialog */

    $( "#dialog" ).dialog();
    //$( "#dialog-form" ).dialog();
    $( "#dialog-form" ).dialog({
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


/* jQuery UI Dialog - Modal form */

    // From http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#e-mail-state-%28type=email%29

});