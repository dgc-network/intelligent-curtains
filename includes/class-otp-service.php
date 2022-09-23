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
    }
    new otp_service();
}
?>