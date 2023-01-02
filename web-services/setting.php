<?php

$options = get_option( 'web_services_settings' );
if ( $options['is_line_bot_api_enabled']==true ){
    require_once plugin_dir_path( __FILE__ ) . 'line-bot-api.php';
}
if ( $options['is_open_ai_api_enabled']==true ){
    require_once plugin_dir_path( __FILE__ ) . 'open-ai-api.php';
}
if ( $options['is_business_central_api_enabled']==true ){
    require_once plugin_dir_path( __FILE__ ) . 'business-central-api.php';
}
define('OP_RETURN_IN_PRODUCTION', $options['is_line_bot_api_enabled']); // development mode or production mode
define('OP_RETURN_BITCOIN_IP', $options['line_bot_token']); // IP address of your bitcoin node
define('OP_RETURN_BITCOIN_USE_CMD', false); // use command-line instead of JSON-RPC?
define('OP_RETURN_BITCOIN_PORT', $options['line_bot_secret']); // leave empty to use default port for mainnet/testnet
define('OP_RETURN_BITCOIN_USER', $options['open_ai_api_key']); // leave empty to read from ~/.bitcoin/bitcoin.conf (Unix only)
define('OP_RETURN_BITCOIN_PASSWORD', $options['business_central_token']); // leave empty to read from ~/.bitcoin/bitcoin.conf (Unix only)
define('OP_RETURN_SEND_AMOUNT', $options['send_amount_field']); // BTC send amount per transaction
define('OP_RETURN_SEND_ADDRESS', $options['send_address_field']); // BTC send address per transaction

define('OP_RETURN_BTC_FEE', $options['transaction_fee_field']); // BTC fee to pay per transaction
define('OP_RETURN_BTC_DUST', $options['dust_amount_field']); // omit BTC outputs smaller than this

define('OP_RETURN_MAX_BYTES', $options['max_bytes_field']); // maximum bytes in an OP_RETURN (80 as of Bitcoin 0.11)
define('OP_RETURN_MAX_BLOCKS', $options['max_blocks_field']); // maximum number of blocks to try when retrieving data

define('OP_RETURN_NET_TIMEOUT_CONNECT', $options['connect_timeout_field']); // how long to time out when connecting to bitcoin node
define('OP_RETURN_NET_TIMEOUT_RECEIVE', $options['receive_timeout_field']); // how long to time out retrieving data from bitcoin node

function web_services_submenu_settings() {
    add_options_page(
        __( 'Web Services Settings', 'textdomain' ),
        __( 'Web Services', 'textdomain' ),
      'manage_options',
      'web-services-page',
      'web_services_render_settings_page'
    );
}
add_action( 'admin_menu', 'web_services_submenu_settings' );

//function web_services_settings_page_callback() {
function web_services_render_settings_page() {
?>
    <h2>Web Services Settings</h2>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'web_services_group' );
        do_settings_sections( 'web_services_page' );
        ?>
        <input
           type="submit"
           name="submit"
           class="button button-primary"
           value="<?php esc_attr_e( 'Save' ); ?>"
        />
    </form>
<?php
}

function web_services_register_settings() {
    register_setting(
        'web_services_group',
        'web_services_settings',
        'web_services_sanitize_callback'
    );

    add_settings_section(
        'section_one',
        'Line Bot API',
        'web_services_section_one_callback',
        'web_services_page'
    );

    add_settings_section(
        'section_two',
        'Open AI API',
        'web_services_section_two_callback',
        'web_services_page'
    );

    add_settings_section(
        'section_three',
        'Business Central API',
        'web_services_section_three_callback',
        'web_services_page'
    );

    add_settings_field(
        'is_line_bot_api_enabled',
        'Line Bot API enabled:',
        'web_services_render_is_line_bot_api_enabled',
        'web_services_page',
        'section_one'
    );

    add_settings_field(
        'line_bot_token',
        'Token:',
        'web_services_render_line_bot_token',
        'web_services_page',
        'section_one'
    );

    add_settings_field(
        'line_bot_secret',
        'Secret:',
        'web_services_render_line_bot_secret',
        'web_services_page',
        'section_one'
    );

    add_settings_field(
        'is_open_ai_api_enabled',
        'Open AI API enabled:',
        'web_services_render_is_open_ai_api_enabled',
        'web_services_page',
        'section_two'
    );

    add_settings_field(
        'open_ai_api_key',
        'API KEY:',
        'web_services_render_open_ai_api_key',
        'web_services_page',
        'section_two'
    );

    add_settings_field(
        'is_business_central_api_enabled',
        'Business Central API enabled:',
        'web_services_render_is_business_central_api_enabled',
        'web_services_page',
        'section_three'
    );

    add_settings_field(
        'business_central_token',
        'Access Token:',
        'web_services_render_business_central_token',
        'web_services_page',
        'section_three'
    );

}
add_action( 'admin_init', 'web_services_register_settings' );

function web_services_sanitize_callback( $input ) {
    $output['is_line_bot_api_enabled']   = rest_sanitize_boolean( $input['is_line_bot_api_enabled'] );
    $output['line_bot_token']      = sanitize_text_field( $input['line_bot_token'] );
    $output['line_bot_secret']     = sanitize_text_field( $input['line_bot_secret'] );
    $output['is_open_ai_api_enabled']   = rest_sanitize_boolean( $input['is_open_ai_api_enabled'] );
    $output['open_ai_api_key']        = sanitize_text_field( $input['open_ai_api_key'] );
    $output['is_business_central_api_enabled']   = rest_sanitize_boolean( $input['is_business_central_api_enabled'] );
    $output['business_central_token']    = sanitize_text_field( $input['business_central_token'] );
    //$output['business_central_token']    = $input['business_central_token'] ;
    // ...
    return $output;
}

function web_services_section_one_callback() {
    echo '<p>Use the Messaging API to build bots that provide personalized experiences for your users on LINE.</p>';
}
  
function web_services_section_two_callback() {
    echo '<p>The API has been deployed in thousands of applications with tasks ranging from helping people learn new languages to solving complex classification problems.</p>';
}
  
function web_services_section_three_callback() {
    echo '<p>Connect apps establish a point-to-point connection between Dynamics 365 Business Central and a 3rd party solution or service and is typically created using standard REST API to interchange data.</p>';
}
  
function web_services_render_is_line_bot_api_enabled() {
    $options = get_option( 'web_services_settings' );
    if (esc_attr( $options['is_line_bot_api_enabled'] )){
        printf(
            '<input type="checkbox" name="%s" checked />',
            esc_attr( 'web_services_settings[is_line_bot_api_enabled]' )
        );      
    } else {
        printf(
            '<input type="checkbox" name="%s" />',
            esc_attr( 'web_services_settings[is_line_bot_api_enabled]' )
        );      
    }
}

function web_services_render_is_open_ai_api_enabled() {
    $options = get_option( 'web_services_settings' );
    if (esc_attr( $options['is_open_ai_api_enabled'] )){
        printf(
            '<input type="checkbox" name="%s" checked />',
            esc_attr( 'web_services_settings[is_open_ai_api_enabled]' )
        );      
    } else {
        printf(
            '<input type="checkbox" name="%s" />',
            esc_attr( 'web_services_settings[is_open_ai_api_enabled]' )
        );      
    }
}

function web_services_render_is_business_central_api_enabled() {
    $options = get_option( 'web_services_settings' );
    if (esc_attr( $options['is_business_central_api_enabled'] )){
        printf(
            '<input type="checkbox" name="%s" checked />',
            esc_attr( 'web_services_settings[is_business_central_api_enabled]' )
        );      
    } else {
        printf(
            '<input type="checkbox" name="%s" />',
            esc_attr( 'web_services_settings[is_business_central_api_enabled]' )
        );      
    }
}

function web_services_render_line_bot_token() {
    $options = get_option( 'web_services_settings' );
    printf(
      '<input type="text" size="50" name="%s" value="%s" />',
      esc_attr( 'web_services_settings[line_bot_token]' ),
      esc_attr( $options['line_bot_token'] )
    );
}

function web_services_render_line_bot_secret() {
    $options = get_option( 'web_services_settings' );
    printf(
      '<input type="text" size="50" name="%s" value="%s" />',
      esc_attr( 'web_services_settings[line_bot_secret]' ),
      esc_attr( $options['line_bot_secret'] )
    );
}

function web_services_render_open_ai_api_key() {
    $options = get_option( 'web_services_settings' );
    printf(
      '<input type="text" size="50" name="%s" value="%s" />',
      esc_attr( 'web_services_settings[open_ai_api_key]' ),
      esc_attr( $options['open_ai_api_key'] )
    );
}

function web_services_render_business_central_token() {
    $options = get_option( 'web_services_settings' );
    printf(
      '<input type="text" size="50" name="%s" value="%s" />',
      esc_attr( 'web_services_settings[business_central_token]' ),
      esc_attr( $options['business_central_token'] )
    );
}

?>