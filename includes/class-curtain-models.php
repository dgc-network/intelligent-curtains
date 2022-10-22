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
            //add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 20 );
            self::create_tables();
        }

        function enqueue_scripts() {
            wp_enqueue_script( 'custom-js', plugin_dir_url( __DIR__ ). 'assets/js/custom-options-view.js', array( 'jquery' ), time() );
            wp_enqueue_script( 'qrcode-js', plugin_dir_url( __DIR__ ). 'assets/js/jquery.qrcode.min.js', array( 'jquery' ), time() );
            //wp_enqueue_script( 'popup-js',  'popupwindow.min.js', array( 'jquery' ), time() );
            wp_enqueue_script( 'chat-js',  plugin_dir_url( __DIR__ ). 'chat/js/chat.js', array( 'jquery' ), time() );
            //wp_enqueue_script( 'jquery-js',  plugins_url( '/chat/js/jquery.js' , __FILE__ ), array(), time() );
            
            wp_enqueue_style( 'custom-css', plugin_dir_url( __DIR__ ). 'assets/css/custom-options-view.css', '', time() );
            //wp_enqueue_style( 'popup-css', 'popupwindow.min.css', '', time() );
            wp_enqueue_style( 'chat-css', plugin_dir_url( __DIR__ ). 'chat/css/chat.css', '', time() );
            wp_enqueue_style( 'screen-css', plugin_dir_url( __DIR__ ). 'chat/css/screen.css', '', time() );
            //wp_head();
        }

        function list_curtain_models() {
?>
            <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
            <link rel="stylesheet" href="/resources/demos/style.css">
            <style>
                
/*            
            label, input { display:block; }
            input.text { margin-bottom:12px; width:95%; padding: .4em; }
            fieldset { padding:0; border:0; margin-top:25px; }
            h1 { font-size: 1.2em; margin: .6em 0; }
            div#users-contain { width: 350px; margin: 20px 0; }
            div#users-contain table { margin: 1em 0; border-collapse: collapse; width: 100%; }
            div#users-contain table td, 
            div#users-contain table th { border: 1px solid #eee; padding: .6em 10px; text-align: left; }
            .ui-dialog .ui-state-error { padding: .3em; }
            .validateTips { border: 1px solid transparent; padding: 0.3em; }
*/            
            </style>
            <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
            <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
            <script>
            $( function() {
            } );
            </script>


            <div id="dialog-form" title="Create new user">
            <p class="validateTips">All form fields are required.</p>
           
            <form>
              <fieldset>
                <label for="name">Name</label>
                <input type="text" name="name" id="name" value="Jane Smith" class="text ui-widget-content ui-corner-all">
                <label for="email">Email</label>
                <input type="text" name="email" id="email" value="jane@smith.com" class="text ui-widget-content ui-corner-all">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" value="xxxxxxx" class="text ui-widget-content ui-corner-all">
           
                <!-- Allow form submission with keyboard without duplicating the dialog button -->
                <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
              </fieldset>
            </form>
          </div>
<?php          
          if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                return self::edit_curtain_model($_POST['_id'], $_POST['_mode']);
            }

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
?>
           
           
           <div id="users-contain" class="ui-widget">
            <h1>Existing Users:</h1>
            <table id="users" class="ui-widget ui-widget-content">
              <thead>
                <tr class="ui-widget-header ">
                  <th>Name</th>
                  <th>Email</th>
                  <th>Password</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>John Doe</td>
                  <td>john.doe@example.com</td>
                  <td>johndoe1</td>
                </tr>
              </tbody>
            </table>
          </div>
          <button id="create-user">Create new user</button>

<?php            
            $output  = '<h2>Model Number</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td colspan=5 style="text-align:right">';
            $output .= '<form method="post">';
            $output .= '<input type="text" name="_where_curtain_model" placeholder="Search...">';
            $output .= '<input type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</td></tr>';
            $output .= '<tr style="background-color:yellow">';
            $output .= '<td>id</td>';
            $output .= '<td>model</td>';
            $output .= '<td>description</td>';
            $output .= '<td>vendor</td>';
            $output .= '<td>update_time</td>';
            $output .= '</tr>';
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
            $output .= '</tbody></table></figure>';
            $output .= '<form method="post">';
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_mode">';
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            //$output .= '<a class="wp-block-button__link" href="/">Cancel</a>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';

            if( isset($_POST['_serial_no']) ) {
                $output .= '<div id="basic-demo" class="example_content"><div id="qrcode"><div id="qrcode_content">';
                //$output .= get_site_url().'/service/?serial_no='.$_POST['_serial_no'].'</div></div></div>';
                $output .= get_site_url().'/'.get_option('_service_page').'/?serial_no='.$_POST['_serial_no'].'</div></div></div>';
            }
                            
            return $output;
        }

        function edit_curtain_model( $_id=null, $_mode=null ) {

            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id={$_id}", OBJECT );
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
                    //$spec = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}specifications WHERE specification_id = {$result->specification_id}", OBJECT );
                    //$output .= '<td>'.$spec->specification.'</td>';
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