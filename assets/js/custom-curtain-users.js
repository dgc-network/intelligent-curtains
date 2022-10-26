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

	originalTitle = document.title;
	startChatSession();

	$([window, document]).blur(function(){
		windowFocus = false;
	}).focus(function(){
		windowFocus = true;
		document.title = originalTitle;
	});

    function startChatSession(){  
        //jQuery.post(
        $.post(
            my_foobar_client.ajaxurl, 
            {
                'action': 'startChatSession',
            }, 
            function(response) {
                username = response.username;
        
                $.each(response.items, function(i,item){
                    if (item)	{ // fix strange ie bug
        
                        chatboxtitle = item.f;
        
                        if ($("#chatbox_"+chatboxtitle).length <= 0) {
                            createChatBox(chatboxtitle,1);
                        }
                        
                        if (item.s == 1) {
                            item.f = username;
                        }
        
                        if (item.s == 2) {
                            $(".chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxinfo">'+item.m+'</span></div>');
                        } else {
                            $(".chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+item.f+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
                        }
                    }
                });
                $(".chatboxcontent").scrollTop($(".chatboxcontent")[0].scrollHeight);
                setTimeout('$(".chatboxcontent").scrollTop($(".chatboxcontent")[0].scrollHeight);', 100); // yet another strange ie bug
                setTimeout('chatHeartbeat();',chatHeartbeatTime);

            }
        );
/*    
        $.ajax({
            type: 'POST',
            cache: false,
            dataType: "json",
            url: '/wp-admin/admin-ajax.php',
            data: {
                'action': 'startChatSession',
            },
            success: function(data) {
     
                username = data.username;
        
                $.each(data.items, function(i,item){
                    if (item)	{ // fix strange ie bug
        
                        chatboxtitle = item.f;
        
                        if ($("#chatbox_"+chatboxtitle).length <= 0) {
                            createChatBox(chatboxtitle,1);
                        }
                        
                        if (item.s == 1) {
                            item.f = username;
                        }
        
                        if (item.s == 2) {
                            $("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxinfo">'+item.m+'</span></div>');
                        } else {
                            $("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+item.f+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
                        }
                    }
                });
                
                for (i=0;i<chatBoxes.length;i++) {
                    chatboxtitle = chatBoxes[i];
                    $("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
                    setTimeout('$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);', 100); // yet another strange ie bug
                }
            
                setTimeout('chatHeartbeat();',chatHeartbeatTime);
                
            },
            error: function(error){
                alert(error);
            },
        });
*/        
    }
    

    jQuery.post(
        my_foobar_client.ajaxurl, 
        {
            'action': 'chatHeartbeat',
        }, 
        function(response) {
			$.each(response.items, function(i,item){
				if (item)	{ // fix strange ie bug
					chatboxtitle = item.f;

					//if ($("#chatbox_"+chatboxtitle).length <= 0) {
					//	createChatBox(chatboxtitle);
					//}
					//if ($("#chatbox_"+chatboxtitle).css('display') == 'none') {
					//	$("#chatbox_"+chatboxtitle).css('display','block');
					//	restructureChatBoxes();
					//}
				
					if (item.s == 1) {
						//item.f = username;
					}
	
					if (item.s == 2) {
						$(".chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxinfo">'+item.m+'</span></div>');
					} else {
						//newMessages[chatboxtitle] = true;
						//newMessagesWin[chatboxtitle] = true;
						$(".chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+item.f+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
					}
	
					$(".chatboxcontent").scrollTop($(".chatboxcontent")[0].scrollHeight);
					//itemsfound += 1;
	
				}
			});

        }
    );


/*
	$.ajax({
        type: 'POST',
        url: '/wp-admin/admin-ajax.php',
		cache: false,
		dataType: "json",
        data: {
            'action': 'chatHeartbeat',
        },
	  	success: function(data) {

			$.each(data.items, function(i,item){
				if (item)	{ // fix strange ie bug
					chatboxtitle = item.f;

					//if ($("#chatbox_"+chatboxtitle).length <= 0) {
					//	createChatBox(chatboxtitle);
					//}
					//if ($("#chatbox_"+chatboxtitle).css('display') == 'none') {
					//	$("#chatbox_"+chatboxtitle).css('display','block');
					//	restructureChatBoxes();
					//}
				
					if (item.s == 1) {
						//item.f = username;
					}
	
					if (item.s == 2) {
						$(".chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxinfo">'+item.m+'</span></div>');
					} else {
						//newMessages[chatboxtitle] = true;
						//newMessagesWin[chatboxtitle] = true;
						$(".chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+item.f+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
					}
	
					$(".chatboxcontent").scrollTop($(".chatboxcontent")[0].scrollHeight);
					//itemsfound += 1;
	
				}
			});

			//chatHeartbeatCount++;

			//if (itemsfound > 0) {
			//	chatHeartbeatTime = minChatHeartbeat;
			//	chatHeartbeatCount = 1;
			//} else if (chatHeartbeatCount >= 10) {
			//	chatHeartbeatTime *= 2;
			//	chatHeartbeatCount = 1;
			//	if (chatHeartbeatTime > maxChatHeartbeat) {
			//		chatHeartbeatTime = maxChatHeartbeat;
			//	}
			//}
			
			//setTimeout('chatHeartbeat();',chatHeartbeatTime);
		},
        error: function(error){
            alert(error);
        },
	});
*/
    $('.chatboxtextarea').on('keypress',function(e) {
        if(e.which == 13) {
            //alert('You pressed enter!');
            message = $('.chatboxtextarea').val();
            message = message.replace(/^\s+|\s+$/g,"");
    
            $('.chatboxtextarea').val('');
            $('.chatboxtextarea').focus();
            $('.chatboxtextarea').css('height','44px');
            if (message != '') {

                jQuery.post(
                    my_foobar_client.ajaxurl, 
                    {
                        'action': 'sendChat',
                        'to': 'Uc12a5ff53a702d188e609709d6ef3edf',
                        'message': message,
                    }, 
                    function(response) {
                        message = message.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;");
                        $(".chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+message+'</span></div>');
                        $(".chatboxcontent").scrollTop($(".chatboxcontent")[0].scrollHeight);
                    }
                );

                //$.post("chat.php?action=sendchat", {to: chatboxtitle, message: message} , function(data){
                    //message = message.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;");
                    //$(".chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+message+'</span></div>');
                    //$(".chatboxcontent").scrollTop($(".chatboxcontent")[0].scrollHeight);
                    //$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+username+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+message+'</span></div>');
                    //$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
                //});
            }
            //chatHeartbeatTime = minChatHeartbeat;
            //chatHeartbeatCount = 1;
    
            return false;
    
        }
    });


});