jQuery(document).ready(function($) {

    $('.chatboxtextarea').on('keypress',function(e) {
        chatboxtitle = $('.chatboxtitle').val();
        if(e.keyCode == 13 && e.shiftKey == 0)  {
            message = $(this).val();
            message = message.replace(/^\s+|\s+$/g,"");
            $(this).val('');
            //$(this).empty();
            $(this).focus();
            $(this).css('height','28px');
            if (message != '') {
                jQuery.ajax({
                    type: 'POST',
                    //url: '/wp-admin/admin-ajax.php',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'send_chat',
                        'to': chatboxtitle,
                        'message': message,
                    },
                    success: function (response) {
                        currenttime = response.currenttime;
                        message = message.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;");
                        $(".chatboxcontent").append('<div class="chatboxmessage" style="float: right;"><div class="chatboxmessagetime">'+currenttime+'</div><div class="chatboxinfo">'+message+'</div></div><div style="clear: right;"></div>');
                        $(".chatboxcontent").scrollTop($(".chatboxcontent")[0].scrollHeight);
                    },
                    error: function(error){
                        alert(error);
                    }
                });
            }
            chatHeartbeatTime = minChatHeartbeat;
            chatHeartbeatCount = 1;
    
            return false;
        }
/*
        var adjustedHeight = chatboxtextarea.clientHeight;
        var maxHeight = 94;
    
        if (maxHeight > adjustedHeight) {
            adjustedHeight = Math.max(chatboxtextarea.scrollHeight, adjustedHeight);
            if (maxHeight)
                adjustedHeight = Math.min(maxHeight, adjustedHeight);
            if (adjustedHeight > chatboxtextarea.clientHeight)
                $(chatboxtextarea).css('height',adjustedHeight+8 +'px');
        } else {
            $(chatboxtextarea).css('overflow','auto');
        }     
*/            
    });
/*
    $("#select-order-status-backup").change(function() {
        var status = $(this).val();
        var number = $("#select-order-number").val();

        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'select_order_status',
                'number': number,
                'status': status,
            },
            success: function (response) {
            },
            error: function(error){
                alert(error);
            }
        });

    });
    
    $("#select-category-id").change(function() {
        var val = $(this).val();
        $("#select-model-id").empty();
        $("#select-specification-id").empty();
        $("#curtain-width-label").empty();
        $("#curtain-height-label").empty();

        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'select_category_id',
                'id': val,
            },
            success: function (response) {
                current_time = response.currenttime;
                models = response.models;
                specifications = response.specifications;

                for (let x in models) {
                    $("#select-model-id").append(models[x]);
                }
    
                for (let x in specifications) {
                    $("#select-specification-id").append(specifications[x]);
                }

                $('#curtain-width-label').append('Width: min('+response.min_width+'),max('+response.max_width+')');
                $('#curtain-height-label').append('Height: min('+response.min_height+'),max('+response.max_height+')');

                if (val==1) {
                    $('#curtain-height-label').hide();
                    $('#curtain-height').hide();
                } else {
                    $('#curtain-height-label').show();
                    $('#curtain-height').show();
                }
            },
            error: function(error){
                alert(error);
            }
        });
    });
*/    
    /* Cart Button */
/*    
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
*/
    /* Button */    
    $('[id^="btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        $(this).css('color', 'cornflowerblue');
    });
        
    $('[id^="btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', 'black');
    });

    /* QR Code Button */
/*    
    $('[id^="btn-qrcode-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        $(this).css('color', 'cornflowerblue');
    });
        
    $('[id^="btn-qrcode-"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', 'black');
    });
        
    $('[id^="btn-qrcode-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(11);
        window.location.replace("?_qrcode=" + id);
    });
*/
    /* Chat Button */
    $('[id^="btn-chat-"]').on( "click", function(e) {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(9);
        window.location.replace("?_id=" + id);

        ///construct the dialog
        $("#chat-dialog").dialog({
            autoOpen: false,
            title: 'Confirmation',
            modal: true,
            buttons: {
                "OK" : function () {
                    ///if the user confirms, proceed with the original action
                    window.location.href = targetUrl;
                },
                "Cancel" : function () {
                    ///otherwise, just close the dialog; the delete event was already interrupted
                    $(this).dialog("close");
                }
            }
        });

        $(".chatboxcontent").scrollTop($(".chatboxcontent")[0].scrollHeight);

        ///open the dialog window
        $("#chat-dialog").dialog("open");

    });

    /* Update Button */
    $('[id^="btn-edit-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(9);
        window.location.replace("?_edit=" + id);
    });

    /* Delete Button */
    $('[id^="btn-del-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(8);        
        if (window.confirm("Are you sure you want to delete this record?")) {
            window.location.replace("?_delete=" + id);
        }        
    });

    /* Delete Customer Order Button */
/*    
    $('[id^="btn-del-customer-order-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(23);        
        window.location.replace("?_delete_customer_order=" + id);
    });
*/
    /* Print Customer Order Button */
/*    
    $('[id^="btn-print-customer-order-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(25);        
        window.location.replace("?_print_customer_order=" + id);
    });
*/
    /* QR Code */
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
    $( "#dialog1" ).dialog();

    $( "#dialog" ).dialog({
        modal: true,
        close: function() {
            //form[ 0 ].reset();
            //allFields.removeClass( "ui-state-error" );
            window.location.replace("?_close=");
        }
    });
});