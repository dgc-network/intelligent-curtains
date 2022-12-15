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

    $("#select-product-id").change(function() {
        var val = $(this).val();
        $("#select-model-id").empty();
        $("#select-specification-id").empty();

        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'select_product_id',
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
            },
            error: function(error){
                alert(error);
            }
        });
    });
    
    /* Cart Button */
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

    /* QR Code Button */
    $('[id^="qrcode-btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        $(this).css('color', 'cornflowerblue');
    });
        
    $('[id^="qrcode-btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', 'black');
    });
        
    $('[id^="qrcode-btn-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(11);
        window.location.replace("?_qrcode=" + id);
    });

    /* Chat Button */
    $('[id^="chat-btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        $(this).css('color', 'cornflowerblue');
    });
        
    $('[id^="chat-btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', 'black');
    });
        
    $('[id^="chat-btn-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(9);
        window.location.replace("?_id=" + id);
    });

    /* Update Button */
    $('[id^="edit-btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        $(this).css('color', 'cornflowerblue');
    });
        
    $('[id^="edit-btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', 'black');
    });
        
    $('[id^="edit-btn-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(9);
        window.location.replace("?_edit=" + id);
    });

    /* Delete Button */
    $('[id^="del-btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        $(this).css('color', 'cornflowerblue');
    });
        
    $('[id^="del-btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', 'black');
    });
        
    $('[id^="del-btn-"]').on( "click", function() {
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
        close: function() {
            form[ 0 ].reset();
            allFields.removeClass( "ui-state-error" );
            window.location.replace("?_close=");
            alert("對話框關閉");
        }
    });
/*
    // 設定對話框
    $("#dialog").dialog({
        autoOpen: false, // 對話框一開始隱藏
        close:function(event, ui){ // 對話框關閉時觸發的方法
            alert("對話框關閉");
        }
    });
  
    function openDialog(){
        $("#dialog").dialog("open"); // 點選按鈕時開啟對話框
    }
*/
    /* jQuery UI Dialog - Modal form */
    $( "#dialog-form" ).dialog({
        //autoOpen: false,
        //autoOpen: true,
        height: 450,
        width: 500,
        //modal: true,

        buttons: {
            //"Create": addUser,
            Cancel: function() {
                //dialog.dialog( "close" );
                $(this).dialog("close");
            }
        },
        
        close: function() {
            form[ 0 ].reset();
            allFields.removeClass( "ui-state-error" );
        }
    
    });

    ///on class click
    $(".delete").click(function (e) {
        e.preventDefault(); ///first, prevent the action
        var targetUrl = $(this).attr("href"); ///the original delete call

        ///construct the dialog
        $("#dialog_id").dialog({
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

        ///open the dialog window
        $("#dialog_id").dialog("open");
    });

});