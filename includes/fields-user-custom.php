<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function user_custom_fields(WP_User $user) {
    ?>
    <h2>potte.art Custom Fields</h2>
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
        </table>
    <?php
    }
    add_action('show_user_profile', 'user_custom_fields'); // editing your own profile
    add_action('edit_user_profile', 'user_custom_fields'); // editing another user
    add_action('user_new_form', 'user_custom_fields'); // creating a new user
    
    function userMetaBirthdaySave($userId) {
        if (current_user_can('edit_user', $userId)) {
            update_user_meta($userId, 'line_user_id', $_REQUEST['line_user_id']);
            update_user_meta($userId, 'wallet_address', $_REQUEST['wallet_address']);
        }    
    }
    add_action('personal_options_update', 'userMetaBirthdaySave');
    add_action('edit_user_profile_update', 'userMetaBirthdaySave');
    add_action('user_register', 'userMetaBirthdaySave');