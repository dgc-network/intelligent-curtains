<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('curtain_agents')) {

    class curtain_agents {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('curtain-agent-list', __CLASS__ . '::list_curtain_agents');
            self::create_tables();
        }

        function list_curtain_agents( $_curtain_user_id = 0 ) {

            if( $_curtain_user_id == 0 ) {
                $six_digit_random_number = random_int(100000, 999999);
                $output  = '請利用手機按 '.'<a href="https://line.me/ti/p/@490tjxdt">';
                $output .= '<img src="https://scdn.line-apps.com/n/line_add_friends/btn/zh-Hant.png" alt="加入好友" height="36" border="0"></a>';
                $output .= '<br>在我們的Line官方帳號聊天室中輸入六位數字密碼: <p style="color:blue">'.$six_digit_random_number.'</p>';
                $output .= '並按下我們提供的連結來繼續後續的作業<br>';
                return $output;
            }

            if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                return self::edit_curtain_agent($_POST['_id'], $_POST['_mode']);
            }

            if( isset($_POST['generate_serial_no']) ) {
                $curtain_service = new curtain_service();
                $data=array();
                $data['curtain_model_id']=$_POST['_curtain_model_id'];
                //$data['specification_id']=$_POST['_specification_id'];
                $data['specification']=$_POST['_specification'];
                $data['curtain_agent_id']=$_POST['_curtain_agent_id'];
                $result = $curtain_service->insert_serial_number($data);
            }
            
            if( isset($_POST['_create_agent']) ) {
                $data=array();
                $data['agent_number']=$_POST['_agent_number'];
                $data['agent_name']=$_POST['_agent_name'];
                $data['agent_address']=$_POST['_agent_address'];
                $data['contact1']=$_POST['_contact1'];
                $data['phone1']=$_POST['_phone1'];
                $data['contact2']=$_POST['_contact2'];
                $data['phone2']=$_POST['_phone2'];
                $result = self::insert_curtain_agent($data);
            }
        
            if( isset($_POST['_update_agent']) ) {
                $data=array();
                $data['agent_number']=$_POST['_agent_number'];
                $data['agent_name']=$_POST['_agent_name'];
                $data['agent_address']=$_POST['_agent_address'];
                $data['contact1']=$_POST['_contact1'];
                $data['phone1']=$_POST['_phone1'];
                $data['contact2']=$_POST['_contact2'];
                $data['phone2']=$_POST['_phone2'];
                $where=array();
                $where['curtain_agent_id']=$_POST['_curtain_agent_id'];
                $result = self::update_curtain_agents($data, $where);
            }
        
            global $wpdb;
            if( isset($_POST['_where_agents']) ) {
                $where='"%'.$_POST['_where_agents'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE agent_name LIKE {$where}", OBJECT );
                unset($_POST['_where_agents']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_agents", OBJECT );
            }
            $output  = '<h2>Curtain Agents</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td colspan=6 style="text-align:right">';
            $output .= '<form method="post">';
            $output .= '<input type="text" name="_where_agents" placeholder="Search...">';
            $output .= '<input type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</td></tr>';
            $output .= '<tr style="background-color:yellow">';
            $output .= '<td>id</td>';
            $output .= '<td>agent</td>';
            $output .= '<td>name</td>';
            $output .= '<td>contact</td>';
            $output .= '<td>phone</td>';
            $output .= '<td>updated</td>';
            $output .= '</tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->curtain_agent_id.'</a></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->curtain_agent_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->agent_number.'" name="_mode">';
                $output .= '</form></td>';
                $output .= '<td>'.$result->agent_name.'</td>';
                $output .= '<td>'.$result->contact1.'</td>';
                $output .= '<td>'.$result->phone1.'</td>';
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
                $output .= get_site_url().'/service/?serial_no='.$_POST['_serial_no'].'</div></div></div>';
            }
                            
            return $output;
        }

        function edit_curtain_agent( $_id=null, $_mode=null ) {

            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id={$_id}", OBJECT );
            if( $_mode=='Create' ) {
                $output  = '<h2>New Curtain Agent</h2>';
            } else {
                $output  = '<h2>Curtain Agents Update</h2>';
            }
            $output .= '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            if( $_mode=='Create' ) {
                $output .= '<tr><td>'.'Agent Number:'.'</td><td><input size="50" type="text" name="_agent_number"></td></tr>';
                $output .= '<tr><td>'.'Agent Name:'.'</td><td><input size="50" type="text" name="_agent_name"></td></tr>';
                $output .= '<tr><td>'.'Agent Address:'.'</td><td><input size="50" type="text" name="_agent_address"></td></tr>';            
                $output .= '<tr><td>'.'Contact1:'.'</td><td><input size="50" type="text" name="_contact1"></td></tr>';            
                $output .= '<tr><td>'.'Phone1:'.'</td><td><input size="50" type="text" name="_phone1"></td></tr>';            
                $output .= '<tr><td>'.'Contact2:'.'</td><td><input size="50" type="text" name="_contact2"></td></tr>';            
                $output .= '<tr><td>'.'Phone12:'.'</td><td><input size="50" type="text" name="_phone2"></td></tr>';            
            } else {
                $output .= '<input type="hidden" value="'.$row->curtain_agent_id.'" name="_curtain_agent_id">';
                $output .= '<tr><td>'.'Agent Number:'.'</td><td><input size="50" type="text" name="_agent_number" value="'.$row->agent_number.'"></td></tr>';
                $output .= '<tr><td>'.'Agent Name:'.'</td><td><input size="50" type="text" name="_agent_name" value="'.$row->agent_name.'"></td></tr>';
                $output .= '<tr><td>'.'Agent Address:'.'</td><td><input size="50" type="text" name="_agent_address" value="'.$row->agent_address.'"></td></tr>';
                $output .= '<tr><td>'.'Contact1:'.'</td><td><input size="50" type="text" name="_contact1" value="'.$row->contact1.'"></td></tr>';
                $output .= '<tr><td>'.'Phone1:'.'</td><td><input size="50" type="text" name="_phone1" value="'.$row->phone1.'"></td></tr>';
                $output .= '<tr><td>'.'Contact2:'.'</td><td><input size="50" type="text" name="_contact2" value="'.$row->contact2.'"></td></tr>';
                $output .= '<tr><td>'.'Phone2:'.'</td><td><input size="50" type="text" name="_phone2" value="'.$row->phone2.'"></td></tr>';
            }   
            $output .= '</tbody></table></figure>';

            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            if( $_mode=='Create' ) {
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create_agent">';
            } else {
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update_agent">';
            }
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';
        
            if( !($_mode=='Create') ) {
                $where='curtain_agent_id='.$row->curtain_agent_id;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE {$where}", OBJECT );
                $output .= '<figure class="wp-block-table"><table><tbody>';
                $output .= '<tr style="background-color:yellow">';
                $output .= '<td>#</td>';
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
                    $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_model WHERE curtain_model_id = {$result->curtain_model_id}", OBJECT );
                    $output .= '<td>'.$model->curtain_model.'</td>';
                    //$spec = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}specifications WHERE specification_id = {$result->specification_id}", OBJECT );
                    //$output .= '<td>'.$spec->specification.'</td>';
                    $output .= '<td>'.$result->specification.'</td>';
                    $user = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = {$result->curtain_user_id}", OBJECT );
                    $output .= '<td>'.$user->display_name.'</td>';
                    $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                    $output .= '</tr>';
                }
                $output .= '</tbody></table></figure>';

                $curtain_model = new curtain_model();
                //$specifications = new specifications();

                $output .= '<form method="post">';
                $output .= '<figure class="wp-block-table"><table><tbody>';
                $output .= '<input type="hidden" value="'.$row->curtain_agent_id.'" name="_curtain_agent_id">';
                $output .= '<tr><td>'.'Model Number:'.'</td><td><select name="_curtain_model_id">'.$curtain_model->select_options().'</select></td></tr>';
                //$output .= '<tr><td>'.'Specification:'.'</td><td><select name="_specification_id">'.$specifications->select_options().'</select></td></tr>';
                $output .= '<tr><td>'.'Specification:'.'</td><td><input size="50" type="text" name="_specification"></td></tr>';
                $output .= '</tbody></table></figure>';
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="New a Serial No" name="generate_serial_no">';
                $output .= '</div>';
                $output .= '</div>';
                $output .= '</form>';                
            }

            return $output;
        }

        function insert_curtain_agent($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_agents';
            $data = array(
                'agent_number' => $data['agent_number'],
                'agent_name' => $data['agent_name'],
                'agent_address' => $data['agent_address'],
                'contact1' => $data['contact1'],
                'phone1' => $data['phone1'],
                'contact2' => $data['contact2'],
                'phone2' => $data['phone2'],
                'create_timestamp' => time(),
                'update_timestamp' => time(),
            );
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        function update_curtain_agents($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_agents';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_agents` (
                curtain_agent_id int NOT NULL AUTO_INCREMENT,
                agent_number varchar(5),
                agent_name varchar(50),
                agent_address varchar(250),
                contact1 varchar(20),
                phone1 varchar(20),
                contact2 varchar(20),
                phone2 varchar(20),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_agent_id)
            ) $charset_collate;";
            dbDelta($sql);            
        }
    }
    new curtain_agents();
}