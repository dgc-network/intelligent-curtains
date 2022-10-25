jQuery(document).ready(function($) {

    $('.chatboxtextarea').on('keypress',function(e) {
        if(e.which == 13) {
            //alert('You pressed enter!');
            message = $('.chatboxtextarea').val();
            message = message.replace(/^\s+|\s+$/g,"");
    
            $('.chatboxtextarea').val('');
            $('.chatboxtextarea').focus();
            $('.chatboxtextarea').css('height','44px');
            if (message != '') {
                //$.post("chat.php?action=sendchat", {to: chatboxtitle, message: message} , function(data){
                    message = message.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;");
                    //$(".chatboxcontent").append(message)
                    $(".chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+message+'</span></div>');
                    $(".chatboxcontent").scrollTop($(".chatboxcontent")[0].scrollHeight);
                    //$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+username+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+message+'</span></div>');
                    //$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
                //});
            }
            //chatHeartbeatTime = minChatHeartbeat;
            //chatHeartbeatCount = 1;
    
            return false;
    
        }
    });


    $('#qrcode').qrcode({
        text: $("#qrcode_content").text()
    });

/* jQuery UI Dialog - Basic dialog */

    $( "#dialog" ).dialog();
    //$( "#dialog-form" ).dialog();
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


/* jQuery UI Dialog - Modal form */

    // From http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#e-mail-state-%28type=email%29

});