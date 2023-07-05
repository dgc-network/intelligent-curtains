jQuery(document).ready(function($) {

    /* Cart Button */
    $('[id^="cart-btn"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        //$(this).css('color', 'cornflowerblue');
        $(this).css('color', 'red');
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
        if (window.confirm("Are you sure you want to delete this record?")) {
            window.location.replace("?_delete_customer_order=" + id);
        }        
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
            window.location.replace("?_order_item_delete=" + id);
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
                $("#order-item-id").val(id);
                $("#curtain-category-id").empty();
                $("#curtain-category-id").append(response.curtain_category_id);
                $("#curtain-model-id").empty();
                $("#curtain-model-id").append(response.curtain_model_id);
                $("#curtain-remote-id").empty();
                $("#curtain-remote-id").append(response.curtain_remote_id);
                $("#curtain-specification-id").empty();
                $("#curtain-specification-id").append(response.curtain_specification_id);
                $("#curtain-width").val(response.curtain_width);
                $("#curtain-height").val(response.curtain_height);
                $("#order-item-qty").val(response.order_item_qty);

                if (response.is_remote_hided) {
                    $('#curtain-remote-label').hide();
                    $('#curtain-remote-id').hide();
                } else {
                    $('#curtain-remote-label').show();
                    $('#curtain-remote-id').show();
                }

                if (response.is_specification_hided) {
                    $('#curtain-specification-label').hide();
                    $('#curtain-specification-id').hide();
                } else {
                    $('#curtain-specification-label').show();
                    $('#curtain-specification-id').show();
                }

                if (response.is_width_hided) {
                    $('#curtain-width-label').hide();
                    $('#curtain-width').hide();
                } else {
                    $('#curtain-width-label').show();
                    $('#curtain-width').show();
                }

                if (response.is_height_hided) {
                    $('#curtain-height-label').hide();
                    $('#curtain-height').hide();
                } else {
                    $('#curtain-height-label').show();
                    $('#curtain-height').show();
                }

                $("#order-item-dialog").dialog('open');
            },
            error: function(error){
                alert(error);
            }
        });
    });

    $("#order-item-dialog").dialog({
        width: 300,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                var order_item_id = $("#order-item-id").val();
                var curtain_category_id = $("#curtain-category-id").val();
                var curtain_model_id = $("#curtain-model-id").val();
                var curtain_remote_id = $("#curtain-remote-id").val();
                var curtain_specification_id = $("#curtain-specification-id").val();
                var curtain_width = $("#curtain-width").val();
                var curtain_height = $("#curtain-height").val();
                var order_item_qty = $("#order-item-qty").val();

                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'order_item_dialog_save_data',
                        '_order_item_id': order_item_id,
                        '_curtain_category_id': curtain_category_id,
                        '_curtain_model_id': curtain_model_id,
                        '_curtain_remote_id': curtain_remote_id,
                        '_curtain_specification_id': curtain_specification_id,
                        '_curtain_width': curtain_width,
                        '_curtain_height': curtain_height,
                        '_order_item_qty': order_item_qty,
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

    $("#curtain-category-id").change(function() {
        var val = $(this).val();
        $("#curtain-model-id").empty();
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'select_category_id',
                'id': val,
            },
            success: function (response) {
                $("#curtain-model-id").empty();
                $("#curtain-model-id").append(response.curtain_model_id);
                //$("#curtain-remote-id").empty();
                //$("#curtain-remote-id").append(response.curtain_remote_id);
                $("#curtain-specification-id").empty();
                $("#curtain-specification-id").append(response.curtain_specification_id);
                $('#curtain-width-label').empty();
                $('#curtain-width-label').append('Width: min('+response.min_width+'),max('+response.max_width+')');
                $('#curtain-height-label').empty();
                $('#curtain-height-label').append('Height: min('+response.min_height+'),max('+response.max_height+')');

                if (response.is_remote_hided) {
                    $('#curtain-remote-label').hide();
                    $('#curtain-remote-id').hide();
                } else {
                    $('#curtain-remote-label').show();
                    $('#curtain-remote-id').show();
                }

                if (response.is_specification_hided) {
                    $('#curtain-specification-label').hide();
                    $('#curtain-specification-id').hide();
                } else {
                    $('#curtain-specification-label').show();
                    $('#curtain-specification-id').show();
                }

                if (response.is_width_hided) {
                    $('#curtain-width-label').hide();
                    $('#curtain-width').hide();
                } else {
                    $('#curtain-width-label').show();
                    $('#curtain-width').show();
                }

                if (response.is_height_hided) {
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

    $("#customer-order-status").change(function() {
        if (window.confirm("Are you sure you want to change the status?")) {
            var customer_order_status = $(this).val();
            var customer_order_number = $("#customer-order-number").val();
    
            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'select_order_status',
                    '_customer_order_number': customer_order_number,
                    '_customer_order_status': customer_order_status,
                },
                success: function (response) {
                },
                error: function(error){
                    alert(error);
                }
            });
        }
    });
    
    /**
     * Sub Items Dialog and Buttons
     */
    $('[id^="btn-del-sub-item-"]').on( "click", function() {
        id = this.id;
        id = id.substring(17);
        if (window.confirm("Are you sure you want to delete this record?")) {
            window.location.replace("?_sub_item_delete=" + id);
        }        
    });

    $('[id^="btn-sub-items"]').on( "click", function() {
        id = this.id;
        id = id.substring(14);
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'sub_items_dialog_get_data',
                '_id': id,
            },
            success: function (response) {                    
                $("#sub-item-id").val(id);
                for(index=0;index<10;index++) {
                    $("#parts-id-"+index).empty();
                    $("#parts-qty-"+index).empty();
                    $("#parts-del-"+index).empty();
                }
                $("#parts-id-add").empty();
                $("#parts-qty-add").empty();

                $.each(response.sub_item_list, function (index, value) {
                    $("#parts-id-"+index).append(value.parts_id);
                    $("#parts-qty-"+index).append(value.parts_qty);
                    $("#parts-del-"+index).append('<span id="btn-del-sub-item-'+value.sub_item_id+'"><i class="fa-regular fa-trash-can"></i></span>');
                });
                $("#parts-id-add").append('<select id="parts-id">'+response.parts_id_options+'</select>');
                $("#parts-qty-add").append('<input type="text" size="12" id="parts-qty" value="1">');

                $("#sub-items-dialog").dialog('open');
            },
            error: function(error){
                alert(error);
            }
        });
    });

    $("#sub-items-dialog").dialog({
        width: 600,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                var sub_item_id = $("#sub-item-id").val();
                var order_item_id = $("#order-item-id").val();

                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'sub_items_dialog_save_data',
                        '_sub_item_id': sub_item_id,
                        '_order_item_id': order_item_id,
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

    $("#sub-items-dialog").dialog('close');        

});