jQuery(document).ready(function($) {

    var categories = '';
    $.ajax({
        type: 'POST',
        url: '/wp-admin/admin-ajax.php',
        dataType: "json",
        data: {
            'action': 'get_categories',
        },
        success: function (data) {
            categories = data;
        },
        error: function(error){
            alert(error);
        }
    });

    $( '.opt_categorias', sub_element ).on( 'change', function() {
        var opt_categorias = this.value;
        $.ajax({
            type: 'POST',
            url: '/wp-admin/admin-ajax.php',
            dataType: "json",
            data: {
                'action': 'get_product_by_category',
                'term_chosen': opt_categorias,
            },
            success: function (data) {
                //alert(data);
                $( '.opt_tipo', sub_element ).empty();
                $( '.opt_tipo', sub_element ).append('<option value="">- Select Resource -</option>');

                var product_id;
                var product_title;
                var product_id;
                var product_title;
                $.each(data, function (m, items) {
                $.each(items, function (n, item) {
                    //alert(item);
                    if (n % 2 == 0) {
                        product_id = item;
                    }
                    if (Math.abs(n % 2) == 1) {
                        product_title = item;
                        $( '.opt_tipo', sub_element ).append('<option value="' + product_id + '">' + product_title + '</option>');
                    }
                    //$( '.opt_tipo', sub_element ).append('<option value="' + item + '">' + item + '</option>');
                });
                });
            },
            error: function(error){
                alert(error);
            }
        });									
        if (this.value=='_delete_assignment') {
            $( this ).closest( sub_element ).remove();
        }							
    });


/*
    $('#qrcode').qrcode({
        text: $("#qrcode_content").text()
    });
*/
/* jQuery UI Dialog - Basic dialog */
/*
    $( "#dialog" ).dialog();
    $( "#dialog-form" ).dialog();
*/
/* jQuery UI Dialog - Modal form */

    // From http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#e-mail-state-%28type=email%29
    var dialog, form,  
    emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,
    name = $( "#name" ),
    email = $( "#email" ),
    password = $( "#password" ),
    allFields = $( [] ).add( name ).add( email ).add( password ),
    tips = $( ".validateTips" );

    //$( "#dialog-form" ).dialog();
    //dialog = $( "#dialog-form" ).dialog({
    $( "#dialog-form" ).dialog({
        //autoOpen: false,
        autoOpen: true,
        height: 400,
        width: 350,
        modal: true,
        buttons: {
            "Create": addUser,
            Cancel: function() {
                dialog.dialog( "close" );
            }
        },
        close: function() {
            form[ 0 ].reset();
            allFields.removeClass( "ui-state-error" );
        }
    });

    //$( "#dialog-form" ).dialog( "open" );

    form = dialog.find( "form" ).on( "submit", function( event ) {
        event.preventDefault();
        addUser();
    });

    function updateTips( t ) {
        tips
            .text( t )
            .addClass( "ui-state-highlight" );
        setTimeout(function() {
            tips.removeClass( "ui-state-highlight", 1500 );
        }, 500 );
    }

    function checkLength( o, n, min, max ) {
        if ( o.val().length > max || o.val().length < min ) {
            o.addClass( "ui-state-error" );
            updateTips( "Length of " + n + " must be between " +
                min + " and " + max + "." );
            return false;
        } else {
            return true;
        }
    }

    function checkRegexp( o, regexp, n ) {
        if ( !( regexp.test( o.val() ) ) ) {
            o.addClass( "ui-state-error" );
            updateTips( n );
            return false;
        } else {
            return true;
        }
    }

    function addUser() {
        var valid = true;
        allFields.removeClass( "ui-state-error" );

        valid = valid && checkLength( name, "username", 3, 16 );
        valid = valid && checkLength( email, "email", 6, 80 );
        valid = valid && checkLength( password, "password", 5, 16 );

        valid = valid && checkRegexp( name, /^[a-z]([0-9a-z_\s])+$/i, "Username may consist of a-z, 0-9, underscores, spaces and must begin with a letter." );
        valid = valid && checkRegexp( email, emailRegex, "eg. ui@jquery.com" );
        valid = valid && checkRegexp( password, /^([0-9a-zA-Z])+$/, "Password field only allow : a-z 0-9" );

        if ( valid ) {
            $( "#users tbody" ).append( "<tr>" +
                "<td>" + name.val() + "</td>" +
                "<td>" + email.val() + "</td>" +
                "<td>" + password.val() + "</td>" +
            "</tr>" );
            dialog.dialog( "close" );
        }
        return valid;
    }

    $( "#create-model" ).button().on( "click", function() {
        dialog.dialog( "open" );
    });


});