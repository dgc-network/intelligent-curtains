<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('curtain_models')) {

    class curtain_models {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('curtain-model-list', __CLASS__ . '::list_curtain_models');
            add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
            add_action( 'wp_ajax_insert_model', array( __CLASS__, 'ajax_insert_model' ) );
            add_action( 'wp_ajax_nopriv_insert_model', array( __CLASS__, 'ajax_insert_model' ) );
            add_action( 'wp_ajax_update_model', array( __CLASS__, 'ajax_update_model' ) );
            add_action( 'wp_ajax_nopriv_update_model', array( __CLASS__, 'ajax_update_model' ) );
            add_action( 'wp_ajax_delete_model', array( __CLASS__, 'ajax_delete_model' ) );
            add_action( 'wp_ajax_nopriv_delete_model', array( __CLASS__, 'ajax_delete_model' ) );
            self::create_tables();
        }

        function enqueue_scripts() {		
            wp_enqueue_script( 'custom-curtain-models', plugin_dir_url( __DIR__ ) . 'assets/js/custom-curtain-models.js', array( 'jquery' ), time(), true );
            //wp_register_script( 'jquery-ui-js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js' );
            //wp_register_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css' );
            //wp_register_style( 'demos-style-css', 'https://jqueryui.com/resources/demos/style.css' );
            //wp_enqueue_script( 'jquery-ui-js' );
            //wp_enqueue_style( 'jquery-ui-css' );  
            //wp_enqueue_style( 'demos-style-css' );  
        }    

        function ajax_insert_model() {
            $args = array(
                'taxonomy'   => "product_cat",
                'number'     => $number,
                'orderby'    => $orderby,
                'order'      => $order,
                'hide_empty' => $hide_empty,
                'include'    => $ids
            );
            $product_categories = get_terms($args);
    
            $titles = array();
            foreach( $product_categories as $cat ) {
                if ($cat->name != 'Uncategorized') {
                    array_push($titles, $cat->name);
                }
            }
            $json = json_encode( $titles );
            echo $json;
            
            die();
        }
            
        function ajax_update_model() {

            $product_category_slug = ( isset($_POST['term_chosen']) && !empty( $_POST['term_chosen']) ? $_POST['term_chosen'] : false );
            
            $query = new WC_Product_Query( array(
                'category' => array( $product_category_slug ),
                'limit' => 10,
                'orderby' => 'date',
                'order' => 'DESC'
            ) );
            
            $products = $query->get_products();
            
            $titles = array();
            foreach( $products as $product ) {
                $title = array();
                array_push($title, $product->get_id());
                array_push($title, $product->get_title());
                array_push($titles, $title);
            }	
            $json = json_encode( $titles );
            echo $json;
            
            die();		
        }
            
        function list_curtain_models() {
?>

            <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
            <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>


<?php          

?>
            <script>
            $( function() {

                var dialog, form,  
    emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,
    name = $( "#name" ),
    email = $( "#email" ),
    password = $( "#password" ),
    allFields = $( [] ).add( name ).add( email ).add( password ),
    tips = $( ".validateTips" );

    dialog = $( "#dialog-form" ).dialog({
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

    $( "#create-model" ).button().on( "click", function() {
        dialog.dialog( "open" );
    });


            } );
            </script>
<?php            

/*
            if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                return self::edit_curtain_model($_POST['_id'], $_POST['_mode']);
            }
*/
            if( ($_GET['action']=='insert-curtain-model') && (isset($_GET['curtain_model_name'])) ) {
                $data=array();
                $data['curtain_model_name']=$_GET['curtain_model_name'];
                $data['model_description']=$_GET['description'];
                $data['vendor_name']=$_GET['vendor_name'];
                $result = self::insert_curtain_model($data);
                $output .= $result.'<br>';
            }
            
            if( ($_GET['action']=='update-curtain-model') && (isset($_GET['curtain_model_id'])) ) {
                $data=array();
                if( isset($_GET['curtain_model_name']) ) {
                    $data['curtain_model_name']=$_GET['curtain_model_name'];
                }
                if( isset($_GET['model_description']) ) {
                    $data['model_description']=$_GET['description'];
                }
                if( isset($_GET['vendor_name']) ) {
                    $data['vendor_name']=$_GET['vendor_name'];
                }
                $where=array();
                $where['curtain_model_id']=$_GET['curtain_model_id'];
                $result = self::update_curtain_products($data, $where);
                $output .= $result.'<br>';
            }
            
            if( isset($_POST['create_curtain_model']) ) {
                $data=array();
                $data['curtain_model_name']=$_POST['_curtain_model_name'];
                $data['model_description']=$_POST['_model_description'];
                $data['vendor_name']=$_POST['_vendor_name'];
                $result = self::insert_curtain_model($data);
            }
            
            if( isset($_POST['update_curtain_model']) ) {
                $data=array();
                $data['curtain_model_name']=$_POST['_curtain_model_name'];
                $data['model_description']=$_POST['_model_description'];
                $data['vendor_name']=$_POST['_vendor_name'];
                $where=array();
                $where['curtain_model_id']=$_POST['_curtain_model_id'];
                $result = self::update_curtain_model($data, $where);
            }

            global $wpdb;
            if( isset($_POST['_where_curtain_model']) ) {
                $where='"%'.$_POST['_where_curtain_model'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE model_description LIKE {$where}", OBJECT );
                unset($_POST['_where_curtain_model']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models", OBJECT );
            }
            $output  = '<h2>Model Number</h2>';
            $output .= '<span style="text-align:right">';
            $output .= '<form method="post">';
            $output .= '<input type="text" name="_where_curtain_model" placeholder="Search...">';
            $output .= '<input type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</span>';
            $output .= '<div id="users-contain" class="ui-widget">';
            $output .= '<table id="users" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th>id</td>';
            $output .= '<th>model</td>';
            $output .= '<th>description</td>';
            $output .= '<th>vendor</td>';
            $output .= '<th>update_time</td>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->curtain_model_id.'</a></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->curtain_model_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->curtain_model_name.'" name="_mode">';
                $output .= '</form></td>';
                $output .= '<td>'.$result->model_description.'</td>';
                $output .= '<td>'.$result->vendor_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';
            //$output .= '<button id="create-model">Create new model</button>';

            $output .= '<form method="post">';
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input id="create-model" class="wp-block-button__link" type="submit" value="Create" name="_mode">';
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            //$output .= '<a class="wp-block-button__link" href="/">Cancel</a>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';


            if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                $_id = $_POST['_id'];
                global $wpdb;
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id={$_id}", OBJECT );
                if (count($row) > 0) {
                    $output .= '<div id="dialog-form" title="Model Number update">';
                    $output .= '<form><fieldset>';
                    $output .= '<input type="hidden" value="'.$row->curtain_model_id.'" name="_curtain_model_id">';
                    $output .= '<label for="name">Model Name</label>';
                    $output .= '<input type="text" name="_curtain_model_name" id="name" class="text ui-widget-content ui-corner-all" value="'.$row->curtain_model_name.'">';
                    $output .= '<label for="name">Description</label>';
                    $output .= '<input type="text" name="_model_description" id="name" class="text ui-widget-content ui-corner-all" value="'.$row->model_description.'">';
                    $output .= '<label for="name">Curtain Vendor</label>';
                    $output .= '<input type="text" name="_vendor_name" id="name" class="text ui-widget-content ui-corner-all" value="'.$row->vendor_name.'">';
                    $output .= '<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">';
                    $output .= '</fieldset></form>';
                    $output .= '</div>';
                } else {
                    $output .= '<div id="dialog-form" title="Create new model">';
                    $output .= '<form><fieldset>';
                    $output .= '<label for="name">Model Name</label>';
                    $output .= '<input type="text" name="_curtain_model_name" id="name" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="name">Description</label>';
                    $output .= '<input type="text" name="_model_description" id="name" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="name">Curtain Vendor</label>';
                    $output .= '<input type="text" name="_vendor_name" id="name" class="text ui-widget-content ui-corner-all">';
                    $output .= '<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">';
                    $output .= '</fieldset></form>';
                    $output .= '</div>';
                }
            }
         
            if( isset($_POST['_serial_no']) ) {
                $output .= '<div id="dialog" title="QR Code">';
                $output .= '<div id="qrcode"><div id="qrcode_content">';
                $output .= get_site_url().'/'.get_option('_service_page').'/?serial_no='.$_POST['_serial_no'];
                $output .= '</div></div>';
                $output .= '</div>';
            }
                            
            return $output;
        }

        function edit_curtain_model( $_id=null, $_mode=null ) {

            if( $_mode=='Create' ) {
                $output  = '<h2>New Model Name</h2>';
            } else {
                $output  = '<h2>Model Name Update</h2>';
            }
            $output .= '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            if( $_mode=='Create' ) {
                $output .= '<tr><td>'.'Model Name:'.'</td><td><input size="50" type="text" name="_curtain_model_name"></td></tr>';
                $output .= '<tr><td>'.'Description :'.'</td><td><input size="50" type="text" name="_model_description"></td></tr>';
                $output .= '<tr><td>'.'Vendor      :'.'</td><td><input size="50" type="text" name="_vendor_name"></td></tr>';            
            } else {
                $output .= '<input type="hidden" value="'.$row->curtain_model_id.'" name="_curtain_model_id">';
                $output .= '<tr><td>'.'Model Name:'.'</td><td><input size="50" type="text" name="_curtain_model_name" value="'.$row->curtain_model_name.'"></td></tr>';
                $output .= '<tr><td>'.'Description :'.'</td><td><input size="50" type="text" name="_model_description" value="'.$row->model_description.'"></td></tr>';
                $output .= '<tr><td>'.'Vendor      :'.'</td><td><input size="50" type="text" name="_vendor_name" value="'.$row->vendor_name.'"></td></tr>';
            }   
            $output .= '</tbody></table></figure>';

            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            if( $_mode=='Create' ) {
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="create_curtain_model">';
            } else {
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="update_curtain_model">';
            }
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';
        
            if( !($_mode=='Create') ) {
                $where='curtain_model_id='.$row->curtain_model_id;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE {$where}", OBJECT );
                $output .= '<figure class="wp-block-table"><table><tbody>';
                $output .= '<tr style="background-color:yellow">';
                $output .= '<td> </td>';
                $output .= '<td>serial_no</td>';
                $output .= '<td>model</td>';
                $output .= '<td>spec</td>';
                $output .= '<td>user</td>';
                $output .= '<td>update_time</td>';
                $output .= '</tr>';
                foreach ( $results as $index=>$result ) {
                    $output .= '<tr>';
                    $output .= '<td></td>';
                    $output .= '<td><form method="post">';
                    $output .= '<input type="submit" value="'.$result->qr_code_serial_no.'" name="_serial_no">';
                    $output .= '</form></td>';
                    $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$result->curtain_model_id}", OBJECT );
                    $output .= '<td>'.$model->curtain_model_name.'</td>';
                    $output .= '<td>'.$result->specification.'</td>';
                    $user = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = {$result->curtain_user_id}", OBJECT );
                    $output .= '<td>'.$user->display_name.'</td>';
                    $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                    $output .= '</tr>';
                }
                $output .= '</tbody></table></figure>';
            }

            return $output;
        }

        function insert_curtain_model($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_models';
            $data = array(
                'curtain_model_name' => $data['curtain_model_name'],
                'model_description' => $data['model_description'],
                'vendor_name' => $data['vendor_name'],
                'create_timestamp' => time(),
                'update_timestamp' => time(),
            );
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        function update_curtain_model($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_models';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        function select_options( $default_id=null ) {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models", OBJECT );
            $output = '<option value="no_select">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $results[$index]->curtain_model_id == $default_id ) {
                    $output .= '<option value="'.$results[$index]->curtain_model_id.'" selected>';
                } else {
                    $output .= '<option value="'.$results[$index]->curtain_model_id.'">';
                }
                $output .= $results[$index]->curtain_model_name;
                $output .= '</option>';        
            }
            $output .= '<option value="delete_select">-- Remove this --</option>';
            return $output;    
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_models` (
                curtain_model_id int NOT NULL AUTO_INCREMENT,
                curtain_model_name varchar(5),
                model_description varchar(50),
                vendor_name varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                UNIQUE (curtain_model_name),
                PRIMARY KEY (curtain_model_id)
            ) $charset_collate;";
            dbDelta($sql);            
        }
    }
    new curtain_models();
}