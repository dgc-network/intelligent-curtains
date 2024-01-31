<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function web_service_register_settings() {
    // Register a section
    add_settings_section(
        'line_bot_settings_section',
        'Line bot Settings',
        'line_bot_settings_section_callback',
        'general'
    );

    // Register a field
    add_settings_field(
        'line_bot_token_option',
        'Line bot Token',
        'line_bot_token_option_callback',
        'general',
        'line_bot_settings_section'
    );
    register_setting('general', 'line_bot_token_option');

    add_settings_field(
        'line_official_account',
        'Line official account',
        'line_official_account_callback',
        'general',
        'line_bot_settings_section'
    );
    register_setting('general', 'line_official_account');

    // Register a section
    add_settings_section(
        'open_ai_settings_section',
        'Open AI Settings',
        'open_ai_settings_section_callback',
        'general'
    );

    // Register a field
    add_settings_field(
        'open_ai_api_key',
        'API_KEY',
        'open_ai_api_key_callback',
        'general',
        'open_ai_settings_section'
    );
    register_setting('general', 'open_ai_api_key');
    
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
            settings_fields('general');
            do_settings_sections('general');
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

function open_ai_settings_section_callback() {
    echo '<p>Settings for Open AI.</p>';
}

function open_ai_api_key_callback() {
    $value = get_option('open_ai_api_key');
    echo '<input type="text" id="open_ai_api_key" name="open_ai_api_key" style="width:100%;" value="' . esc_attr($value) . '" />';
}

