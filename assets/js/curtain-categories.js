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
                'action': 'category_dialog_get_data',
                '_id': id,
            },
            success: function (response) {                    
                $("#curtain-category-id").val(id);
                $("#curtain-category-name").val(response.curtain_category_name);
                //$("#hide-specification").val(response.hide_specification);
                if (response.hide_specification==1) {
                    $('#hide-specification').prop('checked', true);
                }            
                //$("#hide-width").val(response.hide_width);
                if (response.hide_width==1) {
                    $('#hide-width').prop('checked', true);
                }            
                $("#min-width").val(response.min_width);
                $("#max-width").val(response.max_width);
                //$("#hide-height").val(response.hide_height);
                if (response.hide_height==1) {
                    $('#hide-height').prop('checked', true);
                }            
                $("#min-height").val(response.min_height);
                $("#max-height").val(response.max_height);
                $("#category-dialog").dialog('open');
            },
            error: function(error){
                alert(error);
            }
        });
    });

    $("#category-dialog").dialog({
        width: 400,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                var curtain_category_id = $("#curtain-category-id").val();
                var curtain_category_name = $("#curtain-category-name").val();
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
                        'action': 'category_dialog_save_data',
                        '_curtain_category_id': curtain_category_id,
                        '_curtain_category_name': curtain_category_name,
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
                        alert(error);
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
                alert(error);
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
                        alert(error);
                    }
                });
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });

    $("#agent-dialog").dialog('close');        

});