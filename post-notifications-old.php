<?php
/**
 * Plugin Name: Post Notifications
 * Plugin URI: https://github.com/pedrocandeias/post-notifications
 * Description: Send customizable email notifications to selected user roles when post status changes (pending, published, etc.)
 * Version: 1.0.0
 * Author: Pedro Candeias
 * Author URI: https://pedrocandeias.net
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: post-notifications
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
if (!defined('POST_NOTIFICATIONS_VERSION')) {
    define('POST_NOTIFICATIONS_VERSION', '1.0.0');
}
if (!defined('POST_NOTIFICATIONS_PLUGIN_DIR')) {
    define('POST_NOTIFICATIONS_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('POST_NOTIFICATIONS_PLUGIN_URL')) {
    define('POST_NOTIFICATIONS_PLUGIN_URL', plugin_dir_url(__FILE__));
}

/**
 * Main Plugin Class
 */
class Post_Notifications {

    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Load text domain for translations
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));

        // Post status transition hooks
        add_action('transition_post_status', array($this, 'handle_post_status_change'), 10, 3);

        // Activation and uninstall hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_uninstall_hook(__FILE__, array('Post_Notifications', 'uninstall'));
    }

    /**
     * Load plugin text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'post-notifications',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        $default_settings = array(
            'enabled_notifications' => array(
                'pending' => '1',
                'published' => '1',
                'draft' => '0',
                'scheduled' => '1',
                'updated' => '0',
            ),
            'recipient_roles' => array('administrator'),
            'recipient_users' => array(), // Individual users
            'enabled_post_types' => array('post'), // Default to standard posts
        );

        if (!get_option('post_notifications_settings')) {
            add_option('post_notifications_settings', $default_settings);
        }
    }

    /**
     * Plugin uninstall - cleanup
     */
    public static function uninstall() {
        delete_option('post_notifications_settings');
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

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php settings_errors('post_notifications_settings'); ?>

            <!-- Tab Navigation -->
            <h2 class="nav-tab-wrapper">
                <a href="?page=post-notifications&tab=posts" class="nav-tab <?php echo $active_tab === 'posts' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Posts', 'post-notifications'); ?>
                </a>
                <a href="?page=post-notifications&tab=admin" class="nav-tab <?php echo $active_tab === 'admin' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Admin', 'post-notifications'); ?>
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
                <?php
                settings_fields('post_notifications_settings_group');
                ?>

                <?php if ($active_tab === 'posts') : ?>
                <!-- Posts Tab Content -->
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label><?php _e('Notification Types', 'post-notifications'); ?></label>
                        </th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text">
                                    <span><?php _e('Notification Types', 'post-notifications'); ?></span>
                                </legend>

                                <label>
                                    <input type="checkbox"
                                           name="post_notifications_settings[enabled_notifications][pending]"
                                           value="1"
                                           <?php checked(isset($enabled_notifications['pending']) && $enabled_notifications['pending'] == '1'); ?>>
                                    <?php _e('Post submitted for review (Pending)', 'post-notifications'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox"
                                           name="post_notifications_settings[enabled_notifications][published]"
                                           value="1"
                                           <?php checked(isset($enabled_notifications['published']) && $enabled_notifications['published'] == '1'); ?>>
                                    <?php _e('Post published', 'post-notifications'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox"
                                           name="post_notifications_settings[enabled_notifications][draft]"
                                           value="1"
                                           <?php checked(isset($enabled_notifications['draft']) && $enabled_notifications['draft'] == '1'); ?>>
                                    <?php _e('Post saved as draft', 'post-notifications'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox"
                                           name="post_notifications_settings[enabled_notifications][scheduled]"
                                           value="1"
                                           <?php checked(isset($enabled_notifications['scheduled']) && $enabled_notifications['scheduled'] == '1'); ?>>
                                    <?php _e('Post scheduled for future publication', 'post-notifications'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox"
                                           name="post_notifications_settings[enabled_notifications][updated]"
                                           value="1"
                                           <?php checked(isset($enabled_notifications['updated']) && $enabled_notifications['updated'] == '1'); ?>>
                                    <?php _e('Published post updated', 'post-notifications'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox"
                                           name="post_notifications_settings[enabled_notifications][trashed]"
                                           value="1"
                                           <?php checked(isset($enabled_notifications['trashed']) && $enabled_notifications['trashed'] == '1'); ?>>
                                    <?php _e('Post moved to trash', 'post-notifications'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label><?php _e('Recipient Roles', 'post-notifications'); ?></label>
                        </th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text">
                                    <span><?php _e('Recipient Roles', 'post-notifications'); ?></span>
                                </legend>
                                <?php
                                // Get all roles including custom ones
                                $available_roles = wp_roles()->get_names();
                                $wp_roles_obj = wp_roles();

                                // WordPress built-in roles
                                $builtin_roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');

                                // Separate built-in and custom roles
                                $builtin_list = array();
                                $custom_list = array();

                                foreach ($available_roles as $role_key => $role_name) {
                                    if (in_array($role_key, $builtin_roles, true)) {
                                        $builtin_list[$role_key] = $role_name;
                                    } else {
                                        $custom_list[$role_key] = $role_name;
                                    }
                                }

                                // Display built-in roles
                                if (!empty($builtin_list)) :
                                ?>
                                    <div style="margin-bottom: 15px;">
                                        <strong style="display: block; margin-bottom: 8px;"><?php _e('WordPress Built-in Roles:', 'post-notifications'); ?></strong>
                                        <?php foreach ($builtin_list as $role_key => $role_name) :
                                            $role_obj = $wp_roles_obj->get_role($role_key);
                                            $user_count = count(get_users(array('role' => $role_key, 'fields' => 'ID')));
                                        ?>
                                            <label style="display: block; margin-bottom: 5px;">
                                                <input type="checkbox"
                                                       name="post_notifications_settings[recipient_roles][]"
                                                       value="<?php echo esc_attr($role_key); ?>"
                                                       <?php checked(in_array($role_key, $recipient_roles)); ?>>
                                                <?php echo esc_html($role_name); ?>
                                                <span style="color: #666; font-size: 0.9em;">
                                                    (<?php echo esc_html(sprintf(_n('%d user', '%d users', $user_count, 'post-notifications'), $user_count)); ?>)
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php
                                // Display custom roles
                                if (!empty($custom_list)) :
                                ?>
                                    <div style="margin-bottom: 10px; padding: 10px; background: #f0f8ff; border-left: 4px solid #0073aa;">
                                        <strong style="display: block; margin-bottom: 8px;">
                                            <?php _e('Custom Roles:', 'post-notifications'); ?>
                                            <span style="color: #0073aa; font-weight: normal; font-size: 0.9em;">
                                                <?php _e('(Added by plugins or theme)', 'post-notifications'); ?>
                                            </span>
                                        </strong>
                                        <?php foreach ($custom_list as $role_key => $role_name) :
                                            $role_obj = $wp_roles_obj->get_role($role_key);
                                            $user_count = count(get_users(array('role' => $role_key, 'fields' => 'ID')));
                                        ?>
                                            <label style="display: block; margin-bottom: 5px;">
                                                <input type="checkbox"
                                                       name="post_notifications_settings[recipient_roles][]"
                                                       value="<?php echo esc_attr($role_key); ?>"
                                                       <?php checked(in_array($role_key, $recipient_roles)); ?>>
                                                <?php echo esc_html($role_name); ?>
                                                <span style="color: #666; font-size: 0.9em;">
                                                    (<?php echo esc_html(sprintf(_n('%d user', '%d users', $user_count, 'post-notifications'), $user_count)); ?>)
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <p class="description">
                                    <?php _e('Select which user roles should receive email notifications. Custom roles are automatically detected.', 'post-notifications'); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label><?php _e('Individual Users', 'post-notifications'); ?></label>
                        </th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text">
                                    <span><?php _e('Individual Users', 'post-notifications'); ?></span>
                                </legend>

                                <input type="text"
                                       id="post-notifications-user-search"
                                       placeholder="<?php esc_attr_e('Search users...', 'post-notifications'); ?>"
                                       style="width: 100%; max-width: 400px; margin-bottom: 10px; padding: 5px;">

                                <div id="post-notifications-user-list" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff;">
                                    <?php
                                    // Get all users ordered by display name
                                    $all_users = get_users(array(
                                        'orderby' => 'display_name',
                                        'order' => 'ASC',
                                        'fields' => array('ID', 'display_name', 'user_email', 'user_login')
                                    ));

                                    if (!empty($all_users)) :
                                        foreach ($all_users as $user) :
                                            $user_roles = get_userdata($user->ID)->roles;
                                            $role_names = array();
                                            foreach ($user_roles as $role_key) {
                                                $role_obj = get_role($role_key);
                                                if ($role_obj) {
                                                    $wp_roles = wp_roles();
                                                    $role_names[] = isset($wp_roles->role_names[$role_key]) ? translate_user_role($wp_roles->role_names[$role_key]) : $role_key;
                                                }
                                            }
                                            $role_display = !empty($role_names) ? implode(', ', $role_names) : __('No role', 'post-notifications');
                                    ?>
                                        <label class="post-notifications-user-item"
                                               data-name="<?php echo esc_attr(strtolower($user->display_name)); ?>"
                                               data-email="<?php echo esc_attr(strtolower($user->user_email)); ?>"
                                               style="display: block; margin-bottom: 8px; padding: 5px; background: #f9f9f9; border-radius: 3px;">
                                            <input type="checkbox"
                                                   name="post_notifications_settings[recipient_users][]"
                                                   value="<?php echo esc_attr($user->ID); ?>"
                                                   <?php checked(in_array($user->ID, $recipient_users)); ?>>
                                            <strong><?php echo esc_html($user->display_name); ?></strong>
                                            <span style="color: #666; font-size: 0.9em;">
                                                (<?php echo esc_html($user->user_email); ?>)
                                            </span>
                                            <br>
                                            <span style="margin-left: 24px; color: #999; font-size: 0.85em;">
                                                <?php echo esc_html($role_display); ?>
                                            </span>
                                        </label>
                                    <?php
                                        endforeach;
                                    else :
                                    ?>
                                        <p><?php _e('No users found.', 'post-notifications'); ?></p>
                                    <?php endif; ?>
                                </div>

                                <p class="description" style="margin-top: 10px;">
                                    <?php _e('Select individual users who should receive notifications in addition to users selected by role. These users will receive notifications regardless of their role.', 'post-notifications'); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label><?php _e('Post Types', 'post-notifications'); ?></label>
                        </th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text">
                                    <span><?php _e('Post Types', 'post-notifications'); ?></span>
                                </legend>
                                <?php
                                // Get all public post types
                                $post_types = get_post_types(array('public' => true), 'objects');

                                // WordPress built-in post types
                                $builtin_types = array('post', 'page');

                                // Separate built-in and custom post types
                                $builtin_list = array();
                                $custom_list = array();

                                foreach ($post_types as $post_type_key => $post_type_obj) {
                                    if (in_array($post_type_key, $builtin_types, true)) {
                                        $builtin_list[$post_type_key] = $post_type_obj;
                                    } else {
                                        $custom_list[$post_type_key] = $post_type_obj;
                                    }
                                }

                                // Display built-in post types
                                if (!empty($builtin_list)) :
                                ?>
                                    <div style="margin-bottom: 15px;">
                                        <strong style="display: block; margin-bottom: 8px;"><?php _e('WordPress Built-in Post Types:', 'post-notifications'); ?></strong>
                                        <?php foreach ($builtin_list as $post_type_key => $post_type_obj) :
                                            $count_posts = wp_count_posts($post_type_key);
                                            $total_posts = 0;
                                            foreach ($count_posts as $status => $count) {
                                                $total_posts += $count;
                                            }
                                        ?>
                                            <label style="display: block; margin-bottom: 5px;">
                                                <input type="checkbox"
                                                       name="post_notifications_settings[enabled_post_types][]"
                                                       value="<?php echo esc_attr($post_type_key); ?>"
                                                       <?php checked(in_array($post_type_key, $enabled_post_types)); ?>>
                                                <?php echo esc_html($post_type_obj->labels->name); ?>
                                                <span style="color: #666; font-size: 0.9em;">
                                                    (<?php echo esc_html(sprintf(_n('%d item', '%d items', $total_posts, 'post-notifications'), $total_posts)); ?>)
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php
                                // Display custom post types
                                if (!empty($custom_list)) :
                                ?>
                                    <div style="margin-bottom: 10px; padding: 10px; background: #f0f8ff; border-left: 4px solid #0073aa;">
                                        <strong style="display: block; margin-bottom: 8px;">
                                            <?php _e('Custom Post Types:', 'post-notifications'); ?>
                                            <span style="color: #0073aa; font-weight: normal; font-size: 0.9em;">
                                                <?php _e('(Added by plugins or theme)', 'post-notifications'); ?>
                                            </span>
                                        </strong>
                                        <?php foreach ($custom_list as $post_type_key => $post_type_obj) :
                                            $count_posts = wp_count_posts($post_type_key);
                                            $total_posts = 0;
                                            foreach ($count_posts as $status => $count) {
                                                $total_posts += $count;
                                            }
                                        ?>
                                            <label style="display: block; margin-bottom: 5px;">
                                                <input type="checkbox"
                                                       name="post_notifications_settings[enabled_post_types][]"
                                                       value="<?php echo esc_attr($post_type_key); ?>"
                                                       <?php checked(in_array($post_type_key, $enabled_post_types)); ?>>
                                                <?php echo esc_html($post_type_obj->labels->name); ?>
                                                <span style="color: #666; font-size: 0.9em;">
                                                    (<?php echo esc_html(sprintf(_n('%d item', '%d items', $total_posts, 'post-notifications'), $total_posts)); ?>)
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <p class="description">
                                    <?php _e('Select which post types should trigger notifications. Both standard and custom post types are shown.', 'post-notifications'); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                <?php endif; ?>

                <?php if ($active_tab === 'admin') : ?>
                <!-- Admin Tab Content -->
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label><?php _e('Admin Settings', 'post-notifications'); ?></label>
                        </th>
                        <td>
                            <p class="description">
                                <?php _e('Additional administrative settings will be available here.', 'post-notifications'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php endif; ?>

                <?php submit_button(__('Save Settings', 'post-notifications')); ?>
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
        <?php
    }

    /**
     * Handle post status changes
     */
    public function handle_post_status_change($new_status, $old_status, $post) {
        // Avoid sending notifications during autosave or revisions
        if (wp_is_post_autosave($post) || wp_is_post_revision($post)) {
            return;
        }

        // Get settings
        $settings = get_option('post_notifications_settings', array());
        $enabled_notifications = isset($settings['enabled_notifications']) ? $settings['enabled_notifications'] : array();
        $recipient_roles = isset($settings['recipient_roles']) ? $settings['recipient_roles'] : array();
        $recipient_users = isset($settings['recipient_users']) ? $settings['recipient_users'] : array();
        $enabled_post_types = isset($settings['enabled_post_types']) ? $settings['enabled_post_types'] : array('post');

        // Only handle enabled post types
        if (!in_array($post->post_type, $enabled_post_types, true)) {
            return;
        }

        // Check if we should send notification for this status change
        $notification_type = null;

        if ($old_status !== 'pending' && $new_status === 'pending' && isset($enabled_notifications['pending']) && $enabled_notifications['pending'] == '1') {
            $notification_type = 'pending';
        } elseif ($old_status !== 'publish' && $new_status === 'publish' && isset($enabled_notifications['published']) && $enabled_notifications['published'] == '1') {
            $notification_type = 'published';
        } elseif ($new_status === 'draft' && $old_status !== 'draft' && $old_status !== 'auto-draft' && isset($enabled_notifications['draft']) && $enabled_notifications['draft'] == '1') {
            $notification_type = 'draft';
        } elseif ($new_status === 'future' && $old_status !== 'future' && isset($enabled_notifications['scheduled']) && $enabled_notifications['scheduled'] == '1') {
            $notification_type = 'scheduled';
        } elseif ($old_status === 'publish' && $new_status === 'publish' && isset($enabled_notifications['updated']) && $enabled_notifications['updated'] == '1') {
            // Rate limit updated notifications - only send once per hour per post
            $last_notification = get_transient('post_notification_sent_' . $post->ID);
            if ($last_notification !== false) {
                return; // Already sent notification recently
            }
            $notification_type = 'updated';
            set_transient('post_notification_sent_' . $post->ID, time(), HOUR_IN_SECONDS);
        } elseif ($new_status === 'trash' && isset($enabled_notifications['trashed']) && $enabled_notifications['trashed'] == '1') {
            $notification_type = 'trashed';
        }

        // Send notification if applicable
        if ($notification_type && (!empty($recipient_roles) || !empty($recipient_users))) {
            $this->send_notification($post, $notification_type, $recipient_roles, $recipient_users);
        }
    }

    /**
     * Send notification email
     */
    private function send_notification($post, $notification_type, $recipient_roles, $recipient_users = array()) {
        // Get recipients from roles
        $recipients = $this->get_users_by_roles($recipient_roles);

        // Add individual users
        if (!empty($recipient_users)) {
            $individual_users = $this->get_users_by_ids($recipient_users);
            $recipients = array_merge($recipients, $individual_users);
        }

        // Remove duplicates based on user ID
        $unique_recipients = array();
        $user_ids = array();
        foreach ($recipients as $recipient) {
            if (!in_array($recipient->ID, $user_ids, true)) {
                $unique_recipients[] = $recipient;
                $user_ids[] = $recipient->ID;
            }
        }

        if (empty($unique_recipients)) {
            return;
        }

        $recipients = $unique_recipients;

        // Get post author
        $author = get_userdata($post->post_author);
        $author_name = $author ? $author->display_name : __('Unknown', 'post-notifications');

        // Build email content
        $subject = $this->get_email_subject($notification_type, $post);
        $message = $this->get_email_message($notification_type, $post, $author_name);

        // Allow filtering of subject and message
        $subject = apply_filters('post_notifications_email_subject', $subject, $notification_type, $post);
        $message = apply_filters('post_notifications_email_message', $message, $notification_type, $post, $author_name);
        $recipients = apply_filters('post_notifications_recipients', $recipients, $notification_type, $post);

        // Set email headers
        $headers = array('Content-Type: text/html; charset=UTF-8');

        // Send email to each recipient
        foreach ($recipients as $recipient) {
            wp_mail($recipient->user_email, $subject, $message, $headers);
        }
    }

    /**
     * Get users by roles
     */
    private function get_users_by_roles($roles) {
        $users = array();

        // Validate roles against actual WordPress roles
        $available_roles = array_keys(wp_roles()->get_names());

        foreach ($roles as $role) {
            // Security: Only query for valid roles
            if (!in_array($role, $available_roles, true)) {
                continue;
            }

            $role_users = get_users(array(
                'role' => $role,
                'fields' => array('ID', 'user_email', 'display_name'),
            ));

            $users = array_merge($users, $role_users);
        }

        // Remove duplicates based on user ID
        $unique_users = array();
        $user_ids = array();

        foreach ($users as $user) {
            if (!in_array($user->ID, $user_ids, true)) {
                $unique_users[] = $user;
                $user_ids[] = $user->ID;
            }
        }

        return $unique_users;
    }

    /**
     * Get users by IDs
     */
    private function get_users_by_ids($user_ids) {
        $users = array();

        if (empty($user_ids)) {
            return $users;
        }

        foreach ($user_ids as $user_id) {
            $user_id = absint($user_id);
            if ($user_id > 0) {
                $user = get_userdata($user_id);
                if ($user) {
                    // Create user object with same structure as get_users output
                    $user_obj = new stdClass();
                    $user_obj->ID = $user->ID;
                    $user_obj->user_email = $user->user_email;
                    $user_obj->display_name = $user->display_name;
                    $users[] = $user_obj;
                }
            }
        }

        return $users;
    }

    /**
     * Get email subject
     */
    private function get_email_subject($notification_type, $post) {
        $site_name = get_bloginfo('name');
        $post_title = $post->post_title ? $post->post_title : __('(no title)', 'post-notifications');

        // Security: Remove newlines from title to prevent email header injection
        $post_title = str_replace(array("\r", "\n", "\r\n"), ' ', $post_title);

        $subjects = array(
            'pending' => sprintf(__('[%s] New post pending review: %s', 'post-notifications'), $site_name, $post_title),
            'published' => sprintf(__('[%s] Post published: %s', 'post-notifications'), $site_name, $post_title),
            'draft' => sprintf(__('[%s] Post saved as draft: %s', 'post-notifications'), $site_name, $post_title),
            'scheduled' => sprintf(__('[%s] Post scheduled: %s', 'post-notifications'), $site_name, $post_title),
            'updated' => sprintf(__('[%s] Post updated: %s', 'post-notifications'), $site_name, $post_title),
            'trashed' => sprintf(__('[%s] Post trashed: %s', 'post-notifications'), $site_name, $post_title),
        );

        return isset($subjects[$notification_type]) ? $subjects[$notification_type] : sprintf(__('[%s] Post notification', 'post-notifications'), $site_name);
    }

    /**
     * Get email message
     */
    private function get_email_message($notification_type, $post, $author_name) {
        $post_title = $post->post_title ? $post->post_title : __('(no title)', 'post-notifications');
        $post_link = esc_url(get_permalink($post->ID));
        $edit_link = esc_url(get_edit_post_link($post->ID));
        $site_name = esc_html(get_bloginfo('name'));
        $site_url = esc_url(get_bloginfo('url'));

        // Get post type label
        $post_type_obj = get_post_type_object($post->post_type);
        $post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post->post_type;

        $messages = array(
            'pending' => sprintf(
                __('<p>A new post has been submitted for review on <a href="%s">%s</a>.</p>', 'post-notifications'),
                $site_url,
                $site_name
            ) . sprintf(
                __('<p><strong>Title:</strong> %s</p>', 'post-notifications'),
                esc_html($post_title)
            ) . sprintf(
                __('<p><strong>Type:</strong> %s</p>', 'post-notifications'),
                esc_html($post_type_label)
            ) . sprintf(
                __('<p><strong>Author:</strong> %s</p>', 'post-notifications'),
                esc_html($author_name)
            ) . sprintf(
                __('<p><strong>Status:</strong> Pending Review</p>', 'post-notifications')
            ) . sprintf(
                __('<p><a href="%s">Review and approve this post</a></p>', 'post-notifications'),
                $edit_link
            ),

            'published' => sprintf(
                __('<p>A post has been published on <a href="%s">%s</a>.</p>', 'post-notifications'),
                $site_url,
                $site_name
            ) . sprintf(
                __('<p><strong>Title:</strong> %s</p>', 'post-notifications'),
                esc_html($post_title)
            ) . sprintf(
                __('<p><strong>Type:</strong> %s</p>', 'post-notifications'),
                esc_html($post_type_label)
            ) . sprintf(
                __('<p><strong>Author:</strong> %s</p>', 'post-notifications'),
                esc_html($author_name)
            ) . sprintf(
                __('<p><a href="%s">View post</a> | <a href="%s">Edit post</a></p>', 'post-notifications'),
                $post_link,
                $edit_link
            ),

            'draft' => sprintf(
                __('<p>A post has been saved as draft on <a href="%s">%s</a>.</p>', 'post-notifications'),
                $site_url,
                $site_name
            ) . sprintf(
                __('<p><strong>Title:</strong> %s</p>', 'post-notifications'),
                esc_html($post_title)
            ) . sprintf(
                __('<p><strong>Type:</strong> %s</p>', 'post-notifications'),
                esc_html($post_type_label)
            ) . sprintf(
                __('<p><strong>Author:</strong> %s</p>', 'post-notifications'),
                esc_html($author_name)
            ) . sprintf(
                __('<p><a href="%s">Edit draft</a></p>', 'post-notifications'),
                $edit_link
            ),

            'scheduled' => sprintf(
                __('<p>A post has been scheduled for publication on <a href="%s">%s</a>.</p>', 'post-notifications'),
                $site_url,
                $site_name
            ) . sprintf(
                __('<p><strong>Title:</strong> %s</p>', 'post-notifications'),
                esc_html($post_title)
            ) . sprintf(
                __('<p><strong>Type:</strong> %s</p>', 'post-notifications'),
                esc_html($post_type_label)
            ) . sprintf(
                __('<p><strong>Author:</strong> %s</p>', 'post-notifications'),
                esc_html($author_name)
            ) . sprintf(
                __('<p><strong>Scheduled for:</strong> %s</p>', 'post-notifications'),
                esc_html(get_the_date('', $post) . ' ' . get_the_time('', $post))
            ) . sprintf(
                __('<p><a href="%s">Edit post</a></p>', 'post-notifications'),
                $edit_link
            ),

            'updated' => sprintf(
                __('<p>A published post has been updated on <a href="%s">%s</a>.</p>', 'post-notifications'),
                $site_url,
                $site_name
            ) . sprintf(
                __('<p><strong>Title:</strong> %s</p>', 'post-notifications'),
                esc_html($post_title)
            ) . sprintf(
                __('<p><strong>Type:</strong> %s</p>', 'post-notifications'),
                esc_html($post_type_label)
            ) . sprintf(
                __('<p><strong>Author:</strong> %s</p>', 'post-notifications'),
                esc_html($author_name)
            ) . sprintf(
                __('<p><a href="%s">View post</a> | <a href="%s">Edit post</a></p>', 'post-notifications'),
                $post_link,
                $edit_link
            ),

            'trashed' => sprintf(
                __('<p>A post has been moved to trash on <a href="%s">%s</a>.</p>', 'post-notifications'),
                $site_url,
                $site_name
            ) . sprintf(
                __('<p><strong>Title:</strong> %s</p>', 'post-notifications'),
                esc_html($post_title)
            ) . sprintf(
                __('<p><strong>Type:</strong> %s</p>', 'post-notifications'),
                esc_html($post_type_label)
            ) . sprintf(
                __('<p><strong>Author:</strong> %s</p>', 'post-notifications'),
                esc_html($author_name)
            ) . sprintf(
                __('<p><a href="%s">View trashed posts</a></p>', 'post-notifications'),
                esc_url(admin_url('edit.php?post_status=trash&post_type=' . $post->post_type))
            ),
        );

        $message = isset($messages[$notification_type]) ? $messages[$notification_type] : '';

        // Wrap in basic HTML template
        $html_message = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>';
        $html_message .= $message;
        $html_message .= '<hr><p style="font-size: 12px; color: #666;">';
        $html_message .= __('This is an automated notification from Post Notifications plugin.', 'post-notifications');
        $html_message .= '</p></body></html>';

        return $html_message;
    }
}

// Initialize plugin
function post_notifications_init() {
    return Post_Notifications::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'post_notifications_init');
