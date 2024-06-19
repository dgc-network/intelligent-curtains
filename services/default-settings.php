<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'line-bot-api.php';
require_once plugin_dir_path( __FILE__ ) . 'open-ai-api.php';

function web_service_register_settings() {
    // Register Line bot section
    add_settings_section(
        'line_bot_settings_section',
        'Line bot Settings',
        'line_bot_settings_section_callback',
        'web-service-settings'
    );

    // Register a field
    add_settings_field(
        'line_bot_token_option',
        'Line bot Token',
        'line_bot_token_option_callback',
        'web-service-settings',
        'line_bot_settings_section'
    );
    register_setting('web-service-settings', 'line_bot_token_option');

    add_settings_field(
        'line_official_account',
        'Line official account',
        'line_official_account_callback',
        'web-service-settings',
        'line_bot_settings_section'
    );
    register_setting('web-service-settings', 'line_official_account');

    add_settings_field(
        'line_official_qr_code',
        'Line official qr-code',
        'line_official_qr_code_callback',
        'web-service-settings',
        'line_bot_settings_section'
    );
    register_setting('web-service-settings', 'line_official_qr_code');

    // Register AI section
    add_settings_section(
        'open_ai_settings_section',
        'Open AI Settings',
        'open_ai_settings_section_callback',
        'web-service-settings'
    );

    // Register a field
    add_settings_field(
        'open_ai_api_key',
        'API_KEY',
        'open_ai_api_key_callback',
        'web-service-settings',
        'open_ai_settings_section'
    );
    register_setting('web-service-settings', 'open_ai_api_key');
    
    // Register Operation section
    add_settings_section(
        'operation_settings_section',
        'Operation Settings',
        'operation_settings_section_callback',
        'web-service-settings'
    );

    // Register a field
    add_settings_field(
        'operation_row_counts',
        'Row counts',
        'operation_row_counts_callback',
        'web-service-settings',
        'operation_settings_section'
    );
    register_setting('web-service-settings', 'operation_row_counts');
    
    // Register a field
    add_settings_field(
        'operation_fee_rate',
        'Operation fee rate',
        'operation_fee_rate_callback',
        'web-service-settings',
        'operation_settings_section'
    );
    register_setting('web-service-settings', 'operation_fee_rate');
    
    // Register a field
    add_settings_field(
        'operation_wallet_address',
        'Wallet address',
        'operation_wallet_address_callback',
        'web-service-settings',
        'operation_settings_section'
    );
    register_setting('web-service-settings', 'operation_wallet_address');
    
}
add_action('admin_init', 'web_service_register_settings');

function web_service_menu() {
    add_options_page(
        'Web Service Settings',
        'Web Service',
        'manage_options',
        'web-service-settings',
        'web_service_settings_page'
    );
}
add_action('admin_menu', 'web_service_menu');

function web_service_settings_page() {
    ?>
    <div class="wrap">
        <h2>Web Service Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('web-service-settings');
            do_settings_sections('web-service-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function line_bot_settings_section_callback() {
    echo '<p>Settings for Line bot.</p>';
}

function line_bot_token_option_callback() {
    $value = get_option('line_bot_token_option');
    echo '<input type="text" id="line_bot_token_option" name="line_bot_token_option" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function line_official_account_callback() {
    $value = get_option('line_official_account');
    echo '<input type="text" id="line_official_account" name="line_official_account" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function line_official_qr_code_callback() {
    $value = get_option('line_official_qr_code');
    echo '<input type="text" id="line_official_qr_code" name="line_official_qr_code" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function open_ai_settings_section_callback() {
    echo '<p>Settings for Open AI.</p>';
}

function open_ai_api_key_callback() {
    $value = get_option('open_ai_api_key');
    echo '<input type="text" id="open_ai_api_key" name="open_ai_api_key" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function operation_settings_section_callback() {
    echo '<p>Settings for operation.</p>';
}

function operation_row_counts_callback() {
    $value = get_option('operation_row_counts');
    echo '<input type="text" id="operation_row_counts" name="operation_row_counts" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function operation_fee_rate_callback() {
    $value = get_option('operation_fee_rate');
    echo '<input type="text" id="operation_fee_rate" name="operation_fee_rate" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function operation_wallet_address_callback() {
    $value = get_option('operation_wallet_address');
    echo '<input type="text" id="operation_wallet_address" name="operation_wallet_address" style="width:100%;" value="' . esc_attr($value) . '" />';
}

