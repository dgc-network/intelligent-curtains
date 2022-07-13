<?php

$options = get_option( 'op_return_settings' );
define('OP_RETURN_IN_PRODUCTION', $options['in_production_field']); // development mode or production mode
define('OP_RETURN_BITCOIN_IP', $options['ip_address_field']); // IP address of your bitcoin node
define('OP_RETURN_BITCOIN_USE_CMD', false); // use command-line instead of JSON-RPC?
define('OP_RETURN_BITCOIN_PORT', $options['port_number_field']); // leave empty to use default port for mainnet/testnet
define('OP_RETURN_BITCOIN_USER', $options['rpc_user_field']); // leave empty to read from ~/.bitcoin/bitcoin.conf (Unix only)
define('OP_RETURN_BITCOIN_PASSWORD', $options['rpc_password_field']); // leave empty to read from ~/.bitcoin/bitcoin.conf (Unix only)
define('OP_RETURN_SEND_AMOUNT', $options['send_amount_field']); // BTC send amount per transaction
define('OP_RETURN_SEND_ADDRESS', $options['send_address_field']); // BTC send address per transaction

define('OP_RETURN_BTC_FEE', $options['transaction_fee_field']); // BTC fee to pay per transaction
define('OP_RETURN_BTC_DUST', $options['dust_amount_field']); // omit BTC outputs smaller than this

define('OP_RETURN_MAX_BYTES', $options['max_bytes_field']); // maximum bytes in an OP_RETURN (80 as of Bitcoin 0.11)
define('OP_RETURN_MAX_BLOCKS', $options['max_blocks_field']); // maximum number of blocks to try when retrieving data

define('OP_RETURN_NET_TIMEOUT_CONNECT', $options['connect_timeout_field']); // how long to time out when connecting to bitcoin node
define('OP_RETURN_NET_TIMEOUT_RECEIVE', $options['receive_timeout_field']); // how long to time out retrieving data from bitcoin node

function op_return_submenu_settings() {
    add_options_page(
        __( 'OP_RETURN Settings', 'textdomain' ),
        __( 'OP_RETURN', 'textdomain' ),
      'manage_options',
      'op-return-page',
      'op_return_render_settings_page'
    );
}
add_action( 'admin_menu', 'op_return_submenu_settings' );

function op_return_settings_page_callback() {
?>
    <h2>OP_RETURN Settings</h2>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'op_return_group' );
        do_settings_sections( 'op_return_page' );
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

function op_return_register_settings() {
    register_setting(
        'op_return_group',
        'op_return_settings',
        'op_return_sanitize_callback'
    );

    add_settings_section(
        'section_one',
        '',
        'op_return_section_one_callback',
        'op_return_page'
    );

    add_settings_field(
        'in_production_field',
        'Production Mode:',
        'op_return_render_in_production_field',
        'op_return_page',
        'section_one'
    );

    add_settings_field(
        'ip_address_field',
        'IP Address:',
        'op_return_render_ip_address_field',
        'op_return_page',
        'section_one'
    );

    add_settings_field(
        'port_number_field',
        'Port Number:',
        'op_return_render_port_number_field',
        'op_return_page',
        'section_one'
    );

    add_settings_field(
        'rpc_user_field',
        'RPC User:',
        'op_return_render_rpc_user_field',
        'op_return_page',
        'section_one'
    );

    add_settings_field(
        'rpc_password_field',
        'RPC Password:',
        'op_return_render_rpc_password_field',
        'op_return_page',
        'section_one'
    );

    add_settings_field(
        'send_amount_field',
        'Send Amount:',
        'op_return_render_send_amount_field',
        'op_return_page',
        'section_one'
    );

    add_settings_field(
        'send_address_field',
        'Send Address:',
        'op_return_render_send_address_field',
        'op_return_page',
        'section_one'
    );

    add_settings_field(
        'transaction_fee_field',
        'Transaction Fee:',
        'op_return_render_transaction_fee_field',
        'op_return_page',
        'section_one'
    );

    add_settings_field(
        'dust_amount_field',
        'Dust Amount:',
        'op_return_render_dust_amount_field',
        'op_return_page',
        'section_one'
    );

    add_settings_field(
        'max_bytes_field',
        'Max Bytes:',
        'op_return_render_max_bytes_field',
        'op_return_page',
        'section_one'
    );

    add_settings_field(
        'max_blocks_field',
        'Max Blocks:',
        'op_return_render_max_blocks_field',
        'op_return_page',
        'section_one'
    );

    add_settings_field(
        'connect_timeout_field',
        'Connect Timeout:',
        'op_return_render_connect_timeout_field',
        'op_return_page',
        'section_one'
    );

    add_settings_field(
        'receive_timeout_field',
        'Receive Timeout:',
        'op_return_render_receive_timeout_field',
        'op_return_page',
        'section_one'
    );

}
add_action( 'admin_init', 'op_return_register_settings' );

function op_return_sanitize_callback( $input ) {
    $output['in_production_field']   = rest_sanitize_boolean( $input['in_production_field'] );
    $output['ip_address_field']      = sanitize_text_field( $input['ip_address_field'] );
    $output['port_number_field']     = sanitize_text_field( $input['port_number_field'] );
    $output['rpc_user_field']        = sanitize_text_field( $input['rpc_user_field'] );
    $output['rpc_password_field']    = sanitize_text_field( $input['rpc_password_field'] );
    $output['send_amount_field']     = floatval( $input['send_amount_field'] );
    $output['send_address_field']    = sanitize_text_field( $input['send_address_field'] );
    $output['transaction_fee_field'] = floatval($input['transaction_fee_field']);
    $output['dust_amount_field']     = floatval($input['dust_amount_field']);
    $output['max_bytes_field']       = intval($input['max_bytes_field']);
    $output['max_blocks_field']      = intval($input['max_blocks_field']);
    $output['connect_timeout_field'] = intval($input['connect_timeout_field']);
    $output['receive_timeout_field'] = intval($input['receive_timeout_field']);
    // ...
    return $output;
}

function op_return_section_one_callback() {
    //echo '<p>This is the first (and only) section in my settings.</p>';
}
  
function op_return_render_in_production_field() {
    $options = get_option( 'op_return_settings' );
    if (esc_attr( $options['in_production_field'] )){
        printf(
            '<input type="checkbox" name="%s" checked />',
            esc_attr( 'op_return_settings[in_production_field]' )
        );      
    } else {
        printf(
            '<input type="checkbox" name="%s" />',
            esc_attr( 'op_return_settings[in_production_field]' )
        );      
    }
}

function op_return_render_ip_address_field() {
    $options = get_option( 'op_return_settings' );
    printf(
      '<input type="text" size="50" name="%s" value="%s" />',
      esc_attr( 'op_return_settings[ip_address_field]' ),
      esc_attr( $options['ip_address_field'] )
    );
}

function op_return_render_port_number_field() {
    $options = get_option( 'op_return_settings' );
    printf(
      '<input type="text" size="50" name="%s" value="%s" />',
      esc_attr( 'op_return_settings[port_number_field]' ),
      esc_attr( $options['port_number_field'] )
    );
}

function op_return_render_rpc_user_field() {
    $options = get_option( 'op_return_settings' );
    printf(
      '<input type="text" size="50" name="%s" value="%s" />',
      esc_attr( 'op_return_settings[rpc_user_field]' ),
      esc_attr( $options['rpc_user_field'] )
    );
}

function op_return_render_rpc_password_field() {
    $options = get_option( 'op_return_settings' );
    printf(
      '<input type="text" size="50" name="%s" value="%s" />',
      esc_attr( 'op_return_settings[rpc_password_field]' ),
      esc_attr( $options['rpc_password_field'] )
    );
}

function op_return_render_send_amount_field() {
    $options = get_option( 'op_return_settings' );
    printf(
      '<input type="text" name="%s" value="%s" />',
      esc_attr( 'op_return_settings[send_amount_field]' ),
      esc_attr( $options['send_amount_field'] )
    );
}

function op_return_render_send_address_field() {
    $options = get_option( 'op_return_settings' );
    printf(
      '<input type="text" size="50" name="%s" value="%s" />',
      esc_attr( 'op_return_settings[send_address_field]' ),
      esc_attr( $options['send_address_field'] )
    );
}

function op_return_render_transaction_fee_field() {
    $options = get_option( 'op_return_settings' );
    printf(
      '<input type="text" name="%s" value="%s" />',
      esc_attr( 'op_return_settings[transaction_fee_field]' ),
      esc_attr( $options['transaction_fee_field'] )
    );
}

function op_return_render_dust_amount_field() {
    $options = get_option( 'op_return_settings' );
    printf(
      '<input type="number" name="%s" value="%s" />',
      esc_attr( 'op_return_settings[dust_amount_field]' ),
      esc_attr( $options['dust_amount_field'] )
    );
}

function op_return_render_max_bytes_field() {
    $options = get_option( 'op_return_settings' );
    printf(
      '<input type="number" name="%s" value="%s" />',
      esc_attr( 'op_return_settings[max_bytes_field]' ),
      esc_attr( $options['max_bytes_field'] )
    );
}

function op_return_render_max_blocks_field() {
    $options = get_option( 'op_return_settings' );
    printf(
      '<input type="number" name="%s" value="%s" />',
      esc_attr( 'op_return_settings[max_blocks_field]' ),
      esc_attr( $options['max_blocks_field'] )
    );
}

function op_return_render_connect_timeout_field() {
    $options = get_option( 'op_return_settings' );
    printf(
      '<input type="number" name="%s" value="%s" />',
      esc_attr( 'op_return_settings[connect_timeout_field]' ),
      esc_attr( $options['connect_timeout_field'] )
    );
}

function op_return_render_receive_timeout_field() {
    $options = get_option( 'op_return_settings' );
    printf(
      '<input type="number" name="%s" value="%s" />',
      esc_attr( 'op_return_settings[receive_timeout_field]' ),
      esc_attr( $options['receive_timeout_field'] )
    );
}

?>