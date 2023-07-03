jQuery(document).ready(function($) {

    /**
     * Category Dialog and Buttons
     */
    $('[id^="btn-del-category-"]').on( "click", function() {
        id = this.id;
        id = id.substring(17);
        if (window.confirm("Are you sure you want to delete this record?")) {
            window.location.replace("?_course_delete=" + id);
        }        
    });

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
                $("#category-dialog").dialog('open');
            },
            error: function(error){
                alert(error);
            }
        });
    });

    $("#category-dialog").dialog({
        width: 600,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                var curtain_category_name = $("#curtain-category-name").val();

                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'category_dialog_save_data',
                        '_curtain_category_name': curtain_category_name,
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