// curtain-faq on 2024-10-3
jQuery(document).ready(function($) {
    $("#search-faq").on( "change", function() {
        window.location.replace("?_search="+$(this).val()+"&paged=1");
        $(this).val('');
    });

    $('[id^="edit-curtain-faq-"]').on("click", function () {
        const curtain_faq_id = this.id.substring(17);
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_curtain_faq_dialog_data',
                _curtain_faq_id: curtain_faq_id,                
            },
            success: function (response) {
                $("#curtain-faq-dialog").html(response.html_contain);                
                $("#curtain-faq-dialog").dialog('open');                                                    
            },
            error: function (error) {
                console.error(error);
            }
        });
    });            

    $("#new-curtain-faq").on("click", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_curtain_faq_dialog_data',
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

    $("#curtain-faq-dialog").dialog({
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
                        'action': 'set_curtain_faq_dialog_data',
                        '_curtain_faq_id': $("#curtain-faq-id").val(),
                        '_faq_code': $("#faq-code").val(),
                        '_faq_question': $("#faq-question").val(),
                        '_faq_answer': $("#faq-answer").val(),
                        '_toolbox_uri': $("#toolbox-uri").val(),
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
                if (window.confirm("Are you sure you want to delete this FAQ?")) {
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'del_curtain_faq_dialog_data',
                            '_curtain_faq_id': $("#curtain-faq-id").val(),
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

// product-item 2024-8-14 revision
jQuery(document).ready(function($) {
    $("#select-category-in-product").on( "change", function() {
        window.location.replace("?_category="+$(this).val()+"&paged=1");
        $(this).val('');
    });

    $("#search-product").on( "change", function() {
        window.location.replace("?_search="+$(this).val()+"&paged=1");
        $(this).val('');
    });

    $('[id^="edit-product-item-"]').on("click", function () {
        const product_item_id = this.id.substring(18);
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_product_item_dialog_data',
                _product_item_id: product_item_id,                
            },
            success: function (response) {
                $('#product-item-dialog').html(response.html_contain);         
                $("#product-item-dialog").dialog('open');                                                    
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    });            

    $("#new-product-item").on("click", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_product_item_dialog_data',
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

    $("#product-item-dialog").dialog({
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
                        'action': 'set_product_item_dialog_data',
                        '_product_item_id': $("#product-item-id").val(),
                        '_product_item_title': $("#product-item-title").val(),
                        '_product_item_content': $("#product-item-content").val(),
                        '_curtain_category_id': $("#curtain-category-id").val(),
                        '_product_item_price': $("#product-item-price").val(),
                        '_product_item_vendor': $("#product-item-vendor").val(),
                        '_is_curtain_model': $('#is-curtain-model').is(":checked") ? 1 : 0,
                        '_is_specification': $('#is-specification').is(":checked") ? 1 : 0,
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
                            'action': 'del_product_item_dialog_data',
                            '_product_item_id': $("#product-item-id").val(),
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

// serial-number 2024-6-18 revision
jQuery(document).ready(function($) {
    $("#search-serial-number").on( "change", function() {
        window.location.replace("?_search="+$(this).val()+"&paged=1");
        $(this).val('');
    });

    $("#chat-submit").on("click", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'send_message_to_agent',
                '_curtain_agent_id': $("#curtain-agent-id").val(),
                '_curtain_user_id': $("#curtain-user-id").val(),
                '_chat_message': $("#chat-message").val(),
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

    $('[id^="edit-serial-number-"]').on("click", function () {
        const serial_number_id = this.id.substring(19);
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_serial_number_dialog_data',
                _serial_number_id: serial_number_id,                
            },
            success: function (response) {
                $('#serial-number-dialog').html(response.html_contain);                
                $("#serial-number-dialog").dialog('open');
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    });            

    $("#new-serial-number").on("click", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_serial_number_dialog_data',
                '_qr_code_serial_no': $("#qr-code-serial-no").val(),
                '_customer_order_number': $("#customer-order-number").val(),
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

    $("#serial-number-dialog").dialog({
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
                        'action': 'set_serial_number_dialog_data',
                        '_serial_number_id': $("#serial-number-id").val(),
                        '_qr_code_serial_no': $("#qr-code-serial-no").val(),
                        '_customer_order_number': $("#customer-order-number").val(),
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
                if (window.confirm("Are you sure you want to delete this serial-number?")) {
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'del_serial_number_dialog_data',
                            '_serial_number_id': $("#serial-number-id").val(),
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

// curtain-category 2024-4-25 revision
jQuery(document).ready(function($) {
    $("#search-category").on( "change", function() {
        window.location.replace("?_search="+$(this).val()+"&paged=1");
        $(this).val('');
    });

    $('[id^="edit-curtain-category-"]').on("click", function () {
        const curtain_category_id = this.id.substring(22);
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_curtain_category_dialog_data',
                _curtain_category_id: curtain_category_id,                
            },
            success: function (response) {
                $('#curtain-category-dialog').html(response.html_contain);                
                $("#curtain-category-dialog").dialog('open');                                                    
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    });            

    $("#new-curtain-category").on("click", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_curtain_category_dialog_data',
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

    $("#curtain-category-dialog").dialog({
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
                        'action': 'set_curtain_category_dialog_data',
                        '_curtain_category_id': $("#curtain-category-id").val(),
                        '_curtain_category_title': $("#curtain-category-title").val(),
                        '_curtain_min_width': $("#curtain-min-width").val(),
                        '_curtain_max_width': $("#curtain-max-width").val(),
                        '_curtain_min_height': $("#curtain-min-height").val(),
                        '_curtain_max_height': $("#curtain-max-height").val(),
                        '_is_specification': $('#is-specification').is(":checked") ? 1 : 0,
                        '_is_height': $('#is-height').is(":checked") ? 1 : 0,
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
                            'action': 'del_curtain_category_dialog_data',
                            '_curtain_category_id': $("#curtain-category-id").val(),
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

// curtain-model 2024-4-26 revision
jQuery(document).ready(function($) {
    $("#select-category-in-model").on( "change", function() {
        window.location.replace("?_category="+$(this).val()+"&paged=1");
        $(this).val('');
    });

    $("#search-model").on( "change", function() {
        window.location.replace("?_search="+$(this).val()+"&paged=1");
        $(this).val('');
    });

    $('[id^="edit-curtain-model-"]').on("click", function () {
        const curtain_model_id = this.id.substring(19);
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_curtain_model_dialog_data',
                _curtain_model_id: curtain_model_id,                
            },
            success: function (response) {
                $('#curtain-model-dialog').html(response.html_contain);         
                $("#curtain-model-dialog").dialog('open');                                                    
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    });            

    $("#new-curtain-model").on("click", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_curtain_model_dialog_data',
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

    $("#curtain-model-dialog").dialog({
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
                        'action': 'set_curtain_model_dialog_data',
                        '_curtain_model_id': $("#curtain-model-id").val(),
                        '_curtain_model_title': $("#curtain-model-title").val(),
                        '_curtain_model_description': $("#curtain-model-description").val(),
                        '_curtain_category_id': $("#curtain-category-id").val(),
                        '_curtain_model_price': $("#curtain-model-price").val(),
                        '_curtain_model_vendor': $("#curtain-model-vendor").val(),
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
                            'action': 'del_curtain_model_dialog_data',
                            '_curtain_model_id': $("#curtain-model-id").val(),
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

// curtain-specification 2024-4-26 revision
jQuery(document).ready(function($) {
    $("#select-category-in-spec").on( "change", function() {
        window.location.replace("?_category="+$(this).val()+"&paged=1");
        $(this).val('');
    });

    $("#search-specification").on( "change", function() {
        window.location.replace("?_search="+$(this).val()+"&paged=1");
        $(this).val('');
    });

    $('[id^="edit-curtain-specification-"]').on("click", function () {
        const curtain_specification_id = this.id.substring(27);
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_curtain_specification_dialog_data',
                _curtain_specification_id: curtain_specification_id,                
            },
            success: function (response) {
                $('#curtain-specification-dialog').html(response.html_contain);
                $("#curtain-specification-dialog").dialog('open');
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    });            

    $("#new-curtain-specification").on("click", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_curtain_specification_dialog_data',
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

    $("#curtain-specification-dialog").dialog({
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
                        'action': 'set_curtain_specification_dialog_data',
                        '_curtain_specification_id': $("#curtain-specification-id").val(),
                        '_curtain_specification_title': $("#curtain-specification-title").val(),
                        '_curtain_specification_description': $("#curtain-specification-description").val(),
                        '_curtain_category_id': $("#curtain-category-id").val(),
                        '_curtain_specification_price': $("#curtain-specification-price").val(),
                        '_curtain_specification_unit': $("#curtain-specification-unit").val(),
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
                            'action': 'del_curtain_specification_dialog_data',
                            '_curtain_specification_id': $("#curtain-specification-id").val(),
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

// curtain-agent 2024-4-27 revision
jQuery(document).ready(function($) {
    $("#search-agent").on( "change", function() {
        window.location.replace("?_search="+$(this).val()+"&paged=1");
        $(this).val('');
    });

    $('[id^="edit-curtain-agent-"]').on("click", function () {
        const curtain_agent_id = this.id.substring(19);
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_curtain_agent_dialog_data',
                _curtain_agent_id: curtain_agent_id,                
            },
            success: function (response) {
                $('#curtain-agent-dialog').html(response.html_contain);
                $("#curtain-agent-dialog").dialog('open');
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    });            

    $("#new-curtain-agent").on("click", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_curtain_agent_dialog_data',
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

    $("#curtain-agent-dialog").dialog({
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
                        'action': 'set_curtain_agent_dialog_data',
                        '_curtain_agent_id': $("#curtain-agent-id").val(),
                        '_curtain_agent_number': $("#curtain-agent-number").val(),
                        '_curtain_agent_name': $("#curtain-agent-name").val(),
                        '_curtain_agent_contact': $("#curtain-agent-contact").val(),
                        '_curtain_agent_phone': $("#curtain-agent-phone").val(),
                        '_curtain_agent_address': $("#curtain-agent-address").val(),
                        '_curtain_agent_status': $("#curtain-agent-status").val(),
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
                            'action': 'del_curtain_agent_dialog_data',
                            '_curtain_agent_id': $("#curtain-agent-id").val(),
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
