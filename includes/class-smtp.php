<?php
/**
 * SMTP Handler
 *
 * @package WP_Site_Notifications
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WP_Site_Notifications_SMTP
 */
class WP_Site_Notifications_SMTP {

    /**
     * Current account being used for sending
     */
    private $current_account = null;

    /**
     * Constructor
     */
    public function __construct() {
        add_action('phpmailer_init', array($this, 'configure_smtp'), 10, 1);
        add_filter('wp_mail_from', array($this, 'set_from_email'));
        add_filter('wp_mail_from_name', array($this, 'set_from_name'));
        add_action('wp_ajax_wp_site_notifications_test_smtp', array($this, 'send_test_email'));
    }

    /**
     * Get SMTP settings
     */
    private function get_smtp_settings() {
        $settings = get_option('post_notifications_settings', array());
        return isset($settings['smtp']) ? $settings['smtp'] : array();
    }

    /**
     * Check if SMTP is enabled
     */
    private function is_smtp_enabled() {
        $smtp = $this->get_smtp_settings();
        return !empty($smtp['enabled']) && !empty($smtp['host']);
    }

    /**
     * Get account by email
     */
    private function get_account_by_email($email) {
        $smtp = $this->get_smtp_settings();
        $accounts = isset($smtp['accounts']) ? $smtp['accounts'] : array();

        foreach ($accounts as $account) {
            if (isset($account['email']) && $account['email'] === $email) {
                return $account;
            }
        }

        return null;
    }

    /**
     * Get default account
     */
    private function get_default_account() {
        $smtp = $this->get_smtp_settings();
        $accounts = isset($smtp['accounts']) ? $smtp['accounts'] : array();
        $default_email = isset($smtp['default_account']) ? $smtp['default_account'] : '';

        // Try to find the default account
        if (!empty($default_email)) {
            $account = $this->get_account_by_email($default_email);
            if ($account) {
                return $account;
            }
        }

        // Fall back to first account
        if (!empty($accounts)) {
            return reset($accounts);
        }

        return null;
    }

    /**
     * Set the account to use for next email
     * Can be called before wp_mail() to specify which account to use
     */
    public function set_account($email) {
        $this->current_account = $this->get_account_by_email($email);
    }

    /**
     * Configure PHPMailer to use SMTP
     */
    public function configure_smtp($phpmailer) {
        if (!$this->is_smtp_enabled()) {
            return;
        }

        $smtp = $this->get_smtp_settings();

        // Set mailer to SMTP
        $phpmailer->isSMTP();

        // Set SMTP host
        $phpmailer->Host = $smtp['host'];

        // Set SMTP port
        $phpmailer->Port = !empty($smtp['port']) ? intval($smtp['port']) : 587;

        // Set encryption
        if (!empty($smtp['encryption'])) {
            $phpmailer->SMTPSecure = $smtp['encryption'];
        } else {
            $phpmailer->SMTPSecure = '';
            $phpmailer->SMTPAutoTLS = false;
        }

        // Get the account to use
        $account = $this->current_account;
        if (!$account) {
            // Try to find account by current From address
            $account = $this->get_account_by_email($phpmailer->From);
        }
        if (!$account) {
            // Fall back to default account
            $account = $this->get_default_account();
        }

        // Set authentication if we have an account
        if (!empty($smtp['auth']) && $account) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = isset($account['username']) ? $account['username'] : '';
            $phpmailer->Password = isset($account['password']) ? $account['password'] : '';

            // Set from address from account
            if (!empty($account['email'])) {
                $phpmailer->From = $account['email'];
            }

            // Set from name from account
            if (!empty($account['name'])) {
                $phpmailer->FromName = $account['name'];
            }
        } else {
            $phpmailer->SMTPAuth = false;
        }

        // Reset current account after use
        $this->current_account = null;
    }

    /**
     * Set from email
     */
    public function set_from_email($email) {
        if (!$this->is_smtp_enabled()) {
            return $email;
        }

        // If current account is set, use its email
        if ($this->current_account && !empty($this->current_account['email'])) {
            return $this->current_account['email'];
        }

        // Check if email matches any account
        $account = $this->get_account_by_email($email);
        if ($account) {
            return $email;
        }

        // Use default account
        $default_account = $this->get_default_account();
        if ($default_account && !empty($default_account['email'])) {
            return $default_account['email'];
        }

        return $email;
    }

    /**
     * Set from name
     */
    public function set_from_name($name) {
        if (!$this->is_smtp_enabled()) {
            return $name;
        }

        // If current account is set, use its name
        if ($this->current_account && !empty($this->current_account['name'])) {
            return $this->current_account['name'];
        }

        // Use default account name if no name provided
        $default_account = $this->get_default_account();
        if ($default_account && !empty($default_account['name'])) {
            return $default_account['name'];
        }

        return $name;
    }

    /**
     * Send test email via AJAX
     */
    public function send_test_email() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_site_notifications_test_smtp')) {
            wp_send_json_error(__('Security check failed.', 'wp-site-notifications'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-site-notifications'));
        }

        // Get test email address
        $to = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

        if (empty($to)) {
            wp_send_json_error(__('Please enter a valid email address.', 'wp-site-notifications'));
        }

        // Prepare test email
        $subject = sprintf(__('[%s] SMTP Test Email', 'wp-site-notifications'), get_bloginfo('name'));

        $message = '<html><body>';
        $message .= '<h2>' . __('SMTP Test Email', 'wp-site-notifications') . '</h2>';
        $message .= '<p>' . __('This is a test email to verify your SMTP settings are configured correctly.', 'wp-site-notifications') . '</p>';
        $message .= '<p><strong>' . __('Site:', 'wp-site-notifications') . '</strong> ' . get_bloginfo('name') . '</p>';
        $message .= '<p><strong>' . __('URL:', 'wp-site-notifications') . '</strong> ' . home_url() . '</p>';
        $message .= '<p><strong>' . __('Date:', 'wp-site-notifications') . '</strong> ' . current_time('mysql') . '</p>';
        $message .= '<hr>';
        $message .= '<p style="color: #666; font-size: 12px;">' . __('This email was sent from WP Site Notifications plugin.', 'wp-site-notifications') . '</p>';
        $message .= '</body></html>';

        $headers = array('Content-Type: text/html; charset=UTF-8');

        // Try to send the email
        $result = wp_mail($to, $subject, $message, $headers);

        if ($result) {
            wp_send_json_success(__('Test email sent successfully! Please check your inbox.', 'wp-site-notifications'));
        } else {
            global $phpmailer;
            $error_message = __('Failed to send test email.', 'wp-site-notifications');

            if (isset($phpmailer) && !empty($phpmailer->ErrorInfo)) {
                $error_message .= ' ' . __('Error:', 'wp-site-notifications') . ' ' . $phpmailer->ErrorInfo;
            }

            wp_send_json_error($error_message);
        }
    }
}
