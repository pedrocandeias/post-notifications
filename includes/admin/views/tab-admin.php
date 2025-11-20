<?php
/**
 * Admin Tab View
 *
 * @package WP_Site_Notifications
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get admin notification settings
$admin_notifications = isset($settings['admin_notifications']) ? $settings['admin_notifications'] : array();
?>

<!-- Admin Tab Content -->
<style>
    .wp-notifications-admin-group {
        margin-bottom: 20px;
        padding: 15px;
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
    }
    .wp-notifications-admin-group h3 {
        margin: 0 0 10px 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }
    .wp-notifications-admin-group .group-email {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f0f0;
    }
    .wp-notifications-admin-group .group-email label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }
    .wp-notifications-admin-group .group-email input[type="email"] {
        width: 100%;
        max-width: 300px;
    }
    .wp-notifications-admin-group .notification-options {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .wp-notifications-admin-group .notification-options label {
        display: inline-flex;
        align-items: center;
    }
    .wp-notifications-admin-group .notification-options input[type="checkbox"] {
        margin-right: 5px;
    }
</style>

<p class="description" style="margin-bottom: 20px;">
    <?php _e('Configure notifications for administrative actions. Each group has one email address that receives all enabled notifications for that category.', 'wp-site-notifications'); ?>
</p>

<!-- User Management Notifications -->
<div class="wp-notifications-admin-group">
    <h3><?php _e('User Management', 'wp-site-notifications'); ?></h3>

    <div class="group-email">
        <label for="admin_notifications_user_management_email"><?php _e('Email Address', 'wp-site-notifications'); ?></label>
        <input type="email"
               id="admin_notifications_user_management_email"
               name="post_notifications_settings[admin_notifications][user_management_email]"
               value="<?php echo esc_attr(isset($admin_notifications['user_management_email']) ? $admin_notifications['user_management_email'] : ''); ?>"
               placeholder="<?php esc_attr_e('Email address for user notifications', 'wp-site-notifications'); ?>">
    </div>

    <div class="notification-options">
        <label>
            <input type="checkbox"
                   name="post_notifications_settings[admin_notifications][user_registered][enabled]"
                   value="1"
                   <?php checked(!empty($admin_notifications['user_registered']['enabled'])); ?>>
            <?php _e('New user registration', 'wp-site-notifications'); ?>
        </label>

        <label>
            <input type="checkbox"
                   name="post_notifications_settings[admin_notifications][user_deleted][enabled]"
                   value="1"
                   <?php checked(!empty($admin_notifications['user_deleted']['enabled'])); ?>>
            <?php _e('User deleted', 'wp-site-notifications'); ?>
        </label>

        <label>
            <input type="checkbox"
                   name="post_notifications_settings[admin_notifications][user_role_changed][enabled]"
                   value="1"
                   <?php checked(!empty($admin_notifications['user_role_changed']['enabled'])); ?>>
            <?php _e('User role changed', 'wp-site-notifications'); ?>
        </label>
    </div>
</div>

<!-- Plugin Management Notifications -->
<div class="wp-notifications-admin-group">
    <h3><?php _e('Plugin Management', 'wp-site-notifications'); ?></h3>

    <div class="group-email">
        <label for="admin_notifications_plugin_management_email"><?php _e('Email Address', 'wp-site-notifications'); ?></label>
        <input type="email"
               id="admin_notifications_plugin_management_email"
               name="post_notifications_settings[admin_notifications][plugin_management_email]"
               value="<?php echo esc_attr(isset($admin_notifications['plugin_management_email']) ? $admin_notifications['plugin_management_email'] : ''); ?>"
               placeholder="<?php esc_attr_e('Email address for plugin notifications', 'wp-site-notifications'); ?>">
    </div>

    <div class="notification-options">
        <label>
            <input type="checkbox"
                   name="post_notifications_settings[admin_notifications][plugin_activated][enabled]"
                   value="1"
                   <?php checked(!empty($admin_notifications['plugin_activated']['enabled'])); ?>>
            <?php _e('Plugin activated', 'wp-site-notifications'); ?>
        </label>

        <label>
            <input type="checkbox"
                   name="post_notifications_settings[admin_notifications][plugin_deactivated][enabled]"
                   value="1"
                   <?php checked(!empty($admin_notifications['plugin_deactivated']['enabled'])); ?>>
            <?php _e('Plugin deactivated', 'wp-site-notifications'); ?>
        </label>

        <label>
            <input type="checkbox"
                   name="post_notifications_settings[admin_notifications][plugin_updated][enabled]"
                   value="1"
                   <?php checked(!empty($admin_notifications['plugin_updated']['enabled'])); ?>>
            <?php _e('Plugin updated', 'wp-site-notifications'); ?>
        </label>
    </div>
</div>

<!-- Theme Management Notifications -->
<div class="wp-notifications-admin-group">
    <h3><?php _e('Theme Management', 'wp-site-notifications'); ?></h3>

    <div class="group-email">
        <label for="admin_notifications_theme_management_email"><?php _e('Email Address', 'wp-site-notifications'); ?></label>
        <input type="email"
               id="admin_notifications_theme_management_email"
               name="post_notifications_settings[admin_notifications][theme_management_email]"
               value="<?php echo esc_attr(isset($admin_notifications['theme_management_email']) ? $admin_notifications['theme_management_email'] : ''); ?>"
               placeholder="<?php esc_attr_e('Email address for theme notifications', 'wp-site-notifications'); ?>">
    </div>

    <div class="notification-options">
        <label>
            <input type="checkbox"
                   name="post_notifications_settings[admin_notifications][theme_switched][enabled]"
                   value="1"
                   <?php checked(!empty($admin_notifications['theme_switched']['enabled'])); ?>>
            <?php _e('Theme switched', 'wp-site-notifications'); ?>
        </label>

        <label>
            <input type="checkbox"
                   name="post_notifications_settings[admin_notifications][theme_updated][enabled]"
                   value="1"
                   <?php checked(!empty($admin_notifications['theme_updated']['enabled'])); ?>>
            <?php _e('Theme updated', 'wp-site-notifications'); ?>
        </label>
    </div>
</div>

<!-- Core Updates Notifications -->
<div class="wp-notifications-admin-group">
    <h3><?php _e('Core Updates', 'wp-site-notifications'); ?></h3>

    <div class="group-email">
        <label for="admin_notifications_core_updates_email"><?php _e('Email Address', 'wp-site-notifications'); ?></label>
        <input type="email"
               id="admin_notifications_core_updates_email"
               name="post_notifications_settings[admin_notifications][core_updates_email]"
               value="<?php echo esc_attr(isset($admin_notifications['core_updates_email']) ? $admin_notifications['core_updates_email'] : ''); ?>"
               placeholder="<?php esc_attr_e('Email address for core update notifications', 'wp-site-notifications'); ?>">
    </div>

    <div class="notification-options">
        <label>
            <input type="checkbox"
                   name="post_notifications_settings[admin_notifications][core_updated][enabled]"
                   value="1"
                   <?php checked(!empty($admin_notifications['core_updated']['enabled'])); ?>>
            <?php _e('WordPress core updated', 'wp-site-notifications'); ?>
        </label>
    </div>
</div>

<!-- Security Notifications -->
<div class="wp-notifications-admin-group">
    <h3><?php _e('Security', 'wp-site-notifications'); ?></h3>

    <div class="group-email">
        <label for="admin_notifications_security_email"><?php _e('Email Address', 'wp-site-notifications'); ?></label>
        <input type="email"
               id="admin_notifications_security_email"
               name="post_notifications_settings[admin_notifications][security_email]"
               value="<?php echo esc_attr(isset($admin_notifications['security_email']) ? $admin_notifications['security_email'] : ''); ?>"
               placeholder="<?php esc_attr_e('Email address for security notifications', 'wp-site-notifications'); ?>">
    </div>

    <div class="notification-options">
        <label>
            <input type="checkbox"
                   name="post_notifications_settings[admin_notifications][failed_login][enabled]"
                   value="1"
                   <?php checked(!empty($admin_notifications['failed_login']['enabled'])); ?>>
            <?php _e('Failed login attempt', 'wp-site-notifications'); ?>
        </label>

        <label>
            <input type="checkbox"
                   name="post_notifications_settings[admin_notifications][password_reset][enabled]"
                   value="1"
                   <?php checked(!empty($admin_notifications['password_reset']['enabled'])); ?>>
            <?php _e('Password reset requested', 'wp-site-notifications'); ?>
        </label>
    </div>
</div>
