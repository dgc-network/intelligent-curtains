jQuery(document).ready(function($) {
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
*/    
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
                current_time = response.currenttime;
                models = response.models;
                specifications = response.specifications;

                for (let x in models) {
                    $("#curtain-model-id").append(models[x]);
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
/*    
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

                $("#order-item-dialog").dialog('open');
            },
            error: function(error){
                alert(error);
            }
        });
    });

    $("#order-item-dialog").dialog({
        width: 600,
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

});