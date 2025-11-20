<?php
/**
 * Settings Page View
 *
 * @package Post_Notifications
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors('post_notifications_settings'); ?>

    <!-- Tab Navigation -->
    <h2 class="nav-tab-wrapper">
        <a href="?page=wp-site-notifications&tab=posts" class="nav-tab <?php echo $active_tab === 'posts' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Posts', 'wp-site-notifications'); ?>
        </a>
        <a href="?page=wp-site-notifications&tab=admin" class="nav-tab <?php echo $active_tab === 'admin' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Admin', 'wp-site-notifications'); ?>
        </a>
        <a href="?page=wp-site-notifications&tab=smtp" class="nav-tab <?php echo $active_tab === 'smtp' ? 'nav-tab-active' : ''; ?>">
            <?php _e('SMTP', 'wp-site-notifications'); ?>
        </a>
    </h2>

    <style>
        .post-notifications-tab-content {
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            border-top: none;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
    </style>

    <div class="post-notifications-tab-content">
        <form method="post" action="options.php">
            <?php settings_fields('post_notifications_settings_group'); ?>

            <?php if ($active_tab === 'posts') : ?>
                <?php include POST_NOTIFICATIONS_PLUGIN_DIR . 'includes/admin/views/tab-posts.php'; ?>
            <?php endif; ?>

            <?php if ($active_tab === 'admin') : ?>
                <?php include POST_NOTIFICATIONS_PLUGIN_DIR . 'includes/admin/views/tab-admin.php'; ?>
            <?php endif; ?>

            <?php if ($active_tab === 'smtp') : ?>
                <?php include POST_NOTIFICATIONS_PLUGIN_DIR . 'includes/admin/views/tab-smtp.php'; ?>
            <?php endif; ?>

            <?php submit_button(__('Save Settings', 'wp-site-notifications')); ?>
        </form>
    </div><!-- .post-notifications-tab-content -->

    <script>
    (function() {
        var searchInput = document.getElementById('post-notifications-user-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                var searchTerm = this.value.toLowerCase();
                var userItems = document.querySelectorAll('.post-notifications-user-item');

                userItems.forEach(function(item) {
                    var name = item.getAttribute('data-name') || '';
                    var email = item.getAttribute('data-email') || '';

                    if (name.indexOf(searchTerm) !== -1 || email.indexOf(searchTerm) !== -1) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
    })();
    </script>
</div><!-- .wrap -->
