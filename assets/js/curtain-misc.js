// curtain-category 2024-4-25 revision
jQuery(document).ready(function($) {
    $("#search-category").on( "change", function() {
        window.location.replace("?_search="+$(this).val());
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
                //alert(error);
            }
        });    
    });

    $("#curtain-category-dialog").dialog({
        width: 450,
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
                        //alert(error);
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
                            //alert(error);
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
        window.location.replace("?_category="+$(this).val());
        $(this).val('');
    });

    $("#search-model").on( "change", function() {
        window.location.replace("?_search="+$(this).val());
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
                //alert(error);
            }
        });    
    });

    $("#curtain-model-dialog").dialog({
        width: 450,
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
                        //alert(error);
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
                            //alert(error);
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
        window.location.replace("?_category="+$(this).val());
        $(this).val('');
    });

    $("#search-specification").on( "change", function() {
        window.location.replace("?_search="+$(this).val());
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
                //alert(error);
            }
        });    
    });

    $("#curtain-specification-dialog").dialog({
        width: 450,
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
                        //alert(error);
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
                            //alert(error);
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
        window.location.replace("?_search="+$(this).val());
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
                //alert(error);
            }
        });    
    });

    $("#curtain-agent-dialog").dialog({
        width: 450,
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
                    },
                    success: function (response) {
                        window.location.replace(window.location.href);
                    },
                    error: function(error){
                        console.error(error);
                        //alert(error);
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
                            //alert(error);
                        }
                    });
                }
            }
        }
    });
});



jQuery(document).ready(function($) {

    /**
     * Category Dialog and Buttons
     */
    $('[id^="btn-category"]').on( "click", function() {
        id = this.id;
        id = id.substring(13);
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_category_dialog_data',
                '_id': id,
            },
            success: function (response) {                    
                $("#curtain-category-id").val(id);
                $("#curtain-category-name").val(response.curtain_category_name);
                if (response.allow_parts==1) {
                    $('#allow-parts').prop('checked', true);
                } else {
                    $('#allow-parts').prop('checked', false);
                }           
                if (response.hide_remote==1) {
                    $('#hide-remote').prop('checked', true);
                } else {
                    $('#hide-remote').prop('checked', false);
                }
                if (response.hide_specification==1) {
                    $('#hide-specification').prop('checked', true);
                } else {
                    $('#hide-specification').prop('checked', false);
                }            
                if (response.hide_width==1) {
                    $('#hide-width').prop('checked', true);
                } else {
                    $('#hide-width').prop('checked', false);
                }            
                $("#min-width").val(response.min_width);
                $("#max-width").val(response.max_width);
                if (response.hide_height==1) {
                    $('#hide-height').prop('checked', true);
                } else {
                    $('#hide-height').prop('checked', false);
                }            
                $("#min-height").val(response.min_height);
                $("#max-height").val(response.max_height);
                $("#category-dialog").dialog('open');
            },
            error: function(error){
                //alert(error);
            }
        });
    });

    $("#category-dialog").dialog({
        width: 450,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                var curtain_category_id = $("#curtain-category-id").val();
                var curtain_category_name = $("#curtain-category-name").val();
                var allow_parts = 0;
                if ($('#allow-parts').is(":checked")) {
                    allow_parts = 1;
                }
                var hide_remote = 0;
                if ($('#hide-remote').is(":checked")) {
                    hide_remote = 1;
                }
                var hide_specification = 0;
                if ($('#hide-specification').is(":checked")) {
                    hide_specification = 1;
                }
                var hide_width = 0;
                if ($('#hide-width').is(":checked")) {
                    hide_width = 1;
                }
                var min_width = $("#min-width").val();
                var max_width = $("#max-width").val();
                var hide_height = 0;
                if ($('#hide-height').is(":checked")) {
                    hide_height = 1;
                }
                var min_height = $("#min-height").val();
                var max_height = $("#max-height").val();

                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'save_category_dialog_data',
                        '_curtain_category_id': curtain_category_id,
                        '_curtain_category_name': curtain_category_name,
                        '_allow_parts': allow_parts,
                        '_hide_remote': hide_remote,
                        '_hide_specification': hide_specification,
                        '_hide_width': hide_width,
                        '_min_width': min_width,
                        '_max_width': max_width,
                        '_hide_height': hide_height,
                        '_min_height': min_height,
                        '_max_height': max_height,
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

    $("#category-dialog").dialog('close');        

    /**
     * Agent Dialog and Buttons
     */
    $('[id^="btn-agent"]').on( "click", function() {
        id = this.id;
        id = id.substring(10);
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'agent_dialog_get_data',
                '_id': id,
            },
            success: function (response) {                    
                $("#curtain-agent-id").val(id);
                $("#curtain-agent-number").val(response.curtain_agent_number);
                $("#curtain-agent-password").val(response.curtain_agent_password);
                $("#curtain-agent-name").val(response.curtain_agent_name);
                $("#curtain-agent-contact1").val(response.curtain_agent_contact1);
                $("#curtain-agent-phone1").val(response.curtain_agent_phone1);
                $("#curtain-agent-address").val(response.curtain_agent_address);
                $("#agent-dialog").dialog('open');
            },
            error: function(error){
                //alert(error);
            }
        });
    });

    $("#agent-dialog").dialog({
        width: 450,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                var curtain_agent_id = $("#curtain-agent-id").val();
                var curtain_agent_number = $("#curtain-agent-number").val();
                var curtain_agent_password = $("#curtain-agent-password").val();
                var curtain_agent_name = $("#curtain-agent-name").val();
                var curtain_agent_contact1 = $("#curtain-agent-contact1").val();
                var curtain_agent_phone1 = $("#curtain-agent-phone1").val();
                var curtain_agent_address = $("#curtain-agent-address").val();

                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'agent_dialog_save_data',
                        '_curtain_agent_id': curtain_agent_id,
                        '_curtain_agent_number': curtain_agent_number,
                        '_curtain_agent_password': curtain_agent_password,
                        '_curtain_agent_name': curtain_agent_name,
                        '_curtain_agent_contact1': curtain_agent_contact1,
                        '_curtain_agent_phone1': curtain_agent_phone1,
                        '_curtain_agent_address': curtain_agent_address,
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

    $("#agent-dialog").dialog('close');        

    /**
     * Model Dialog and Buttons
     */
    $('[id^="btn-model"]').on( "click", function() {
        id = this.id;
        id = id.substring(10);
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'model_dialog_get_data',
                '_id': id,
            },
            success: function (response) {                    
                $("#curtain-model-id").val(id);
                $("#curtain-model-name").val(response.curtain_model_name);
                $("#model-description").val(response.model_description);
                $("#model-price").val(response.model_price);
                $("#curtain-category-id").empty();
                $("#curtain-category-id").append(response.curtain_category_id);
                $("#curtain-vendor-name").val(response.curtain_vendor_name);
                $("#model-dialog").dialog('open');
            },
            error: function(error){
                //alert(error);
            }
        });
    });

    $("#model-dialog").dialog({
        width: 300,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                var curtain_model_id = $("#curtain-model-id").val();
                var curtain_model_name = $("#curtain-model-name").val();
                var model_description = $("#model-description").val();
                var model_price = $("#model-price").val();
                var curtain_category_id = $("#curtain-category-id").val();
                var curtain_vendor_name = $("#curtain-vendor-name").val();

                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'model_dialog_save_data',
                        '_curtain_model_id': curtain_model_id,
                        '_curtain_model_name': curtain_model_name,
                        '_model_description': model_description,
                        '_model_price': model_price,
                        '_curtain_category_id': curtain_category_id,
                        '_curtain_vendor_name': curtain_vendor_name,
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

    $("#model-dialog").dialog('close');        

    /**
     * Specification Dialog and Buttons
     */
    $('[id^="btn-specification"]').on( "click", function() {
        id = this.id;
        id = id.substring(18);
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'specification_dialog_get_data',
                '_id': id,
            },
            success: function (response) {                    
                $("#curtain-specification-id").val(id);
                $("#curtain-specification-name").val(response.curtain_specification_name);
                $("#specification-description").val(response.specification_description);
                $("#specification-price").val(response.specification_price);
                $("#specification-unit").val(response.specification_unit);
                $("#curtain-category-id").empty();
                $("#curtain-category-id").append(response.curtain_category_id);
                $("#specification-dialog").dialog('open');
            },
            error: function(error){
                //alert(error);
            }
        });
    });

    $("#specification-dialog").dialog({
        width: 300,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                var curtain_specification_id = $("#curtain-specification-id").val();
                var curtain_specification_name = $("#curtain-specification-name").val();
                var specification_description = $("#specification-description").val();
                var specification_price = $("#specification-price").val();
                var specification_unit = $("#specification-unit").val();
                var curtain_category_id = $("#curtain-category-id").val();

                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'specification_dialog_save_data',
                        '_curtain_specification_id': curtain_specification_id,
                        '_curtain_specification_name': curtain_specification_name,
                        '_specification_description': specification_description,
                        '_specification_price': specification_price,
                        '_specification_unit': specification_unit,
                        '_curtain_category_id': curtain_category_id,
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

    $("#specification-dialog").dialog('close');        

    /**
     * Remote Dialog and Buttons
     */
    $('[id^="btn-remote"]').on( "click", function() {
        id = this.id;
        id = id.substring(11);
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'remote_dialog_get_data',
                '_id': id,
            },
            success: function (response) {                    
                $("#curtain-remote-id").val(id);
                $("#curtain-remote-name").val(response.curtain_remote_name);
                $("#curtain-remote-price").val(response.curtain_remote_price);
                $("#remote-dialog").dialog('open');
            },
            error: function(error){
                //alert(error);
            }
        });
    });

    $("#remote-dialog").dialog({
        width: 300,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                var curtain_remote_id = $("#curtain-remote-id").val();
                var curtain_remote_name = $("#curtain-remote-name").val();
                var curtain_remote_price = $("#curtain-remote-price").val();

                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'remote_dialog_save_data',
                        '_curtain_remote_id': curtain_remote_id,
                        '_curtain_remote_name': curtain_remote_name,
                        '_curtain_remote_price': curtain_remote_price,
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

    $("#remote-dialog").dialog('close');        

});