jQuery(document).ready(function($) {

    // JavaScript to detect mobile browser
    if (/Mobi/.test(navigator.userAgent)) {
        // User is on a mobile device
        $('.mobile-content').show();
    } else {
        // User is not on a mobile device, send one-time password via Line
        $('.desktop-content').show();
    }

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
                        //alert(error);
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
    /* Button */    
    $('[id^="btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        //$(this).css('color', 'cornflowerblue');
        $(this).css('color', 'red');
    });
        
    $('[id^="btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', '');
    });
    
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
            window.location.replace("?_close=");
        }
    });
});