<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('otp_service')) {

    class otp_service {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('product-info', __CLASS__ . '::registration');
            add_shortcode('serial-number-list', __CLASS__ . '::list_serial_number');
            add_shortcode('curtain-product-list', __CLASS__ . '::list_curtain_products');
            add_shortcode('curtain-user-list', __CLASS__ . '::list_curtain_users');
            self::create_tables();
            //self::delete_records();
        }

        function create_tables() {

            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}serial_number` (
                serial_number_id int NOT NULL AUTO_INCREMENT,
                curtain_product_id int(10),
                curtain_user_id int(10),
                qr_code_serial_no varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (serial_number_id)
            ) $charset_collate;";
            dbDelta($sql);
            
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_products` (
                curtain_product_id int NOT NULL AUTO_INCREMENT,
                model_number varchar(5),
                specification varchar(5),
                product_name varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_product_id)
            ) $charset_collate;";
            dbDelta($sql);
            
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_users` (
                curtain_user_id int NOT NULL AUTO_INCREMENT,
                line_user_id varchar(50),
                display_name varchar(50),
                last_otp varchar(10),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_user_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    new otp_service();
}
?>