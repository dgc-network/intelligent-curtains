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
                        '_is_factory_personnel': $('#is-factory-personnel').is(":checked") ? 1 : 0,
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
    //* Cart Button
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
                        
                activate_customer_order_dialog_data(customer_order_id);

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

    function activate_customer_order_dialog_data(customer_order_id) {
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
                    activate_curtain_category_id_data(order_item_id);

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
                            $("#new-order-item-dialog").dialog('close');
                            $('#order-item-container').html(response.html_contain);
                            activate_customer_order_dialog_data(customer_order_id);
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
                            $("#curtain-order-item-dialog").dialog('close');
                            $('#order-item-container').html(response.html_contain);
                            activate_customer_order_dialog_data(customer_order_id);            
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
                                $("#curtain-order-item-dialog").dialog('close');
                                $('#order-item-container').html(response.html_contain);
                                activate_customer_order_dialog_data(customer_order_id);
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
                    if (order_item_id){
                        $('#curtain-order-item-dialog').html(response.html_contain);
                        activate_curtain_category_id_data(order_item_id);
                    } else {
                        $('#new-order-item-dialog').html(response.html_contain);
                        activate_curtain_category_id_data();
                    }
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    }
});
