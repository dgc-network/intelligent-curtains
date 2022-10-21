jQuery(document).ready(function($) {
    /*
     * Woocommerce Product Data Metabox Options
     */
    $( 'input#_trip_options' ).change( function() {
        var is_trip_options = $( 'input#_trip_options:checked' ).length;
        $( '.show_if_trip_options' ).hide();
        $( '.hide_if_trip_options' ).hide();

        if ( is_trip_options ) {
            $( '.hide_if_trip_options' ).hide();
        }
        if ( is_trip_options ) {
            $( '.show_if_trip_options' ).show();
        }
    });
    $( 'input#_trip_options' ).trigger( 'change' );

    /*
     * TABs Tab
     */
    $( "#tabs-ul" ).sortable();
    $( "#tabs-ul" ).disableSelection();
    $( ".tab-li" ).hide();

    $( ".tab-li" ).each( function( index, element ) {
        if ( !$( 'p', element ).is(":empty") ) {
            $( ".itinerary-rows" ).show();
            $( element ).show();
            $( element ).delegate("span", "click", function(){
                $( 'table', element ).toggleClass('toggle-access');
            });
        };

        $( element ).delegate(".item_title", "keyup", function(){
            $( 'span', element ).text($(this).val());
        });
    });

    /*
     * FAQs Tab
     */
    var faq_x = 0;
    $( ".faq-li" ).each( function( index, element ) {

        $( element ).delegate("span", "click", function(){
            $( 'table', element ).toggleClass('toggle-access');
        });

        $( element ).delegate(".item_title", "keyup", function(){
            $( '.faq-title', element ).text($(this).val());
        });

        $( '.remove-faq', element ).on( 'click', function() {
            if (confirm('Are you sure?') == true) {
                $( this ).closest('.faq-li').remove();
            };
        });
        faq_x += 1;
    });

    $( '#first-faq' ).on( 'click', function() {
        $(".no-faqs").hide();
        $(".faq-header").show();
        $(".faq-rows").show();
    });

    $( '.add-faq' ).on( 'click', function() {

        var default_faq = 'FAQ Questions';
        var new_faq = '<li class="faq-li" id="faq-li-' + faq_x + '">';
        new_faq += '<span class="fas fa-bars"> </span>';
        new_faq += '<span class="faq-title">'+ default_faq +'</span>';
        new_faq += '<table>';
        new_faq += '<tr>';
        new_faq += '<th>Your question</th>';
        new_faq += '<td><input type="text" width="100%" class="item_title" value="'+ default_faq +'" name="faq_item_question-' + faq_x + '"></td>';
        new_faq += '</tr>';
        new_faq += '<tr>';
        new_faq += '<th>Your answer</th>';
        new_faq += '<td><textarea rows="5" name="faq_item_answer-' + faq_x + '"></textarea></td>';
        new_faq += '</tr>';
        new_faq += '<tr>';
        new_faq += '<td colspan="2"><button class="remove-faq" type="button">- Remove Faq -</button></td>';
        new_faq += '</tr>';
        new_faq += '</table>';
        new_faq += '</li>';

        $( '#end-of-faq' ).before(new_faq);
        var element = '#faq-li-' + faq_x ;
        $( 'span', element ).on( 'click', function() {
            $( 'table', element ).toggleClass( 'toggle-access' );
        });
        $( element ).delegate( '.item_title', 'keyup', function() {
            $( '.faq-title', element ).text($(this).val());
        });
        $( '.remove-faq', element ).on( 'click', function() {
            if (confirm('Are you sure?') == true) {
                $( this ).closest('.faq-li').remove();
            };
        });
        faq_x += 1;
    });


    /*
     * Itinerary Tab
     */
        // alerts 'Some string to translate'
        //alert( object_name.remove_trip_options );

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

    var x = 0;
    $( '.itinerary-li' ).each( function( index, element ) {

        $( element ).delegate( 'span', 'click', function() {
            $( 'table', element ).toggleClass( 'toggle-access' );
        });

        $( element ).delegate( '.item_label', 'keyup', function() {
            $( '.span-label', element ).text($(this).val());
        });

        $( element ).delegate( '.item_title', 'keyup', function() {
            $( '.span-title', element ).text($(this).val());
        });

        $( '.remove-itinerary', element ).on( 'click', function() {
            if (confirm('Are you sure?') == true) {
                $( this ).closest('.itinerary-li').remove();
            };
        });

        $( '.item_date', element ).datepicker();

        var y = 0;
        $( '.assignment-rows', element ).each( function( sub_index, sub_element ) {
            $( '.opt-categorias', sub_element ).on( 'change', function() {
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
                        $( '.opt_tipo', sub_element ).append("<option value=''>- Select Resource -</option>");

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
            y = y + 1;
        });

        $( element ).delegate( '#first-assignment', 'click', function() {
            $( '.no-assignments', element ).hide();
            $( '.assignment-header', element ).show();
        });

        $( element ).delegate( '.add-assignment', 'click', function() {

            var new_assignment = '<tr class="assignment-rows" id="assignment-row-'+ index +'-'+ y +'"><td>';
            new_assignment += '<select style="width:100%" class="opt_categorias" name="itinerary_item_assignment-'+ index +'-category-'+ y +'">';
            new_assignment += '<option>- Select Category -</option>';
            $.each(categories, function (i, item) {
                new_assignment += '<option value="' + item + '">' + item + '</option>';
            });
            new_assignment += '<option style="color:red" value="_delete_assignment">- Remove Assignment -</option>';
            new_assignment += '</select></td><td>';
            new_assignment += '<select style="width:100%" class="opt_tipo" name="itinerary_item_assignment-'+ index +'-resource-'+ y +'"></select>';
            new_assignment += '</td></tr>';

            $( '#end-of-assignment', element ).before(new_assignment);
            var sub_element = '#assignment-row-' + index +'-'+ y;
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
            y = y + 1;
        });
        x = x + 1;
    });

    $( '#first-itinerary' ).on( 'click', function() {
        $( '.no-itineraries' ).hide();
        $( '.itinerary-header' ).show();
        $( '.itinerary-rows' ).show();					
    });

    $( '.add-itinerary' ).on( 'click', function() {
        //var itinerary_label = DEFAULT_ITINERARY_LABEL;
        //var itinerary_title = DEFAULT_ITINERARY_TITLE;
        var itinerary_label = 'Day X';
        var itinerary_title = 'My Plan';
        var new_itinerary = '<li class="itinerary-li" id="itinerary-li-' + x + '">';
        new_itinerary += '<span class="fas fa-bars"> </span>';
        new_itinerary += '<span class="span-label">' + itinerary_label + '</span>, ';
        new_itinerary += '<span class="span-title">' + itinerary_title + '</span>';
        new_itinerary += '<table>';
        new_itinerary += '<tr>';
        new_itinerary +=  '<th>Itinerary label</th>';
        new_itinerary +=  '<td><input type="text" class="item_label" name="itinerary_item_label-' + x + '" value="' + itinerary_label + '"></td>';
        new_itinerary += '</tr>';
        new_itinerary += '<tr>';
        new_itinerary +=  '<th>Itinerary title</th>';
        new_itinerary +=  '<td><input type="text" class="item_title" name="itinerary_item_title-' + x + '" value="' + itinerary_title + '"></td>';
        new_itinerary += '</tr>';
        new_itinerary += '<tr>';
        new_itinerary +=  '<th>Itinerary date</th>';
        new_itinerary +=  '<td><input type="text" class="item_date" name="itinerary_item_date-' + x + '"></td>';
        new_itinerary += '</tr>';
        new_itinerary += '<tr>';
        new_itinerary +=  '<td colspan="2"><b>Description</b><br>';
        new_itinerary +=  '<textarea rows="5" name="itinerary_item_desc-' + x + '"></textarea></td>';
        new_itinerary += '</tr>';
        new_itinerary += '<tr>';
        new_itinerary +=  '<td colspan="2">';
        new_itinerary +=  '<table style="width:100%;margin-left:0">';
        new_itinerary +=  '<tr style="display:none" class="assignment-header">';
        new_itinerary +=   '<th class="assignment-row-head">Resources Assignment</th>';
        new_itinerary +=   '<td style="text-align:right"><button class="add-assignment" type="button">+ Add Assignment</button></td>';
        new_itinerary +=  '</tr>';
        new_itinerary +=  '<tr class="no-assignments">';
        new_itinerary +=   '<td colspan="2">No Assignments found. ';
        new_itinerary +=   '<button class="add-assignment" id="first-assignment" type="button">+ Add Assignment</button></td>';
        new_itinerary +=  '</tr>';
        new_itinerary +=  '<tr id="end-of-assignment"></tr>';
        new_itinerary +=  '</table>';
        new_itinerary += '</tr>';
        new_itinerary += '<tr>';
        new_itinerary += '<td colspan ="2"><button class="remove-itinerary" type="button">- Remove Itinerary -</button></td>';
        new_itinerary += '</tr>';
        new_itinerary += '</table>';

        $( '#end-of-itinerary' ).before(new_itinerary);
        var element = '#itinerary-li-' + x ;
        $( 'span', element ).on( 'click', function() {
            $( 'table', element ).toggleClass( 'toggle-access' );
        });
        $( element ).delegate( '.item_label', 'keyup', function() {
            $( '.span-label', element ).text($(this).val());
        });
        $( element ).delegate( '.item_title', 'keyup', function() {
            $( '.span-title', element ).text($(this).val());
        });
        $( '.remove-itinerary', element ).on( 'click', function() {
            if (confirm('Are you sure?') == true) {
                $( this ).closest('.itinerary-li').remove();
            };
        });
        $( '.item_date', element ).datepicker();

        $( element ).delegate( '#first-assignment', 'click', function() {
            $( '.no-assignments', element ).hide();
            $( '.assignment-header', element ).show();
        });
        var y = 0;
        $( element ).delegate( '.add-assignment', 'click', function() {
            var new_assignment = '<tr class="assignment-rows" id="assignment-row-'+ x +'-'+ y +'"><td>';
            new_assignment += '<select style="width:100%" class="opt_categorias" name="itinerary_item_assignment-'+ x +'-category-'+ y +'">';
            new_assignment += '<option>- Select Category -</option>';
            $.each(categories, function (i, item) {
                new_assignment += '<option value="' + item + '">' + item + '</option>';
            });
            new_assignment += '<option style="color:red" value="_delete_assignment">- Remove Assignment -</option>';
            new_assignment += '</select></td><td>';
            new_assignment += '<select style="width:100%" class="opt_tipo" name="itinerary_item_assignment-'+ x +'-resource-'+ y +'"></select>';
            new_assignment += '</td></tr>';

            $( '#end-of-assignment', element ).before(new_assignment);
            var sub_element = '#assignment-row-' + x +'-'+ y;
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
            y = y + 1;
        });
        x = x + 1;
    });
});