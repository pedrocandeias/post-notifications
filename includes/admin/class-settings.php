<?php
/**
 * Settings Page Handler
 *
 * @package Post_Notifications
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Post_Notifications_Settings
 */
class Post_Notifications_Settings {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Post Notifications Settings', 'post-notifications'),
            __('Post Notifications', 'post-notifications'),
            'manage_options',
            'post-notifications',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'post_notifications_settings_group',
            'post_notifications_settings',
            array($this, 'sanitize_settings')
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        // Verify nonce for security
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'post_notifications_settings_group-options')) {
            add_settings_error(
                'post_notifications_settings',
                'nonce_failed',
                __('Security check failed. Please try again.', 'post-notifications'),
                'error'
            );
            return get_option('post_notifications_settings', array());
        }

        // Verify user has permission
        if (!current_user_can('manage_options')) {
            add_settings_error(
                'post_notifications_settings',
                'permission_denied',
                __('You do not have permission to modify these settings.', 'post-notifications'),
                'error'
            );
            return get_option('post_notifications_settings', array());
        }

        // Sanitize enabled notifications - validate against allowed types
        $allowed_notification_types = array('pending', 'published', 'draft', 'scheduled', 'updated', 'trashed');
        if (isset($input['enabled_notifications']) && is_array($input['enabled_notifications'])) {
            $sanitized['enabled_notifications'] = array();
            foreach ($input['enabled_notifications'] as $key => $value) {
                if (in_array($key, $allowed_notification_types, true)) {
                    $sanitized['enabled_notifications'][$key] = '1';
                }
            }
        } else {
            $sanitized['enabled_notifications'] = array();
        }

        // Sanitize recipient roles - validate against actual WordPress roles
        if (isset($input['recipient_roles']) && is_array($input['recipient_roles'])) {
            $available_roles = array_keys(wp_roles()->get_names());
            $sanitized['recipient_roles'] = array();
            foreach ($input['recipient_roles'] as $role) {
                $role = sanitize_text_field($role);
                if (in_array($role, $available_roles, true)) {
                    $sanitized['recipient_roles'][] = $role;
                }
            }
        } else {
            $sanitized['recipient_roles'] = array();
        }

        // Sanitize enabled post types - validate against actual post types
        if (isset($input['enabled_post_types']) && is_array($input['enabled_post_types'])) {
            $available_post_types = get_post_types(array('public' => true), 'names');
            $sanitized['enabled_post_types'] = array();
            foreach ($input['enabled_post_types'] as $post_type) {
                $post_type = sanitize_text_field($post_type);
                if (in_array($post_type, $available_post_types, true)) {
                    $sanitized['enabled_post_types'][] = $post_type;
                }
            }
        } else {
            $sanitized['enabled_post_types'] = array('post'); // Default to standard posts
        }

        // Sanitize recipient users - validate against actual user IDs
        if (isset($input['recipient_users']) && is_array($input['recipient_users'])) {
            $sanitized['recipient_users'] = array();
            foreach ($input['recipient_users'] as $user_id) {
                $user_id = absint($user_id);
                // Verify user exists
                if ($user_id > 0 && get_userdata($user_id)) {
                    $sanitized['recipient_users'][] = $user_id;
                }
            }
        } else {
            $sanitized['recipient_users'] = array();
        }

        return $sanitized;
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get current settings
        $settings = get_option('post_notifications_settings', array());
        $enabled_notifications = isset($settings['enabled_notifications']) ? $settings['enabled_notifications'] : array();
        $recipient_roles = isset($settings['recipient_roles']) ? $settings['recipient_roles'] : array('administrator');
        $recipient_users = isset($settings['recipient_users']) ? $settings['recipient_users'] : array();
        $enabled_post_types = isset($settings['enabled_post_types']) ? $settings['enabled_post_types'] : array('post');

        // Get active tab
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'posts';

        // Include the view
        include POST_NOTIFICATIONS_PLUGIN_DIR . 'includes/admin/views/settings-page.php';
    }
}
