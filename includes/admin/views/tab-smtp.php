<?php
/**
 * SMTP Tab View
 *
 * @package WP_Site_Notifications
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get SMTP settings
$smtp_settings = isset($settings['smtp']) ? $settings['smtp'] : array();
$smtp_enabled = !empty($smtp_settings['enabled']);
$smtp_host = isset($smtp_settings['host']) ? $smtp_settings['host'] : '';
$smtp_port = isset($smtp_settings['port']) ? $smtp_settings['port'] : '587';
$smtp_encryption = isset($smtp_settings['encryption']) ? $smtp_settings['encryption'] : 'tls';
$smtp_auth = !empty($smtp_settings['auth']);

// Get email accounts
$smtp_accounts = isset($smtp_settings['accounts']) ? $smtp_settings['accounts'] : array();
$default_account = isset($smtp_settings['default_account']) ? $smtp_settings['default_account'] : '';
?>

<!-- SMTP Tab Content -->
<p class="description" style="margin-bottom: 20px;">
    <?php _e('Configure SMTP settings to send emails through your own mail server instead of the default PHP mail function.', 'wp-site-notifications'); ?>
</p>

<table class="form-table" role="presentation">
    <tr>
        <th scope="row">
            <label for="smtp_enabled"><?php _e('Enable SMTP', 'wp-site-notifications'); ?></label>
        </th>
        <td>
            <label>
                <input type="checkbox"
                       id="smtp_enabled"
                       name="post_notifications_settings[smtp][enabled]"
                       value="1"
                       <?php checked($smtp_enabled); ?>>
                <?php _e('Use SMTP for sending emails', 'wp-site-notifications'); ?>
            </label>
            <p class="description">
                <?php _e('When enabled, all emails from this plugin will be sent using the SMTP settings below.', 'wp-site-notifications'); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="smtp_host"><?php _e('SMTP Host', 'wp-site-notifications'); ?></label>
        </th>
        <td>
            <input type="text"
                   id="smtp_host"
                   name="post_notifications_settings[smtp][host]"
                   value="<?php echo esc_attr($smtp_host); ?>"
                   class="regular-text"
                   placeholder="smtp.example.com">
            <p class="description">
                <?php _e('The SMTP server hostname (e.g., smtp.gmail.com, smtp.office365.com).', 'wp-site-notifications'); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="smtp_port"><?php _e('SMTP Port', 'wp-site-notifications'); ?></label>
        </th>
        <td>
            <input type="number"
                   id="smtp_port"
                   name="post_notifications_settings[smtp][port]"
                   value="<?php echo esc_attr($smtp_port); ?>"
                   class="small-text"
                   min="1"
                   max="65535">
            <p class="description">
                <?php _e('Common ports: 25 (unencrypted), 465 (SSL), 587 (TLS).', 'wp-site-notifications'); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="smtp_encryption"><?php _e('Encryption', 'wp-site-notifications'); ?></label>
        </th>
        <td>
            <select id="smtp_encryption" name="post_notifications_settings[smtp][encryption]">
                <option value="" <?php selected($smtp_encryption, ''); ?>><?php _e('None', 'wp-site-notifications'); ?></option>
                <option value="ssl" <?php selected($smtp_encryption, 'ssl'); ?>>SSL</option>
                <option value="tls" <?php selected($smtp_encryption, 'tls'); ?>>TLS</option>
            </select>
            <p class="description">
                <?php _e('TLS is recommended for port 587, SSL for port 465.', 'wp-site-notifications'); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="smtp_auth"><?php _e('Authentication', 'wp-site-notifications'); ?></label>
        </th>
        <td>
            <label>
                <input type="checkbox"
                       id="smtp_auth"
                       name="post_notifications_settings[smtp][auth]"
                       value="1"
                       <?php checked($smtp_auth); ?>>
                <?php _e('Use SMTP authentication', 'wp-site-notifications'); ?>
            </label>
            <p class="description">
                <?php _e('Most SMTP servers require authentication.', 'wp-site-notifications'); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row" colspan="2">
            <h3 style="margin: 0;"><?php _e('Email Accounts', 'wp-site-notifications'); ?></h3>
            <p class="description" style="font-weight: normal; margin-top: 5px;">
                <?php _e('Add email accounts with their credentials. Each account can be used as a sender for different notification types.', 'wp-site-notifications'); ?>
            </p>
        </th>
    </tr>

    <tr>
        <th scope="row">
            <?php _e('Accounts', 'wp-site-notifications'); ?>
        </th>
        <td>
            <div id="smtp-accounts-container">
                <?php
                if (empty($smtp_accounts)) {
                    $smtp_accounts = array(array('email' => '', 'name' => '', 'username' => '', 'password' => ''));
                }
                foreach ($smtp_accounts as $index => $account) :
                    $account_email = isset($account['email']) ? $account['email'] : '';
                    $account_name = isset($account['name']) ? $account['name'] : '';
                    $account_username = isset($account['username']) ? $account['username'] : '';
                    $account_password = isset($account['password']) ? $account['password'] : '';
                ?>
                <div class="smtp-account-row" style="background: #f9f9f9; padding: 15px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                        <div>
                            <label style="display: block; margin-bottom: 3px; font-weight: 600;"><?php _e('From Email', 'wp-site-notifications'); ?></label>
                            <input type="email"
                                   name="post_notifications_settings[smtp][accounts][<?php echo $index; ?>][email]"
                                   value="<?php echo esc_attr($account_email); ?>"
                                   class="regular-text"
                                   placeholder="email@example.com"
                                   style="width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 3px; font-weight: 600;"><?php _e('From Name', 'wp-site-notifications'); ?></label>
                            <input type="text"
                                   name="post_notifications_settings[smtp][accounts][<?php echo $index; ?>][name]"
                                   value="<?php echo esc_attr($account_name); ?>"
                                   class="regular-text"
                                   placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>"
                                   style="width: 100%;">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="display: block; margin-bottom: 3px; font-weight: 600;"><?php _e('SMTP Username', 'wp-site-notifications'); ?></label>
                            <input type="text"
                                   name="post_notifications_settings[smtp][accounts][<?php echo $index; ?>][username]"
                                   value="<?php echo esc_attr($account_username); ?>"
                                   class="regular-text"
                                   autocomplete="off"
                                   style="width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 3px; font-weight: 600;"><?php _e('SMTP Password', 'wp-site-notifications'); ?></label>
                            <input type="password"
                                   name="post_notifications_settings[smtp][accounts][<?php echo $index; ?>][password]"
                                   value="<?php echo esc_attr($account_password); ?>"
                                   class="regular-text"
                                   autocomplete="new-password"
                                   style="width: 100%;">
                        </div>
                    </div>
                    <?php if ($index > 0) : ?>
                    <button type="button" class="button smtp-remove-account" style="margin-top: 10px;">
                        <?php _e('Remove Account', 'wp-site-notifications'); ?>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="smtp-add-account" class="button button-secondary">
                <?php _e('Add Another Account', 'wp-site-notifications'); ?>
            </button>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="smtp_default_account"><?php _e('Default Account', 'wp-site-notifications'); ?></label>
        </th>
        <td>
            <select id="smtp_default_account" name="post_notifications_settings[smtp][default_account]">
                <?php foreach ($smtp_accounts as $index => $account) :
                    if (!empty($account['email'])) :
                ?>
                <option value="<?php echo esc_attr($account['email']); ?>" <?php selected($default_account, $account['email']); ?>>
                    <?php echo esc_html($account['email']); ?>
                </option>
                <?php endif; endforeach; ?>
            </select>
            <p class="description">
                <?php _e('Select the default email account to use when no specific account is configured.', 'wp-site-notifications'); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <?php _e('Test Email', 'wp-site-notifications'); ?>
        </th>
        <td>
            <p class="description">
                <?php _e('Save settings first, then use the button below to send a test email.', 'wp-site-notifications'); ?>
            </p>
            <p style="margin-top: 10px;">
                <input type="email"
                       id="smtp_test_email"
                       placeholder="<?php esc_attr_e('Enter test email address', 'wp-site-notifications'); ?>"
                       class="regular-text">
                <button type="button"
                        id="smtp_send_test"
                        class="button button-secondary">
                    <?php _e('Send Test Email', 'wp-site-notifications'); ?>
                </button>
                <span id="smtp_test_result" style="margin-left: 10px;"></span>
            </p>
        </td>
    </tr>
</table>

<script>
jQuery(document).ready(function($) {
    // Add new account
    var accountIndex = <?php echo count($smtp_accounts); ?>;
    $('#smtp-add-account').on('click', function() {
        var template = `
        <div class="smtp-account-row" style="background: #f9f9f9; padding: 15px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                <div>
                    <label style="display: block; margin-bottom: 3px; font-weight: 600;"><?php echo esc_js(__('From Email', 'wp-site-notifications')); ?></label>
                    <input type="email"
                           name="post_notifications_settings[smtp][accounts][${accountIndex}][email]"
                           class="regular-text"
                           placeholder="email@example.com"
                           style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 3px; font-weight: 600;"><?php echo esc_js(__('From Name', 'wp-site-notifications')); ?></label>
                    <input type="text"
                           name="post_notifications_settings[smtp][accounts][${accountIndex}][name]"
                           class="regular-text"
                           placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>"
                           style="width: 100%;">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div>
                    <label style="display: block; margin-bottom: 3px; font-weight: 600;"><?php echo esc_js(__('SMTP Username', 'wp-site-notifications')); ?></label>
                    <input type="text"
                           name="post_notifications_settings[smtp][accounts][${accountIndex}][username]"
                           class="regular-text"
                           autocomplete="off"
                           style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 3px; font-weight: 600;"><?php echo esc_js(__('SMTP Password', 'wp-site-notifications')); ?></label>
                    <input type="password"
                           name="post_notifications_settings[smtp][accounts][${accountIndex}][password]"
                           class="regular-text"
                           autocomplete="new-password"
                           style="width: 100%;">
                </div>
            </div>
            <button type="button" class="button smtp-remove-account" style="margin-top: 10px;">
                <?php echo esc_js(__('Remove Account', 'wp-site-notifications')); ?>
            </button>
        </div>`;
        $('#smtp-accounts-container').append(template);
        accountIndex++;
    });

    // Remove account
    $(document).on('click', '.smtp-remove-account', function() {
        $(this).closest('.smtp-account-row').remove();
    });

    // Test email
    $('#smtp_send_test').on('click', function() {
        var testEmail = $('#smtp_test_email').val();
        var $button = $(this);
        var $result = $('#smtp_test_result');

        if (!testEmail) {
            $result.html('<span style="color: #d63638;"><?php echo esc_js(__('Please enter an email address.', 'wp-site-notifications')); ?></span>');
            return;
        }

        $button.prop('disabled', true);
        $result.html('<span style="color: #666;"><?php echo esc_js(__('Sending...', 'wp-site-notifications')); ?></span>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_site_notifications_test_smtp',
                email: testEmail,
                nonce: '<?php echo wp_create_nonce('wp_site_notifications_test_smtp'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<span style="color: #00a32a;">' + response.data + '</span>');
                } else {
                    $result.html('<span style="color: #d63638;">' + response.data + '</span>');
                }
            },
            error: function() {
                $result.html('<span style="color: #d63638;"><?php echo esc_js(__('Request failed. Please try again.', 'wp-site-notifications')); ?></span>');
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
});
</script>
