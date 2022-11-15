jQuery(document).ready(function($) {

    var windowFocus = true;
    var username;
    var chatHeartbeatCount = 0;
    var minChatHeartbeat = 1000;
    var maxChatHeartbeat = 33000;
    var chatHeartbeatTime = minChatHeartbeat;
    var originalTitle;
    var blinkOrder = 0;
    
    var chatboxFocus = new Array();
    var newMessages = new Array();
    var newMessagesWin = new Array();
    var chatBoxes = new Array();
    var chatboxtitle;
/*    
	originalTitle = document.title;
	$([window, document]).blur(function(){
		windowFocus = false;
	}).focus(function(){
		windowFocus = true;
		document.title = originalTitle;
	});
*/
	$(".startChatSession").click(function(){
        $(".chatboxcontent").scrollTop($(".chatboxcontent")[0].scrollHeight);
        setTimeout('$(".chatboxcontent").scrollTop($(".chatboxcontent")[0].scrollHeight);', 1000); // yet another strange ie bug
    });

    $('.chatboxtextarea').on('keypress',function(e) {
        chatboxtitle = $('.chatboxtitle').val();
        checkChatBoxInputKey(e,this,chatboxtitle);
    });

    function checkChatBoxInputKey(event,chatboxtextarea,chatboxtitle) {
	 
        if(event.keyCode == 13 && event.shiftKey == 0)  {
            message = $(chatboxtextarea).val();
            message = message.replace(/^\s+|\s+$/g,"");
            //alert('chatboxtitle:'+chatboxtitle+', message:'+message);
    
            $(chatboxtextarea).val('');
            $(chatboxtextarea).focus();
            $(chatboxtextarea).css('height','44px');
            if (message != '') {
                jQuery.ajax({
                    type: 'POST',
                    url: '/wp-admin/admin-ajax.php',
                    dataType: "json",
                    data: {
                        'action': 'sendChat',
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
    }

    function chatHeartbeat(){

        var itemsfound = 0;
        
        if (windowFocus == false) {
     
            var blinkNumber = 0;
            var titleChanged = 0;
            for (x in newMessagesWin) {
                if (newMessagesWin[x] == true) {
                    ++blinkNumber;
                    if (blinkNumber >= blinkOrder) {
                        document.title = x+' says...';
                        titleChanged = 1;
                        break;	
                    }
                }
            }
            
            if (titleChanged == 0) {
                document.title = originalTitle;
                blinkOrder = 0;
            } else {
                ++blinkOrder;
            }
    
        } else {
            for (x in newMessagesWin) {
                newMessagesWin[x] = false;
            }
        }
    
        for (x in newMessages) {
            if (newMessages[x] == true) {
                if (chatboxFocus[x] == false) {
                    //FIXME: add toggle all or none policy, otherwise it looks funny
                    $('#chatbox_'+x+' .chatboxhead').toggleClass('chatboxblink');
                }
            }
        }
        
        jQuery.ajax({
            type: 'POST',
            url: '/wp-admin/admin-ajax.php',
            dataType: "json",
            data: {
                'action': 'chatHeartbeat',
            },
            success: function (response) {
                $.each(response.items, function(i,item){
                    if (item)	{ // fix strange ie bug
                        chatboxtitle = item.f;

                        //if ($("#chatbox_"+chatboxtitle).length <= 0) {
                        //    createChatBox(chatboxtitle);
                        //}
                        //if ($("#chatbox_"+chatboxtitle).css('display') == 'none') {
                        //    $("#chatbox_"+chatboxtitle).css('display','block');
                        //    restructureChatBoxes();
                        //}
           
                        if (item.s == 1) {
                            item.f = username;
                        }
        
                        if (item.s == 2) {
                            $(".chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxinfo">'+item.m+'</span></div>');
                        } else {
                            newMessages[chatboxtitle] = true;
                            newMessagesWin[chatboxtitle] = true;
                            $(".chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+item.f+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
                        }
        
                        $(".chatboxcontent").scrollTop($(".chatboxcontent")[0].scrollHeight);
                        itemsfound += 1;
        
                    }
                });
    
                chatHeartbeatCount++;
    
                if (itemsfound > 0) {
                    chatHeartbeatTime = minChatHeartbeat;
                    chatHeartbeatCount = 1;
                } else if (chatHeartbeatCount >= 10) {
                    chatHeartbeatTime *= 2;
                    chatHeartbeatCount = 1;
                    if (chatHeartbeatTime > maxChatHeartbeat) {
                        chatHeartbeatTime = maxChatHeartbeat;
                    }
                }
                
                setTimeout('chatHeartbeat();',chatHeartbeatTime);
            },
            error: function(error){
                alert(error);
            }
        });
    }    
});