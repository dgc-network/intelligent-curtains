<?php
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}serial_number" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}curtain_users" );
//delete_option("my_plugin_db_version");

?>