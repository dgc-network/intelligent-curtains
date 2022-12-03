jQuery(document).ready(function($) {

    $("#select-product-id").change(function() {
        var val = $(this).val();
        $("#select-model-id").empty();
        $("#select-specification-id").empty();
        $("#select-model-id").append('<option value="0">-- Select an option --</option>');
        $("#select-specification-id").append('<option value="0">-- Select an option --</option>');

        jQuery.ajax({
            type: 'POST',
            //url: '/wp-admin/admin-ajax.php',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'select_product_id',
                'id': val,
            },
            success: function (response) {
                currenttime = response.currenttime;
                models = response.models;

                for (x in models) {
                    $("#select-model-id").append(model[x]);
                }
    
                //message = message.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;");
                $("#select-model-id").append('<option value="0">'+currenttime+'</option>');
            },
            error: function(error){
                alert(error);
            }
        });
      
        //alert(val);
        $("#select-model-id").append('<option value="0">-- Remove this --</option>');
        $("#select-specification-id").append('<option value="0">-- Remove this --</option>');

        //jQuery("#select-3-field").val(val);
    });
    
    $('[id^="cart-btn"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        $(this).css('color', 'cornflowerblue');
    });
        
    $('[id^="cart-btn"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', 'black');
    });
        
    $('[id^="cart-btn"]').on( "click", function() {
        window.location.assign("orders")
    });

    $('[id^="edit-btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
    });
        
    $('[id^="edit-btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
    });
        
    $('[id^="edit-btn-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(9);
        window.location.replace("?_edit=" + id);
    });

    $('[id^="del-btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
    });
        
    $('[id^="del-btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
    });
        
    $('[id^="del-btn-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(8);        
        if (window.confirm("Are you sure you want to delete this record?")) {
            window.location.replace("?_delete=" + id);
        }        
    });

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