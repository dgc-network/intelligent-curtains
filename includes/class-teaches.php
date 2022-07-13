<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('teaches')) {

    class teaches {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('teach_list', __CLASS__ . '::list_mode');
            self::create_tables();
        }

        function list_mode() {

            if( isset($_POST['submit_action']) ) {
        
                global $wpdb;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}teach_courses WHERE teach_id = {$_GET['_id']}", OBJECT );
                foreach ($results as $index => $result) {
                    if ( $_POST['_course_id_'.$index]=='delete_select' ){
                        $table = $wpdb->prefix.'teach_courses';
                        $where = array(
                            't_c_id' => $results[$index]->t_c_id
                        );
                        $wpdb->delete( $table, $where );    
                    } else {
                        $table = $wpdb->prefix.'teach_courses';
                        $data = array(
                            //'teach_date' => strtotime($_POST['_teach_date']),
                            'course_id' => $_POST['_course_id_'.$index]
                        );
                        $where = array(
                            't_c_id' => $results[$index]->t_c_id
                        );
                        $updated = $wpdb->update( $table, $data, $where );
                    }
                }
                if (( $_POST['_course_id']=='no_select' ) || ( $_POST['_course_id']=='delete_select' ) ){
                } else {
                    $table = $wpdb->prefix.'teach_courses';
                    $data = array(
                        //'created_date' => strtotime($_POST['_created_date']), 
                        'teach_id' => $_GET['_id'],
                        'course_id' => $_POST['_course_id']
                    );
                    $format = array('%d', '%d');
                    $wpdb->insert($table, $data, $format);    
                }
            }
            
            if( isset($_GET['view_mode']) ) {
                global $wpdb;
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}teaches WHERE teach_id = {$_GET['_id']}", OBJECT );
                $TeachDate = wp_date( get_option( 'date_format' ), $row->teach_date );
                $output  = '<form method="post">';
                $output .= '<figure class="wp-block-table"><table><tbody>';
                $output .= '<tr><td>'.'Title:'.'</td><td>'.$row->teach_title.'</td></tr>';
                $output .= '<tr><td>'.'Date:'.'</td><td>'.$TeachDate.'</td></tr>';
                $output .= '</tbody></table></figure>';

                $output .= '<figure class="wp-block-table"><table><tbody>';
                $output .= '<tr><td>'.'#'.'</td><td>'.'Courses'.'</td></tr>';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}teach_courses WHERE teach_id = {$_GET['_id']}", OBJECT );
                foreach ($results as $index => $result) {
                    $output .= '<tr><td>'.$index.'</td><td>'.'<select name="_course_id_'.$index.'">'.Courses::select_options($results[$index]->course_id).'</td></tr>';
                }
                $output .= '<tr><td>'.($index+1).'</td><td>'.'<select name="_course_id">'.Courses::select_options().'</select>'.'</td></tr>';
                $output .= '</tbody></table></figure>';
                
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Submit" name="submit_action">';
                $output .= '</div>';
                $output .= '</form>';
                $output .= '<form method="get">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
                $output .= '</div>';
                $output .= '</div>';
                $output .= '</form>';

                return $output;
            }        
            
            if( isset($_POST['edit_mode']) ) {
        
            //$AgentList = new AgentList();
            //$Agent = new Agent();
                //$agents = $AgentList->getAgents();
        /*
                foreach ($courses as $index => $course) {
                    if ($_POST['_item']=='edit_'.$index) {
                        $PublicKey = $agents[$index]->getPublicKey();
                        $KeyValueEntries = $agents[$index]->getMetadata();
                        foreach ($KeyValueEntries as $KeyValueEntry)
                        if ($KeyValueEntry->getKey()=='email') 
                            $LoginName = $KeyValueEntry->getValue();
                    }
                }
        */
                global $wpdb;
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}teaches WHERE teach_id = {$_POST['_id']}", OBJECT );
                $TeachDate = wp_date( get_option( 'date_format' ), $row->teach_date );
                $output  = '<form method="post">';
                $output .= '<figure class="wp-block-table"><table><tbody>';
                if( $_POST['edit_mode']=='Create' ) {
                    $output .= '<tr><td>'.'Title:'.'</td><td><input style="width: 100%" type="text" name="_teach_title" value=""></td></tr>';
                    $output .= '<tr><td>'.'Date:'.'</td><td><input style="width: 100%" type="date" name="_teach_date" value=""></td></tr>';
                }
                if( $_POST['edit_mode']=='Update' ) {
                    $output .= '<tr><td>'.'ID:'.'</td><td style="width: 100%"><input style="width: 100%" type="text" name="_teach_id" value="'.$row->teach_id.'"></td></tr>';
                    $output .= '<tr><td>'.'Title:'.'</td><td><input style="width: 100%" type="text" name="_teach_title" value="'.$row->teach_title.'"></td></tr>';
                    $output .= '<tr><td>'.'Date:'.'</td><td><input style="width: 100%" type="date" name="_teach_date" value="'.$TeachDate.'"></td></tr>';
                }
                if( $_POST['edit_mode']=='Delete' ) {
                    $output .= '<tr><td>'.'ID:'.'</td><td style="width: 100%"><input style="width: 100%" type="text" name="_teach_id" value="'.$row->teach_id.'"></td></tr>';
                    $output .= '<tr><td>'.'Title:'.'</td><td><input style="width: 100%" type="text" name="_teach_title" value="'.$row->teach_title.'" disabled></td></tr>';
                    $output .= '<tr><td>'.'Date:'.'</td><td><input style="width: 100%" type="date" name="_teach_date" value="'.$TeachDate.'" disabled></td></tr>';
                }
                $output .= '</tbody></table></figure>';
        
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                if( $_POST['edit_mode']=='Create' ) {
                    $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="create_action">';
                }
                if( $_POST['edit_mode']=='Update' ) {
                    $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="update_action">';
                }
                if( $_POST['edit_mode']=='Delete' ) {
                    $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="delete_action">';
                }
                $output .= '</div>';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
                $output .= '</div>';
                $output .= '</div>';
                $output .= '</form>';
            
                return $output;
        
            }
            
            if( isset($_POST['create_action']) ) {
        
                global $wpdb;
                $table = $wpdb->prefix.'teaches';
                $data = array(
                    'teach_date' => strtotime($_POST['_teach_date']), 
                    'teach_title' => $_POST['_teach_title']
                );
                $format = array('%d', '%s');
                $wpdb->insert($table, $data, $format);
                $my_id = $wpdb->insert_id;
        
                $Roles = array();
                $KeyValueEntries = array();
        /*
                $KeyValueEntry = new KeyValueEntry();
                $KeyValueEntry->setKey('email');
                $KeyValueEntry->setValue($_POST['_LoginName']);
                $KeyValueEntries[]=$KeyValueEntry;
        
                $CreateAgentAction = new CreateAgentAction();
                $CreateAgentAction->setOrgId($_GET['_OrgId']);
                $CreateAgentAction->setPublicKey($_POST['_PublicKey']);
                $CreateAgentAction->setActive($_GET['_Active']);
                $CreateAgentAction->setRoles($Roles);
                $CreateAgentAction->setMetadata($KeyValueEntries);
        
                $send_data = $CreateAgentAction->serializeToString();
                $send_address = 'DFcP5QFjbYtfgzWoqGedhxecCrRe41G3RD';
                $private_key = 'L44NzghbN6UD737kG6ukfdCq6BXyyTY2W15UkNhHnBff6acYWtsZ';
                $send_amount = 0.001;
            
                try {
                    $agents = $AgentList->getAgents();
                    $Agent->mergeFromString($send_data);
                    $agents[] = $Agent;
                    $AgentList->setAgents($agents);
                    //$send_data = $AgentList->serializeToString();
                } catch (Exception $e) {
                    // Handle parsing error from invalid data.
                    // ...
                }
        */        
        /*
                $result = OP_RETURN_send($send_address, $send_amount, $send_data);
            
                if (isset($result['error']))
                    $result_output = 'Error: '.$result['error']."\n";
                else
                    $result_output = 'TxID: '.$result['txid']."\nWait a few seconds then check on: http://coinsecrets.org/\n";
        */
            
            }
        
            if( isset($_POST['update_action']) ) {
        
                global $wpdb;
                $table = $wpdb->prefix.'teaches';
                $data = array(
                    'teach_title' => $_POST['_teach_title'],
                    'teach_date' => strtotime($_POST['_teach_date'])
                );
                $where = array('teach_id' => $_POST['_teach_id']);
                $updated = $wpdb->update( $table, $data, $where );
/*         
                if ( false === $updated ) {
                    // There was an error.
                } else {
                    // No error. You can check updated to see how many rows were changed.
                }
                
                $Roles = array();
                $KeyValueEntries = array();
        
                $KeyValueEntry = new KeyValueEntry();
                $KeyValueEntry->setKey('email');
                $KeyValueEntry->setValue($_GET['_Name']);
                $KeyValueEntries[]=$KeyValueEntry;
        
                $UpdateAgentAction = new UpdateAgentAction();
                $UpdateAgentAction->setOrgId($_GET['_OrgId']);
                $UpdateAgentAction->setPublicKey($_GET['_PublicKey']);
                $UpdateAgentAction->setActive($_GET['_Active']);
                $UpdateAgentAction->setRoles($Roles);
                $UpdateAgentAction->setMetadata($KeyValueEntries);
        
                $send_data = $UpdateAgentAction->serializeToString();
                $send_address = 'DFcP5QFjbYtfgzWoqGedhxecCrRe41G3RD';
                $private_key = 'L44NzghbN6UD737kG6ukfdCq6BXyyTY2W15UkNhHnBff6acYWtsZ';
                $send_amount = 0.001;
            
                try {
                    $agents = $AgentList->getAgents();
                    $Agent->mergeFromString($send_data);
                    foreach ( $agents as $agent ){
        
                    }
                    //$agents[] = $Agent;
                    $AgentList->setAgents($agents);
                    //$send_data = $AgentList->serializeToString();
                } catch (Exception $e) {
                    // Handle parsing error from invalid data.
                    // ...
                }
        
                $result = OP_RETURN_send($send_address, $send_amount, $send_data);
            
                if (isset($result['error']))
                    $result_output = 'Error: '.$result['error']."\n";
                else
                    $result_output = 'TxID: '.$result['txid']."\nWait a few seconds then check on: http://coinsecrets.org/\n";
        */
                
            }
        
            if( isset($_POST['delete_action']) ) {
                global $wpdb;
                $table = $wpdb->prefix.'teaches';
                $where = array('teach_id' => $_POST['_teach_id']);
                $deleted = $wpdb->delete( $table, $where );
            }

            /**
             * List Mode
             */                    
            $output  = '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>Title</td><td>Date</td><td>--</td><td>--</td></tr>';
        
            //$agents = $AgentList->getAgents();
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}teaches", OBJECT );
            foreach ($results as $index => $result) {
        /*
                $PublicKey = $agents[$index]->getPublicKey();
                $KeyValueEntries = $agents[$index]->getMetadata();
                foreach ($KeyValueEntries as $KeyValueEntry)
                    if ($KeyValueEntry->getKey()=='email') 
                        $LoginName = $KeyValueEntry->getValue();
        */
                //$CourseId = $results[$index]['CourseId'];
                //$CourseName = $results[$index]['CourseName'];
                $TeachId = $results[$index]->teach_id;
                $TeachTitle = $results[$index]->teach_title;
                //$TeachDate = $results[$index]->teach_date;
                $TeachDate = wp_date( get_option( 'date_format' ), $results[$index]->teach_date );
        
                $output .= '<form method="post" name="'.$index.'">';
                $output .= '<tr>';
                $output .= '<td><a href="?view_mode=true&_id='.$TeachId.'">'.$TeachTitle.'</a></td>';
                $output .= '<td>'.$TeachDate.'</td>';
                $output .= '<input type="hidden" value="'.$TeachId.'" name="_id">';
                $output .= '<td><input class="wp-block-button__link" type="submit" value="Update" name="edit_mode"></td>';
                $output .= '<td><input class="wp-block-button__link" type="submit" value="Delete" name="edit_mode"></td>';
                $output .= '</tr>';
                $output .= '</form>';
            }        
            $output .= '</tbody></table></figure>';
        
            $output .= '<form method="post">';
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="edit_mode">';
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            $output .= '<a class="wp-block-button__link" href="/">Cancel</a>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';
        
            return $output;    
        }
        
        function create_tables() {
        
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}teaches` (
                teach_id int NOT NULL AUTO_INCREMENT,
                teach_title varchar(255) NOT NULL,
                teach_date int NOT NULL,
                PRIMARY KEY  (teach_id)
            ) $charset_collate;";        
            dbDelta($sql);

            $sql = "CREATE TABLE `{$wpdb->prefix}teach_courses` (
                t_c_id int NOT NULL AUTO_INCREMENT,
                teach_id int NOT NULL,
                course_id int NOT NULL,
                PRIMARY KEY  (t_c_id)
            ) $charset_collate;";        
            dbDelta($sql);
        }
        
    }
    new teaches();
}
?>