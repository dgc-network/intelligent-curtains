<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_service')) {
    class curtain_service {
        /**
         * Class constructor
         */
        public function __construct() {
            $this->create_tables();
        }

        public function init_curtain_service() {
            global $wpdb;
            $serial_number = new serial_number();

            if( isset($_GET['_id']) ) {
                $_SESSION['line_user_id'] = $_GET['_id'];
            }

            $output = '<div style="text-align:center;">';
            if( isset($_GET['serial_no']) ) {
                $qr_code_serial_no = $_GET['serial_no'];
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = %s", $qr_code_serial_no ), OBJECT );            
                if (is_null($row) || !empty($wpdb->last_error)) {
                    /** incorrect QR-code then display the admin link */
                    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE line_user_id = %s", $_SESSION['line_user_id'] ), OBJECT );
                    $output .= '<div class="wp-block-buttons">';
                    foreach ( $results as $index=>$result ) {
                        $output .= '<div class="wp-block-button" style="margin: 10px;">';
                        $output .= '<a class="wp-block-button__link" href="'.$this->get_link($result->service_option_id).'">'.$this->get_name($result->service_option_id).'</a>';
                        $output .= '</div>';
                    }
                    $output .= '</div>';                    

                } else {
                    /** registration for QR-code */
                    $curtain_user_id=$row->curtain_user_id;
                    $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = %d", $row->curtain_user_id ), OBJECT );            
                    if (!(is_null($user) || !empty($wpdb->last_error))) {
                        $output .= 'Hi, '.$user->display_name.'<br>';
                        //$_SESSION['line_user_id'] = $user->line_user_id;
                    }
                    $output .= '感謝您選購我們的電動窗簾<br>';
                    $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$row->curtain_model_id}", OBJECT );
                    if (!(is_null($model) || !empty($wpdb->last_error))) {
                        $output .= '型號:'.$model->curtain_model_name.' 規格: '.$row->specification.'<br>';
                    }
                    $six_digit_random_number = random_int(100000, 999999);
                    $output .= '請利用手機<i class="fa-solid fa-mobile-screen"></i>按'.'<a href="'.get_option('_line_account').'">這裡</a>, 加入我們的Line官方帳號,<br>';
                    $output .= '再使用電腦<i class="fa-solid fa-desktop"></i>上的Line, 在我們的官方帳號聊天室中輸入六位數字密碼,<br>';
                    $output .= '<span style="font-size:24px;color:blue;">'.$six_digit_random_number.'</span>'.'完成註冊程序<br>';

                    //$output .= '請利用手機按<br>'.'<a href="'.get_option('_line_account').'">';
                    //$output .= '<img src="https://scdn.line-apps.com/n/line_add_friends/btn/zh-Hant.png" alt="加入好友" height="16px" border="0"></a>';
                    //$output .= '<br>在我們的Line官方帳號聊天室中輸入六位數字密碼: <span style="font-size:24px;color:blue;">'.$six_digit_random_number.'</span>';
                    //$output .= ' 完成註冊程序<br>';
                    $data=array();
                    $data['one_time_password']=$six_digit_random_number;
                    $where=array();
                    $where['qr_code_serial_no']=$qr_code_serial_no;
                    $result = $serial_number->update_serial_number($data, $where);    
                }
    
            } else {

                $where='"%view%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_category LIKE {$where}", OBJECT );
                $output .= '<div class="wp-block-buttons">';
                foreach ( $results as $index=>$result ) {
                    $output .= '<div class="wp-block-button" style="margin: 10px;">';
                    $output .= '<a class="wp-block-button__link" href="'.$result->service_option_link.'">'.$result->service_option_title.'</a>';
                    $output .= '</div>';
                }
                $output .= '</div>';
            }
            $output .= '</div>';
            return $output;
        }

        public function list_service_options() {
            global $wpdb;
            $curtain_users = new curtain_users();

            if( isset($_SESSION['line_user_id']) ) {
                $_option_page = 'Options';
                $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE line_user_id = %s AND service_option_id= %d", $_SESSION['line_user_id'], $this->get_id($_option_page) ), OBJECT );            
                if (is_null($permission) || !empty($wpdb->last_error)) {
                    if ( $_GET['_check_permission'] != 'false' ) {
                        return 'You have not permission to access '.$_option_page.' page. Please check to the administrators.';
                    }
                }
            } else {
                if ( $_GET['_check_permission'] != 'false' ) {
                    return 'You have not permission to access this page. Please check to the administrators.';
                }
            }

            if( isset($_POST['_create']) ) {
                $data=array();
                $data['service_option_title']=$_POST['_service_option_title'];
                $data['service_option_link']=$_POST['_service_option_link'];
                $data['service_option_category']=$_POST['_service_option_category'];
                $data['service_option_page']=$_POST['_service_option_page'];
                $this->insert_service_option($data);
            }
        
            if( isset($_POST['_update']) ) {
                $data=array();
                $data['service_option_title']=$_POST['_service_option_title'];
                $data['service_option_link']=$_POST['_service_option_link'];
                $data['service_option_category']=$_POST['_service_option_category'];
                $data['service_option_page']=$_POST['_service_option_page'];
                $where=array();
                $where['service_option_id']=$_POST['_service_option_id'];
                $this->update_service_options($data, $where);
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $where=array();
                $where['service_option_id']=$_GET['_delete'];
                $this->delete_service_options($where);
                $curtain_users->delete_user_permissions($where);
            }

            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_title LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}service_options", OBJECT );
            }
            $output  = '<h2>Service Options</h2>';
            $output .= '<div style="display: flex; justify-content: space-between; margin: 5px;">';
            $output .= '<div>';
            $output .= '<form method="post">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_add">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div style="text-align: right">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input style="display:inline" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '</div>';

            $output .= '<div class="ui-widget">';
            $output .= '<table class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>title</th>';
            //$output .= '<th>link</th>';
            $output .= '<th>category</th>';
            //$output .= '<th>page</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="edit-btn-'.$result->service_option_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td>'.$result->service_option_title.'</td>';
                //$output .= '<td>'.$result->service_option_link.'</td>';
                $output .= '<td>'.$result->service_option_category.'</td>';
                //$output .= '<td>'.$result->service_option_page.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="del-btn-'.$result->service_option_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Service Option update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->service_option_id.'" name="_service_option_id">';
                $output .= '<label for="service_option_title">Option Title</label>';
                $output .= '<input type="text" name="_service_option_title" id="service_option_title" class="text ui-widget-content ui-corner-all" value="'.$row->service_option_title.'">';
                $output .= '<label for="service_option_link">Option Link/Page</label>';
                $output .= '<input type="text" name="_service_option_link" id="service_option_link" class="text ui-widget-content ui-corner-all" value="'.$row->service_option_link.'">';
                $output .= '<label for="service_option_category">Category</label>';
                $output .= '<input type="text" name="_service_option_category" id="service_option_category" class="text ui-widget-content ui-corner-all" value="'.$row->service_option_category.'">';
                //$output .= '<label for="service_option_page">Page</label>';
                //$output .= '<input type="text" name="_service_option_page" id="service_option_page" class="text ui-widget-content ui-corner-all" value="'.$row->service_option_page.'">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new option">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="service_option_title">Option Title</label>';
                $output .= '<input type="text" name="_service_option_title" id="service_option_title" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="service_option_link">Option Link/Page</label>';
                $output .= '<input type="text" name="_service_option_link" id="service_option_link" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="service_option_category">Category</label>';
                $output .= '<input type="text" name="_service_option_category" id="service_option_category" class="text ui-widget-content ui-corner-all">';
                //$output .= '<label for="service_option_page">Page</label>';
                //$output .= '<input type="text" name="_service_option_page" id="service_option_page" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        public function insert_service_option($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'service_options';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        public function update_service_options($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'service_options';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_service_options($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'service_options';
            $wpdb->delete($table, $where);
        }

        public function get_id( $_title='' ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_title = %s OR service_option_page = %s", $_title, $_title ), OBJECT );
            return $row->service_option_id;
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_id = %d OR service_option_page = %s", $_id, $_id ), OBJECT );
            return $row->service_option_title;
        }

        public function get_link( $_title=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_id = %d OR service_option_page = %s OR service_option_title = %s", $_title, $_title, $_title ), OBJECT );
            //return get_site_url().'/'.$row->service_option_link;
            return $row->service_option_link;
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}service_options` (
                service_option_id int NOT NULL AUTO_INCREMENT,
                service_option_title varchar(20),
                service_option_link varchar(255),
                service_option_category varchar(10),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (service_option_id)
            ) $charset_collate;";
            dbDelta($sql);            
        }
    }
    $my_class = new curtain_service();
    add_shortcode( 'curtain-service', array( $my_class, 'init_curtain_service' ) );
    add_shortcode( 'service-option-list', array( $my_class, 'list_service_options' ) );
}
?>