<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function user_custom_fields(WP_User $user) {
    ?>
    <h2>Custom Fields</h2>
        <table class="form-table">
            <tr>
                <th><label for="line_user_id">Line User ID</label></th>
                <td>
                    <input
                        type="text"
                        value="<?php echo esc_attr(get_user_meta($user->ID, 'line_user_id', true)); ?>"
                        name="line_user_id"
                        id="line_user_id"
                        class="regular-text"
                    >
                </td>
            </tr>
            <tr>
                <th><label for="wallet_address">Wallet address</label></th>
                <td>
                    <input
                        type="text"
                        value="<?php echo esc_attr(get_user_meta($user->ID, 'wallet_address', true)); ?>"
                        name="wallet_address"
                        id="wallet_address"
                        class="regular-text"
                    >
                </td>
            </tr>
            <tr>
                <th><label for="curtain_agent_id">Agent</label></th>
                <td>
                    <?php
                    $curtain_agent_id = get_user_meta($user->ID, 'curtain_agent_id', true);
                    $curtain_agent_name = get_post_meta($curtain_agent_id, 'curtain_agent_name', true);
                    ?>
                    <input
                        type="text"
                        value="<?php echo esc_attr($curtain_agent_name); ?>"
                        name="curtain_agent_id"
                        id="curtain_agent_id"
                        class="regular-text"
                    >
                </td>
            </tr>
        </table>
    <?php
}
add_action('show_user_profile', 'user_custom_fields'); // editing your own profile
add_action('edit_user_profile', 'user_custom_fields'); // editing another user
add_action('user_new_form', 'user_custom_fields'); // creating a new user

function userMetaDataSave($userId) {
    if (current_user_can('edit_user', $userId)) {
        update_user_meta($userId, 'line_user_id', $_REQUEST['line_user_id']);
        update_user_meta($userId, 'wallet_address', $_REQUEST['wallet_address']);
    }    
}
add_action('personal_options_update', 'userMetaDataSave');
add_action('edit_user_profile_update', 'userMetaDataSave');
add_action('user_register', 'userMetaDataSave');