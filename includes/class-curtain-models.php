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
            wp_enqueue_script( 'jquery-ui-js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array( 'jquery' ), time(), true  );
            wp_enqueue_script( 'jquery-ui-dialog' );
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

            if( isset($_POST['_create']) ) {
                $data=array();
                $data['curtain_model_name']=$_POST['_curtain_model_name'];
                $data['model_description']=$_POST['_model_description'];
                $data['curtain_vendor_name']=$_POST['_curtain_vendor_name'];
                $result = self::insert_curtain_model($data);
            }
            
            if( isset($_POST['_update']) ) {
                $data=array();
                $data['curtain_model_name']=$_POST['_curtain_model_name'];
                $data['model_description']=$_POST['_model_description'];
                $data['curtain_vendor_name']=$_POST['_curtain_vendor_name'];
                $where=array();
                $where['curtain_model_id']=$_POST['_curtain_model_id'];
                $result = self::update_curtain_models($data, $where);
            }

            global $wpdb;
            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE model_description LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models", OBJECT );
            }
            $output  = '<h2>Curtain Models</h2>';
            $output .= '<div style="text-align: right">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input style="display:inline" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div class="ui-widget">';
            $output .= '<table id="users" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th>id</th>';
            $output .= '<th>model</th>';
            $output .= '<th>description</th>';
            $output .= '<th>vendor</th>';
            $output .= '<th>update_time</th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->curtain_model_id.'</a></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->curtain_model_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->curtain_model_name.'">';
                $output .= '</form></td>';
                $output .= '<td>'.$result->model_description.'</td>';
                $output .= '<td>'.$result->curtain_vendor_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';
            $output .= '<form method="post">';
            $output .= '<input id="create-model" class="wp-block-button__link" type="submit" value="Create" name="_mode">';
            $output .= '</form>';

            if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                $_id = $_POST['_id'];
                global $wpdb;
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id={$_id}", OBJECT );
                if (count($row) > 0) {
                    $output .= '<div id="dialog" title="Curtain model update">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<input type="hidden" value="'.$row->curtain_model_id.'" name="_curtain_model_id">';
                    $output .= '<label for="_curtain_model_name">Model Name</label>';
                    $output .= '<input type="text" name="_curtain_model_name" id="curtain-model-name" class="text ui-widget-content ui-corner-all" value="'.$row->curtain_model_name.'">';
                    $output .= '<label for="_model_description">Description</label>';
                    $output .= '<input type="text" name="_model_description" id="model-description" class="text ui-widget-content ui-corner-all" value="'.$row->model_description.'">';
                    $output .= '<label for="_curtain_vendor_name">Curtain Vendor</label>';
                    $output .= '<input type="text" name="_curtain_vendor_name" id="vendor-name" class="text ui-widget-content ui-corner-all" value="'.$row->curtain_vendor_name.'">';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="_delete">';
                    $output .= '</form>';
                    $output .= '</div>';
                } else {
                    $output .= '<div id="dialog" title="Create new model">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<label for="_curtain_model_name">Model Name</label>';
                    $output .= '<input type="text" name="_curtain_model_name" id="curtain-model-name" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="_model_description">Description</label>';
                    $output .= '<input type="text" name="_model_description" id="model-description" class="text ui-widget-content ui-corner-all">';
                    $output .= '<label for="_curtain_vendor_name">Curtain Vendor</label>';
                    $output .= '<input type="text" name="_curtain_vendor_name" id="vendor-name" class="text ui-widget-content ui-corner-all">';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
                    $output .= '</form>';
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

        function insert_curtain_model($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_models';
            $data = array(
                'curtain_model_name' => $data['curtain_model_name'],
                'model_description' => $data['model_description'],
                'curtain_vendor_name' => $data['curtain_vendor_name'],
                'create_timestamp' => time(),
                'update_timestamp' => time(),
            );
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        function update_curtain_models($data=[], $where=[]) {
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
                curtain_vendor_name varchar(50),
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