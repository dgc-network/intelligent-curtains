jQuery(document).ready(function($) {

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
    $('[id^="btn-qrcode-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(11);
        window.location.replace("?_qrcode=" + id);
    });

    /* Delete Customer Order Button */
    $('[id^="btn-del-customer-order-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(23);        
        window.location.replace("?_delete_customer_order=" + id);
    });

    /* Print Customer Order Button */
    $('[id^="btn-print-customer-order-"]').on( "click", function() {
        id = this.id;
        // strip the first part of the element id to leave the numeric ID
        id = id.substring(25);        
        window.location.replace("?_print_customer_order=" + id);
    });

    /**
     * Order Item Dialog and Buttons
     */
    $('[id^="btn-del-order-item-"]').on( "click", function() {
        id = this.id;
        id = id.substring(19);
        if (window.confirm("Are you sure you want to delete this record?")) {
            window.location.replace("?_course_delete=" + id);
        }        
    });

    $('[id^="btn-order-item"]').on( "click", function() {
        id = this.id;
        id = id.substring(15);
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'order_item_dialog_get_data',
                '_id': id,
            },
            success: function (response) {
/*                    
                    $("#course-id").val(id);
                    $("#course-title").val(response.course_title);
                    $("#course-info").val(response.course_info);
                    $("#course-price").val(response.course_price);
                    $("#course-period").val(response.course_period);
                    for(index=0;index<10;index++) {
                        $("#session-edit-"+index).empty();
                        $("#session-title-"+index).empty();
                        $("#session-del-"+index).empty();
                    }
                    $("#session-add").empty();
                    $.each(response.course_outline, function (index, value) {
                        $("#session-edit-"+index).append('<span id="btn-edit-session-'+value.session_id+'"><i class="fa-regular fa-pen-to-square"></i></span>');
                        $("#session-title-"+index).append('<a href="/blocks/?_block_list='+value.session_id+'">'+value.session_title+'</a>');
                        $("#session-del-"+index).append('<span id="btn-del-session-'+value.session_id+'"><i class="fa-regular fa-trash-can"></i></span>');
                    });
                    $("#session-add").append('<div id="btn-add-session-'+id+'" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div>');
    
                    $('[id^="btn-"]').mouseover(function() {
                        $(this).css('cursor', 'pointer');
                        $(this).css('color', 'red');
                    });
                        
                    $('[id^="btn-"]').mouseout(function() {
                        $(this).css('cursor', 'default');
                        $(this).css('color', 'black');
                    });
                
                    $('[id^="btn-del-session-"]').on( "click", function() {
                        id = this.id;
                        id = id.substring(16);
                        if (window.confirm("Are you sure you want to delete this record?")) {
                            window.location.replace("?_session_delete=" + id);
                        }        
                    });
    
                    $('[id^="btn-add-session-"]').on( "click", function() {
                        id = this.id;
                        id = id.substring(16);
                        $("#course-id").val(id);
                        $("#session-id").val('');
                        $("#session-title").val('');
                        $("#session-release-date").val('');
                        $("#session-release-time").val('08:00');
                        $("#session-visibility").val('');
                        $("#session-dialog").dialog('open');
                    });
                
                    $('[id^="btn-edit-session-"]').on( "click", function() {
                        id = this.id;
                        id = id.substring(17);
                        jQuery.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'session_dialog_get_data',
                                '_id': id,
                            },
                            success: function (response) {
                                $("#session-id").val(id);
                                $("#course-id").val(response.course_id);
                                $("#session-title").val(response.session_title);
                                $("#session-release-date").val(response.session_release_date);
                                $("#session-release-time").val(response.session_release_time);
                                $("#session-visibility").val(response.session_visibility);
                                $("#session-dialog").dialog('open');
                            },
                            error: function(error){
                                alert(error);
                            }
                        });
                    });
*/                
                $("#order-item-dialog").dialog('open');
            },
            error: function(error){
                alert(error);
            }
        });
    });

    $("#order-item-dialog").dialog({
        width: 800,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                var course_id = $("#course-id").val();
                var course_title = $("#course-title").val();
                var course_info = $("#course-info").val();
                var course_price = $("#course-price").val();
                var course_period = $("#course-period").val();

                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'order_item_dialog_save_data',
                        '_course_id': course_id,
                        '_course_title': course_title,
                        '_course_info': course_info,
                        '_course_price': course_price,
                        '_course_period': course_period,
                    },
                    success: function (response) {
                        window.location.replace("?_update=");
                    },
                    error: function(error){
                        alert(error);
                    }
                });
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });

    $("#order-item-dialog").dialog('close');        

});