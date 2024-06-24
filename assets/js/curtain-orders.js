// login-users 2024-6-12 revision
jQuery(document).ready(function($) {
    $("#search-user").on( "change", function() {
        window.location.replace("?_search="+$(this).val());
        $(this).val('');
    });

    $('[id^="edit-login-user-"]').on("click", function () {
        const login_user_id = this.id.substring(16);
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_login_user_dialog_data',
                _login_user_id: login_user_id,                
            },
            success: function (response) {
                $("#login-user-dialog").html(response.html_contain);
                $("#login-user-dialog").dialog('open');
            },
            error: function (error) {
                console.error(error);
            }
        });
    });            

    $("#login-user-dialog").dialog({
        width: 390,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'set_login_user_dialog_data',
                        '_login_user_id': $("#login-user-id").val(),
                        '_display_name': $("#display-name").val(),
                        '_user_email': $("#user-email").val(),
                        '_is_warehouse_personnel': $('#is-warehouse-personnel').is(":checked") ? 1 : 0,
                    },
                    success: function (response) {
                        window.location.replace(window.location.href);
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });
            },
            "Delete": function() {
                if (window.confirm("Are you sure you want to delete this user?")) {
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'del_login_user_dialog_data',
                            '_login_user_id': $("#login-user-id").val(),
                        },
                        success: function (response) {
                            window.location.replace(window.location.href);
                        },
                        error: function(error){
                            console.error(error);
                            alert(error);
                        }
                    });
                }
            }
        }
    });

    $("#new-login-user").on("click", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_login_user_dialog_data',
            },
            success: function (response) {
                $("#new-user-dialog").html(response.html_contain);
                $("#new-user-dialog").dialog('open');
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });    
    });

    $("#new-user-dialog").dialog({
        width: 390,
        modal: true,
        autoOpen: false,
        buttons: {
            "Add": function() {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'set_login_user_dialog_data',
                        //'_login_user_id': $("#login-user-id").val(),
                        '_display_name': $("#display-name").val(),
                        '_user_email': $("#user-email").val(),
                    },
                    success: function (response) {
                        $("#new-user-dialog").dialog('close');
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });
            },
            "Cancel": function() {
                window.location.replace(window.location.href);
            }
        }
    });
});

// order-status 2024-6-11 revision
jQuery(document).ready(function($) {
    $("#search-status").on( "change", function() {
        window.location.replace("?_search="+$(this).val());
        $(this).val('');
    });

    $('[id^="edit-order-status-"]').on("click", function () {
        const order_status_id = this.id.substring(18);
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_order_status_dialog_data',
                _order_status_id: order_status_id,                
            },
            success: function (response) {
                $("#order-status-dialog").html(response.html_contain);                
                $("#order-status-dialog").dialog('open');                                                    
            },
            error: function (error) {
                console.error(error);
            }
        });
    });            

    $("#new-order-status").on("click", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_order_status_dialog_data',
            },
            success: function (response) {
                window.location.replace(window.location.href);
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });    
    });

    $("#order-status-dialog").dialog({
        width: 390,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'set_order_status_dialog_data',
                        '_order_status_id': $("#order-status-id").val(),
                        '_status_action': $("#status-action").val(),
                        '_status_color': $("#status-color").val(),
                        '_status_code': $("#status-code").val(),
                        '_next_status': $("#next-status").val(),
                        '_status_title': $("#status-title").val(),
                        '_status_description': $("#status-description").val(),
                    },
                    success: function (response) {
                        window.location.replace(window.location.href);
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });
            },
            "Delete": function() {
                if (window.confirm("Are you sure you want to delete this item?")) {
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'del_order_status_dialog_data',
                            '_order_status_id': $("#order-status-id").val(),
                        },
                        success: function (response) {
                            window.location.replace(window.location.href);
                        },
                        error: function(error){
                            console.error(error);
                            alert(error);
                        }
                    });
                }
            }
        }
    });
});

// 2024-4-19 customer-order revision
jQuery(document).ready(function($) {

    $("#agent-submit").on("click", function() {
        const ajaxData = {
            'action': 'set_curtain_agent_id',
        };
        ajaxData['_agent_number'] = $("#agent-number").val();
        ajaxData['_agent_password'] = $("#agent-password").val();
        ajaxData['_display_name'] = $("#display-name").val();
        ajaxData['_user_email'] = $("#user-email").val();

        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: ajaxData,
            success: function (response) {
                window.location.replace(window.location.href);
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });    
    });

    $("#select-order-category").on("change", function() {
        // Initialize an empty array to store query parameters
        var queryParams = [];
    
        // Check the selected value for each select element and add it to the queryParams array
        var categoryValue = $("#select-order-category").val();
        if (categoryValue) {
            queryParams.push("_category=" + categoryValue);
        }
    
        var agentIdValue = $("#select-curtain-agent").val();
        if (agentIdValue) {
            queryParams.push("_curtain_agent_id=" + agentIdValue);
        }
    
        // Combine all query parameters into a single string
        var queryString = queryParams.join("&");
    
        // Redirect to the new URL with all combined query parameters
        window.location.href = "?" + queryString;
    
        // Clear the values of all select elements after redirection
        $("#select-order-category, #select-curtain-agent").val('');
    
        // Toggle visibility of elements if needed
        $("#quotation-title, #quotation-select, #customer-order-title, #customer-order-select").toggle();
    });
    
    $("#select-curtain-agent").on("change", function() {
        // Initialize an empty array to store query parameters
        var queryParams = [];
    
        // Check the selected value for each select element and add it to the queryParams array
        var categoryValue = $("#select-order-category").val();
        if (categoryValue) {
            queryParams.push("_category=" + categoryValue);
        }
    
        var agentIdValue = $("#select-curtain-agent").val();
        if (agentIdValue) {
            queryParams.push("_curtain_agent_id=" + agentIdValue);
        }
    
        // Combine all query parameters into a single string
        var queryString = queryParams.join("&");
    
        // Redirect to the new URL with all combined query parameters
        window.location.href = "?" + queryString;
    
        // Clear the values of all select elements after redirection
        $("#select-order-category, #select-curtain-agent").val('');
    
    });

    $("#search-order").on( "change", function() {
        window.location.replace("?_search="+$(this).val());
        $(this).val('');
    });

    $('[id^="edit-quotation-"]').on("click", function () {
        const customer_order_id = this.id.substring(15);
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_customer_order_dialog_data',
                _customer_order_id: customer_order_id,
                _is_admin: $("#is-admin").val()
            },
            success: function (response) {

                $('#result-container').html(response.html_contain);

                $(".datepicker").datepicker({
                    onSelect: function(dateText, inst) {
                        $(this).val(dateText);
                    }
                });            

                $("#save-quotation").on("click", function() {
                    const ajaxData = {
                        'action': 'set_quotation_dialog_data',
                    };
                    ajaxData['_customer_order_id'] = customer_order_id;
                    ajaxData['_customer_name'] = $("#customer-name").val();
                    ajaxData['_customer_order_amount'] = $("#customer-order-amount").val();
                    ajaxData['_customer_order_remark'] = $("#customer-order-remark").val();
                            
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: ajaxData,
                        success: function (response) {
                            window.location.replace(window.location.href);
                        },
                        error: function(error){
                            console.error(error);
                            alert(error);
                        }
                    });
                });

                $("#del-quotation").on("click", function() {
                    if (window.confirm("Are you sure you want to delete this quotation?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_quotation_dialog_data',
                                '_customer_order_id': customer_order_id,
                            },
                            success: function (response) {
                                window.location.replace(window.location.href);
                            },
                            error: function(error){
                                console.error(error);
                                alert(error);
                            }
                        });
                    }
                });

                $("#exit-customer-order-dialog").on("click", function () {
                    window.location.replace(window.location.href);
                });

                $('[id^="cancel-customer-order-"]').on("click", function () {
                    if (window.confirm("Are you sure you want to cancel this order?")) {
                        const customer_order_id = this.id.substring(22);
                        const ajaxData = {
                            'action': 'proceed_customer_order_status',
                        };
                        ajaxData['_next_status'] = 0;
                        ajaxData['_customer_order_id'] = $("#customer-order-id").val();
                        ajaxData['_customer_order_amount'] = $("#customer-order-amount").val();
                    
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: ajaxData,
                            success: function (response) {
                                window.location.replace(window.location.href);
                            },
                            error: function(error){
                                console.error(error);                    
                                alert(error);
                            }
                        });
                    }
                });

                $('[id^="print-customer-order-"]').on("click", function () {
                    const customer_order_id = this.id.substring(21);
                    $.ajax({
                        url: ajax_object.ajax_url,
                        type: 'post',
                        data: {
                            action: 'print_customer_order_data',
                            _customer_order_id: customer_order_id,
                        },
                        success: function (response) {            
                            $('#result-container').html(response.html_contain);
                            $("#exit-customer-order-printing").on("click", function () {
                                window.location.replace(window.location.href);
                            });                        
                        }
                    });
                });

                $('[id^="display-account-receivable-"]').on("click", function () {
                    const curtain_agent_id = this.id.substring(27);
                    $.ajax({
                        url: ajax_object.ajax_url,
                        type: 'post',
                        data: {
                            action: 'get_account_receivable_summary_data',
                            _curtain_agent_id: curtain_agent_id,
                        },
                        success: function (response) {            
                            $('#account-receivable-dialog').html(response.html_contain);
                            $('#account-receivable-dialog').dialog('open');
                        }
                    });
                });

                $("#account-receivable-dialog").dialog({                    
                    width: 500,
                    modal: true,
                    autoOpen: false,
                    buttons: {
                        "列印明細": function() {
                            var checkedIds = [];
                            $('.customer_order_ids:checked').each(function() {
                                checkedIds.push(this.id);
                            });

                            jQuery.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'get_account_receivable_detail_data',
                                    '_customer_order_ids': checkedIds,
                                },
                                success: function (response) {
                                    $('#result-container').html(response.html_contain);
                                    $('#account-receivable-dialog').dialog('close');
                                },
                                error: function(error){
                                    console.error(error);
                                    alert(error);
                                }
                            });
                        },
                    }
                });

                $('[id^="proceed-customer-order-status-"]').on("click", function () {
                    const statusCode = $("#status-code").val();
                    const taobaoOrderNumber = $("#taobao-order-number").val();
                    const taobaoShipNumber = $("#taobao-ship-number").val();
                    const curtainShipNumber = $("#curtain-ship-number").val();

                    if (statusCode=='order01' && !taobaoOrderNumber) {
                        alert("Taobao order number cannot be empty!");
                        return; // Stop the process if the value is empty
                    }
                    if (statusCode=='order02' && !taobaoShipNumber) {
                        alert("Taobao ship number cannot be empty!");
                        return; // Stop the process if the value is empty
                    }
                    if (statusCode=='order03' && !curtainShipNumber) {
                        alert("Curtain ship number cannot be empty!");
                        return; // Stop the process if the value is empty
                    }

                    const next_status = this.id.substring(30);
                    const ajaxData = {
                        'action': 'proceed_customer_order_status',
                    };
                    ajaxData['_next_status'] = next_status;
                    ajaxData['_customer_order_id'] = $("#customer-order-id").val();
                    ajaxData['_customer_order_amount'] = $("#customer-order-amount").val();
                    ajaxData['_taobao_order_number'] = $("#taobao-order-number").val();
                    ajaxData['_taobao_ship_number'] = $("#taobao-ship-number").val();
                    ajaxData['_curtain_ship_number'] = $("#curtain-ship-number").val();
                    //ajaxData['_curtain_ship_date'] = $("#curtain-ship-date").val();
            
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: ajaxData,
                        success: function (response) {
                            window.location.replace(window.location.href);
                        },
                        error: function(error){
                            console.error(error);                    
                            alert(error);
                        }
                    });    
                });
                        
                activate_order_item_list_data(customer_order_id);
                activate_customer_order_dialog_data();

            },
            error: function (error) {
                console.error(error);
            }
        });
    });            

    $("#new-quotation").on("click", function() {
        const ajaxData = {
            'action': 'set_quotation_dialog_data',
        };
        ajaxData['_curtain_agent_id'] = $("#select-curtain-agent").val();

        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: ajaxData,
            success: function (response) {
                window.location.replace(window.location.href);
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });    
    });

    function activate_customer_order_dialog_data() {
        $("#qr-code-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
        });

        $("#new-order-item-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {
                "Add": function() {
                    jQuery.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_order_item_dialog_data',
                            '_curtain_category_id': $("#curtain-category-id").val(),
                            '_curtain_model_id': $("#curtain-model-id").val(),
                            '_curtain_specification_id': $("#curtain-specification-id").val(),
                            '_curtain_width': $("#curtain-width").val(),
                            '_curtain_height': $("#curtain-height").val(),
                            '_order_item_qty': $("#order-item-qty").val(),
                            '_order_item_note': $("#order-item-note").val(),
                            '_customer_order_id': $("#customer-order-id").val(),
                            '_customer_order_amount': $("#customer-order-amount").val(),
                        },
                        success: function (response) {
                            $('#order-item-container').html(response.html_contain);
                            $("#new-order-item-dialog").dialog('close');
                            activate_order_item_list_data($("#customer-order-id").val());
            
                        },
                        error: function(error){
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Cancel": function() {
                    if (window.confirm("Are you sure you want to cancel this item?")) {
                        $("#new-order-item-dialog").dialog('close');
                    }
                }
            }
        });
    
        $("#curtain-order-item-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {
                "Save": function() {
                    jQuery.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_order_item_dialog_data',
                            '_order_item_id': $("#order-item-id").val(),
                            '_curtain_category_id': $("#curtain-category-id").val(),
                            '_curtain_model_id': $("#curtain-model-id").val(),
                            '_curtain_specification_id': $("#curtain-specification-id").val(),
                            '_curtain_width': $("#curtain-width").val(),
                            '_curtain_height': $("#curtain-height").val(),
                            '_order_item_qty': $("#order-item-qty").val(),
                            '_order_item_note': $("#order-item-note").val(),
                            '_customer_order_amount': $("#customer-order-amount").val(),
                        },
                        success: function (response) {
                            $('#order-item-container').html(response.html_contain);
                            $("#curtain-order-item-dialog").dialog('close');
                            activate_order_item_list_data($("#customer-order-id").val());
            
                        },
                        error: function(error){
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function() {
                    if (window.confirm("Are you sure you want to delete this item?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_order_item_dialog_data',
                                '_order_item_id': $("#order-item-id").val(),
                            },
                            success: function (response) {
                                $('#order-item-container').html(response.html_contain);
                                $("#curtain-order-item-dialog").dialog('close');
                                activate_order_item_list_data($("#customer-order-id").val());
                            },
                            error: function(error){
                                console.error(error);
                                alert(error);
                            }
                        });
                    }
                }
            }
        });        
    };

    function activate_curtain_category_id_data(order_item_id=false) {
        $("#curtain-category-id").on( "change", function() {
            $.ajax({
                url: ajax_object.ajax_url,
                type: 'post',
                data: {
                    action: 'get_order_item_dialog_data',
                    _order_item_id: order_item_id,
                    _curtain_category_id: $(this).val(),
                },
                success: function (response) {
                    $('#new-order-item-dialog').html(response.html_contain);
                    $('#curtain-order-item-dialog').html(response.html_contain);
                    activate_order_item_list_data($("#customer-order-id").val());
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    }

    activate_order_item_list_data();
    function activate_order_item_list_data(customer_order_id) {
        $("#new-order-item").on("click", function() {
            $.ajax({
                url: ajax_object.ajax_url,
                type: 'post',
                data: {
                    action: 'get_order_item_dialog_data',
                },
                success: function (response) {
                    $('#new-order-item-dialog').html(response.html_contain);
                    $("#new-order-item-dialog").dialog('open');
                    activate_curtain_category_id_data();
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });

        $('[id^="edit-order-item-"]').on("click", function () {
            const order_item_id = this.id.substring(16);
            $.ajax({
                url: ajax_object.ajax_url,
                type: 'post',
                data: {
                    action: 'get_order_item_dialog_data',
                    _order_item_id: order_item_id,
                },
                success: function (response) {
                    $('#curtain-order-item-dialog').html(response.html_contain);
                    $("#curtain-order-item-dialog").dialog('open');
                    activate_curtain_category_id_data(order_item_id);                            
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });

        $('[id^="view-qr-code-"]').on("click", function () {
            const order_item_id = this.id.substring(13);
            $.ajax({
                url: ajax_object.ajax_url,
                type: 'post',
                data: {
                    action: 'get_order_item_dialog_data',
                    _order_item_id: order_item_id,
                },
                success: function (response) {
                    $('#qr-code-dialog').html(response.qr_code_dialog);
                    $("#qr-code-dialog").dialog('open');
                    //activate_curtain_category_id_data(order_item_id);

                    $('#qrcode').qrcode({
                        text: $("#qrcode_content").text()
                    });                                
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    };
});


jQuery(document).ready(function($) {

    /* Cart Button */
    $('[id^="cart-btn"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        //$(this).css('color', 'cornflowerblue');
        $(this).css('color', 'red');
    });
        
    $('[id^="cart-btn"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', '');
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
    $('[id^="btn-customer-order-del-"]').on( "click", function() {
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
    $('[id^="btn-order-item-del-"]').on( "click", function() {
        id = this.id;
        id = id.substring(19);
        if (window.confirm("Are you sure you want to delete this item?")) {
            window.location.replace("?_order_item_delete=" + id);
        }        
    });

    $('[id^="btn-add-order-item"]').on( "click", function() {
        $("#order-add-item-dialog").dialog('open');
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
                //alert(error);
            }
        });
    });

    $("#order-add-item-dialog").dialog({
        width: 350,
        modal: true,
        autoOpen: false,
        buttons: {
            "Add": function() {
                //var order_item_id = $("#order-item-id").val();
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
                        'action': 'order_item_dialog_add_data',
                        //'_order_item_id': order_item_id,
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
                        //alert(error);
                    }
                });
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });
    $("#order-add-item-dialog").dialog('close');        

    $("#order-item-dialog").dialog({
        width: 350,
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
                        //alert(error);
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
                //$('#curtain-width-label').append('Width: min('+response.min_width+'),max('+response.max_width+')');
                $('#curtain-width-label').append('寬度: 不得少於('+response.min_width+'),不得大於('+response.max_width+')');
                $('#curtain-height-label').empty();
                //$('#curtain-height-label').append('Height: min('+response.min_height+'),max('+response.max_height+')');
                $('#curtain-height-label').append('高度: 不得少於('+response.min_height+'),不得大於('+response.max_height+')');

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
                //alert(error);
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
                    //alert(error);
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

    $('[id^="btn-sub-items-"]').on( "click", function() {
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
                $("#order-item-id").val(id);
                for(index=0;index<10;index++) {
                    $("#sub-item-"+index).hide();
                    $("#parts-id-"+index).empty();
                    $("#parts-qty-"+index).empty();
                    $("#parts-del-"+index).empty();
                }
                //$("#parts-id-add").empty();
                //$("#parts-qty-add").empty();

                $.each(response.sub_item_list, function (index, value) {
                    $("#sub-item-"+index).show();
                    $("#parts-id-"+index).append(value.parts_id);
                    $("#parts-qty-"+index).append(value.parts_qty);
                    $("#parts-del-"+index).append('<span id="btn-del-sub-item-'+value.sub_item_id+'"><i class="fa-regular fa-trash-can"></i></span>');
                });

                $('[id^="btn-"]').mouseover(function() {
                    $(this).css('cursor', 'pointer');
                    $(this).css('color', 'red');
                });
                    
                $('[id^="btn-"]').mouseout(function() {
                    $(this).css('cursor', 'default');
                    $(this).css('color', '');
                });
            
                $('[id^="btn-del-sub-item-"]').on( "click", function() {
                    id = this.id;
                    id = id.substring(17);
                    if (window.confirm("Are you sure you want to delete this record?")) {
                        window.location.replace("?_delete_sub_item=" + id);
                    }        
                });

                $("#sub-items-dialog").dialog('open');
            },
            error: function(error){
                //alert(error);
            }
        });
    });

    $("#sub-items-dialog").dialog({
        width: 600,
        modal: true,
        autoOpen: false,
        buttons: {
            "Add": function() {
                //var sub_item_id = $("#sub-item-id").val();
                var parts_id = $("#parts-id").val();
                var parts_qty = $("#parts-qty").val();
                var order_item_id = $("#order-item-id").val();

                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'sub_items_dialog_save_data',
                        '_parts_id': parts_id,
                        '_parts_qty': parts_qty,
                        '_order_item_id': order_item_id,
                    },
                    success: function (response) {
                        window.location.replace("?_update=");
                    },
                    error: function(error){
                        //alert(error);
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

