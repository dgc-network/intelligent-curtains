jQuery(document).ready(function($) {

// open
$('#basic-demo').PopupWindow("open")

    $("#basic-demo").PopupWindow({
        // options here
      // popup title
  title               : "Popup Window",
 
  // modal mode
  modal               : true,
 
  // auto open on page load
  autoOpen            : true,
 
  // anmation speed
  animationTime       : 300,
 
  // custom css classes
  customClass         : "",
   
  // custom action buttons
  buttons             : {
      close               : true,
      maximize            : true,
      collapse            : true,
      minimize            : true
  },
 
  // button's position
  buttonsPosition     : "right",
 
  // custom button text
  buttonsTexts        : {
    close               : "Close",
    maximize            : "Maximize",
    unmaximize          : "Restore",
    minimize            : "Minimize",
    unminimize          : "Show",
    collapse            : "Collapse",
    uncollapse          : "Expand"
  }, 
   
  // draggable options
  draggable           : true,
  nativeDrag          : true,
  dragOpacity         : 0.6,
   
  // resizable options
  resizable           : true,
  resizeOpacity       : 0.6,
   
  // enable status bar
  statusBar           : true,
   
  // top position
  top                 : "auto",
 
  // left position
  left                : "auto",
   
 
  // height / width
  height              : 200,
  width               : 400,
  maxHeight           : undefined,
  maxWidth            : undefined,
  minHeight           : 100,
  minWidth            : 200,
  collapsedWidth      : undefined,
   
  // always keep in viewport
  keepInViewport      : true,
 
  // enable mouseh move events
  mouseMoveEvents     : true
    
    });   

    $('#qrcode').qrcode({
        text: "https://www.htmleaf.com"
    });

    /*
     * AJAX for Woocommerce Add To Cart button
     */
    $( '.single_add_to_cart_button' ).on( 'click', function(e) {
        e.preventDefault();
/*
        var myInput = document.getElementById("start_date_input");
        if !(myInput && myInput.value) {
              alert("My input has no value!");
              return;
        }
*/					
        var $thisbutton = $(this),
        $form = $thisbutton.closest('form.cart'),
        id = $thisbutton.val(),
        product_qty = $form.find('input[name=quantity]').val() || 1,
        product_id = $form.find('input[name=product_id]').val() || id,
        variation_id = $form.find('input[name=variation_id]').val() || 0;

        var itinerary_date_array = [];
        $( '.itinerary-li' ).each( function( index, element ) {
            var itinerary_date = $( '#itinerary-date-'+index ).val();
            itinerary_date_array.push( itinerary_date );
        })
        var start_date_input = $( '#from' ).val();
        var end_date_input = $( '#to' ).val();

        var data = {
            action: 'woocommerce_ajax_add_to_cart',
            product_id: product_id,
            product_sku: '',
            quantity: product_qty,
            variation_id: variation_id,
            itinerary_date_array: itinerary_date_array,
            start_date_input: start_date_input,
            end_date_input: end_date_input,
        };

        $(document.body).trigger('adding_to_cart', [$thisbutton, data]);

        $.ajax({
            type: 'post',
            url: '/wp-admin/admin-ajax.php',
            data: data,
            beforeSend: function (response) {
                $thisbutton.removeClass('added').addClass('loading');
            },
            complete: function (response) {
                $thisbutton.addClass('added').removeClass('loading');
            },
            success: function (response) {
                if (response.error && response.product_url) {
                    window.location = response.product_url;
                    return;
                } else {
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
                }
            },
        });

        return false;
    });

    /*
     * Update the Itinerary Date after the Datepicker value changed
     */
    $( '.start_date' ).datepicker();
    $( '.start_date' ).on( 'change', function() {
        var start_date = new Date(this.value);
        var updated_start_date = new Date(this.value);
        //$( '#start_date_input' ).val(updated_start_date.toLocaleDateString());
        $( '.itinerary-li' ).each( function( index, element ) {
            updated_start_date.setDate(start_date.getDate() + index);
            //$( 'input', element ).val(updated_start_date.toLocaleDateString());
            $( 'input', element ).val(updated_start_date.toString());
            $( '#itinerary-date-'+index ).datepicker();
            $( '#itinerary-date-'+index ).on( 'change', function() {
                var trip_date = new Date(this.value);
                var updated_trip_date = new Date(this.value);
                $( '.itinerary-li' ).each( function( index2, element2 ) {
                    if (index2 > index) {
                        updated_trip_date.setDate(trip_date.getDate() + index2 - index);
                        //$( 'input', element2 ).val(updated_trip_date.toLocaleDateString());
                        $( 'input', element2 ).val(updated_trip_date.toString());
                    }
                });
            });
        });
    });

    var from = $( "#from" )
    .datepicker({
          defaultDate: "+1w",
          changeMonth: true,
    })
    .on( "change", function() {
          to.datepicker( "option", "minDate", getDateElement( this ) );
    });

    var to = $( "#to" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
    })
    .on( "change", function() {
        from.datepicker( "option", "maxDate", getDateElement( this ) );
    });

    function getDateElement( element ) {
        var date;
        try {
            date = $.datepicker.parseDate( dateFormat, element.value );
        } catch( error ) {
            date = null;
        }
        return date;
    }


});