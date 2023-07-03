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
                $("#hide-specification").val(response.hide_specification);
                $("#hide-width").val(response.hide_width);
                $("#min-width").val(response.min_width);
                $("#max-width").val(response.max_width);
                $("#hide-height").val(response.hide_height);
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
        width: 500,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                var curtain_category_name = $("#curtain-category-name").val();
                var hide_specification = $("#hide-specification").val();
                var hide_width = $("#hide-width").val();
                var min_width = $("#min-width").val();
                var max_width = $("#max-width").val();
                var hide_height = $("#hide-height").val();
                var min_height = $("#min-height").val();
                var max_height = $("#max-height").val();

                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'category_dialog_save_data',
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

});