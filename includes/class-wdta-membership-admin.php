<?php
/**
 * Admin interface management
 */

class WDTA_Membership_Admin {
    
    /**
     * Initialize admin hooks
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        add_menu_page(
            'WDTA Membership',
            'WDTA Membership',
            'manage_options',
            'wdta-membership',
            array(__CLASS__, 'admin_page'),
            'dashicons-groups',
            30
        );
        
        add_submenu_page(
            'wdta-membership',
            'Settings',
            'Settings',
            'manage_options',
            'wdta-membership-settings',
            array(__CLASS__, 'settings_page')
        );
        
        add_submenu_page(
            'wdta-membership',
            'Members',
            'Members',
            'manage_options',
            'wdta-membership-members',
            array(__CLASS__, 'members_page')
        );
    }
    
    /**
     * Enqueue admin scripts
     */
    public static function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'wdta-membership') === false) {
            return;
        }
        
        wp_enqueue_style('wdta-membership-admin', WDTA_MEMBERSHIP_PLUGIN_URL . 'assets/css/wdta-admin.css', array(), WDTA_MEMBERSHIP_VERSION);
    }
    
    /**
     * Register settings
     */
    public static function register_settings() {
        // General settings
        register_setting('wdta_membership_general', 'wdta_membership_currency');
        register_setting('wdta_membership_general', 'wdta_membership_current_year_price');
        register_setting('wdta_membership_general', 'wdta_membership_next_year_price');
        register_setting('wdta_membership_general', 'wdta_membership_payment_method');
        
        // Email settings
        register_setting('wdta_membership_email', 'wdta_membership_from_email');
        register_setting('wdta_membership_email', 'wdta_membership_from_name');
        
        // Inactive users email
        register_setting('wdta_membership_email', 'wdta_membership_inactive_email_enabled');
        register_setting('wdta_membership_email', 'wdta_membership_inactive_email_recipients');
        register_setting('wdta_membership_email', 'wdta_membership_inactive_email_subject');
        
        // Reminder emails
        for ($i = 1; $i <= 3; $i++) {
            register_setting('wdta_membership_email', 'wdta_membership_reminder' . $i . '_enabled');
            register_setting('wdta_membership_email', 'wdta_membership_reminder' . $i . '_timing');
            register_setting('wdta_membership_email', 'wdta_membership_reminder' . $i . '_unit');
            register_setting('wdta_membership_email', 'wdta_membership_reminder' . $i . '_period');
            register_setting('wdta_membership_email', 'wdta_membership_reminder' . $i . '_subject');
        }
    }
    
    /**
     * Main admin page
     */
    public static function admin_page() {
        $current_year = WDTA_Membership_Status::get_current_year();
        $next_year = WDTA_Membership_Status::get_next_year();
        
        // Get statistics
        $active_current = self::count_active_members($current_year);
        $inactive_current = self::count_inactive_members($current_year);
        $active_next = self::count_active_members($next_year);
        
        ?>
        <div class="wrap">
            <h1>WDTA Membership Dashboard</h1>
            
            <div class="wdta-admin-dashboard">
                <div class="wdta-stat-box">
                    <h3><?php echo esc_html($current_year); ?> Memberships</h3>
                    <p class="wdta-stat-number"><?php echo esc_html($active_current); ?></p>
                    <p class="wdta-stat-label">Active Members</p>
                </div>
                
                <div class="wdta-stat-box">
                    <h3>Inactive Members</h3>
                    <p class="wdta-stat-number"><?php echo esc_html($inactive_current); ?></p>
                    <p class="wdta-stat-label">Need Renewal</p>
                </div>
                
                <div class="wdta-stat-box">
                    <h3><?php echo esc_html($next_year); ?> Renewals</h3>
                    <p class="wdta-stat-number"><?php echo esc_html($active_next); ?></p>
                    <p class="wdta-stat-label">Already Renewed</p>
                </div>
            </div>
            
            <h2>Shortcodes</h2>
            <div class="wdta-shortcodes">
                <div class="wdta-shortcode-box">
                    <h3>Membership Registration Form</h3>
                    <p>For non-logged in users to create an account and pay for current year:</p>
                    <code>[wdta_membership_form]</code>
                </div>
                
                <div class="wdta-shortcode-box">
                    <h3>Membership Renewal Form</h3>
                    <p>For logged-in users to pay for next year:</p>
                    <code>[wdta_membership_renewal_form]</code>
                </div>
            </div>
            
            <h2>Quick Actions</h2>
            <p>
                <a href="<?php echo admin_url('admin.php?page=wdta-membership-settings'); ?>" class="button button-primary">Settings</a>
                <a href="<?php echo admin_url('admin.php?page=wdta-membership-members'); ?>" class="button">View Members</a>
                <a href="<?php echo admin_url('admin.php?page=wdta-membership&action=send_inactive_report'); ?>" class="button">Send Inactive Users Report</a>
            </p>
            
            <?php
            // Handle quick actions
            if (isset($_GET['action']) && $_GET['action'] === 'send_inactive_report') {
                if (WDTA_Membership_Email::send_inactive_users_report()) {
                    echo '<div class="notice notice-success"><p>Inactive users report sent successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-info"><p>No inactive users to report or email disabled.</p></div>';
                }
            }
            ?>
        </div>
        <?php
    }
    
    /**
     * Settings page
     */
    public static function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Save settings
        if (isset($_POST['wdta_membership_save_settings'])) {
            check_admin_referer('wdta_membership_settings');
            
            // Save all settings
            $fields = array(
                'wdta_membership_currency',
                'wdta_membership_current_year_price',
                'wdta_membership_next_year_price',
                'wdta_membership_from_email',
                'wdta_membership_from_name',
                'wdta_membership_inactive_email_enabled',
                'wdta_membership_inactive_email_recipients',
                'wdta_membership_inactive_email_subject',
            );
            
            // Add reminder fields
            for ($i = 1; $i <= 3; $i++) {
                $fields[] = 'wdta_membership_reminder' . $i . '_enabled';
                $fields[] = 'wdta_membership_reminder' . $i . '_timing';
                $fields[] = 'wdta_membership_reminder' . $i . '_unit';
                $fields[] = 'wdta_membership_reminder' . $i . '_period';
                $fields[] = 'wdta_membership_reminder' . $i . '_subject';
            }
            
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    update_option($field, sanitize_text_field($_POST[$field]));
                }
            }
            
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        
        ?>
        <div class="wrap">
            <h1>WDTA Membership Settings</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('wdta_membership_settings'); ?>
                
                <h2>General Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Currency</th>
                        <td>
                            <input type="text" name="wdta_membership_currency" value="<?php echo esc_attr(get_option('wdta_membership_currency', 'USD')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Current Year Membership Price</th>
                        <td>
                            <input type="number" step="0.01" name="wdta_membership_current_year_price" value="<?php echo esc_attr(get_option('wdta_membership_current_year_price', '50.00')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Next Year Membership Price</th>
                        <td>
                            <input type="number" step="0.01" name="wdta_membership_next_year_price" value="<?php echo esc_attr(get_option('wdta_membership_next_year_price', '50.00')); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                
                <h2>Email Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">From Email</th>
                        <td>
                            <input type="email" name="wdta_membership_from_email" value="<?php echo esc_attr(get_option('wdta_membership_from_email', get_option('admin_email'))); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">From Name</th>
                        <td>
                            <input type="text" name="wdta_membership_from_name" value="<?php echo esc_attr(get_option('wdta_membership_from_name', get_bloginfo('name'))); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                
                <h2>Inactive Users Report</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Report</th>
                        <td>
                            <label>
                                <input type="checkbox" name="wdta_membership_inactive_email_enabled" value="yes" <?php checked(get_option('wdta_membership_inactive_email_enabled'), 'yes'); ?> />
                                Send inactive users report on January 1st
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Recipients</th>
                        <td>
                            <input type="text" name="wdta_membership_inactive_email_recipients" value="<?php echo esc_attr(get_option('wdta_membership_inactive_email_recipients', get_option('admin_email'))); ?>" class="regular-text" />
                            <p class="description">Comma-separated email addresses</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Email Subject</th>
                        <td>
                            <input type="text" name="wdta_membership_inactive_email_subject" value="<?php echo esc_attr(get_option('wdta_membership_inactive_email_subject', 'Inactive WDTA Members Report')); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                
                <h2>Reminder Emails</h2>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <h3>Reminder <?php echo $i; ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Reminder</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wdta_membership_reminder<?php echo $i; ?>_enabled" value="yes" <?php checked(get_option('wdta_membership_reminder' . $i . '_enabled'), 'yes'); ?> />
                                    Send this reminder
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Timing</th>
                            <td>
                                <input type="number" name="wdta_membership_reminder<?php echo $i; ?>_timing" value="<?php echo esc_attr(get_option('wdta_membership_reminder' . $i . '_timing', '30')); ?>" style="width: 80px;" />
                                <select name="wdta_membership_reminder<?php echo $i; ?>_unit">
                                    <option value="days" <?php selected(get_option('wdta_membership_reminder' . $i . '_unit'), 'days'); ?>>Days</option>
                                    <option value="weeks" <?php selected(get_option('wdta_membership_reminder' . $i . '_unit'), 'weeks'); ?>>Weeks</option>
                                    <option value="months" <?php selected(get_option('wdta_membership_reminder' . $i . '_unit'), 'months'); ?>>Months</option>
                                </select>
                                <select name="wdta_membership_reminder<?php echo $i; ?>_period">
                                    <option value="before" <?php selected(get_option('wdta_membership_reminder' . $i . '_period'), 'before'); ?>>Before</option>
                                    <option value="after" <?php selected(get_option('wdta_membership_reminder' . $i . '_period'), 'after'); ?>>After</option>
                                </select>
                                December 31st
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Email Subject</th>
                            <td>
                                <input type="text" name="wdta_membership_reminder<?php echo $i; ?>_subject" value="<?php echo esc_attr(get_option('wdta_membership_reminder' . $i . '_subject', 'WDTA Membership Reminder')); ?>" class="regular-text" />
                            </td>
                        </tr>
                    </table>
                <?php endfor; ?>
                
                <p class="submit">
                    <input type="submit" name="wdta_membership_save_settings" class="button button-primary" value="Save Settings" />
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * Members list page
     */
    public static function members_page() {
        $current_year = WDTA_Membership_Status::get_current_year();
        $year_filter = isset($_GET['year']) ? intval($_GET['year']) : $current_year;
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        // Get all users with their membership status for the selected year
        $sql = $wpdb->prepare(
            "SELECT u.ID, u.user_login, u.user_email, u.display_name, 
                    m.membership_year, m.status, m.payment_date, m.payment_amount
            FROM {$wpdb->users} u
            LEFT JOIN $table_name m ON u.ID = m.user_id AND m.membership_year = %d
            ORDER BY u.display_name ASC",
            $year_filter
        );
        
        $members = $wpdb->get_results($sql);
        
        ?>
        <div class="wrap">
            <h1>WDTA Members</h1>
            
            <div class="wdta-members-filter">
                <form method="get" action="">
                    <input type="hidden" name="page" value="wdta-membership-members" />
                    <label>Filter by Year:</label>
                    <select name="year" onchange="this.form.submit()">
                        <?php for ($y = $current_year - 2; $y <= $current_year + 1; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php selected($year_filter, $y); ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </form>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?php echo esc_html($member->ID); ?></td>
                            <td><?php echo esc_html($member->display_name); ?></td>
                            <td><?php echo esc_html($member->user_email); ?></td>
                            <td>
                                <?php if ($member->status === 'active'): ?>
                                    <span class="wdta-status-badge wdta-status-active">Active</span>
                                <?php else: ?>
                                    <span class="wdta-status-badge wdta-status-inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $member->payment_date ? esc_html(date('Y-m-d', strtotime($member->payment_date))) : '-'; ?></td>
                            <td><?php echo $member->payment_amount ? '$' . esc_html(number_format($member->payment_amount, 2)) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Count active members for a year
     */
    private static function count_active_members($year) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE membership_year = %d AND status = 'active'",
            $year
        ));
    }
    
    /**
     * Count inactive members for a year
     */
    private static function count_inactive_members($year) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT u.ID) FROM {$wpdb->users} u 
            LEFT JOIN $table_name m ON u.ID = m.user_id AND m.membership_year = %d
            WHERE m.status IS NULL OR m.status = 'inactive'",
            $year
        ));
    }
}
