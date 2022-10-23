jQuery(document).ready(function($) {
/*    
    // open
    $('#basic-demo').PopupWindow("open")

    $("#basic-demo").PopupWindow({
        // options here
        // popup title
        title               : "QR Code",
 
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
            maximize            : false,
            collapse            : false,
            minimize            : false
        },
 
        // button's position
        buttonsPosition     : "left",
 
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
        resizable           : false,
        resizeOpacity       : 0.6,
   
        // enable status bar
        statusBar           : false,
   
        // top position
        top                 : "auto",
 
        // left position
        left                : "auto",
   
 
        // height / width
        height              : 320,
        width               : 300,
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
*/
    $('#qrcode').qrcode({
        text: $("#qrcode_content").text()
    });

/* jQuery UI Dialog - Basic dialog */

    $( "#dialog" ).dialog();

/* jQuery UI Dialog - Modal form */

    // From http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#e-mail-state-%28type=email%29

});