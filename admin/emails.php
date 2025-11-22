<?php
/**
 * Admin emails management template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Email Management</h1>
    
    <?php settings_errors('wdta_emails'); ?>
    
    <form method="post" action="">
        <?php wp_nonce_field('wdta_emails_action', 'wdta_emails_nonce'); ?>
        
        <h2>Email Settings</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wdta_email_from_name">From Name</label></th>
                <td>
                    <input type="text" id="wdta_email_from_name" name="wdta_email_from_name" 
                           value="<?php echo esc_attr(get_option('wdta_email_from_name', get_bloginfo('name'))); ?>" 
                           class="regular-text">
                    <p class="description">The name that appears in the "From" field of emails</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wdta_email_from_address">From Email Address</label></th>
                <td>
                    <input type="email" id="wdta_email_from_address" name="wdta_email_from_address" 
                           value="<?php echo esc_attr(get_option('wdta_email_from_address', get_option('admin_email'))); ?>" 
                           class="regular-text">
                    <p class="description">The email address that appears in the "From" field</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wdta_email_admin_recipient">Admin Recipient Email</label></th>
                <td>
                    <input type="email" id="wdta_email_admin_recipient" name="wdta_email_admin_recipient" 
                           value="<?php echo esc_attr(get_option('wdta_email_admin_recipient', get_option('admin_email'))); ?>" 
                           class="regular-text">
                    <p class="description">Email address to receive admin notifications</p>
                </td>
            </tr>
        </table>
        
        <h2>Email Templates</h2>
        <p class="description">Customize the email messages sent to members. Available placeholders: {user_name}, {user_email}, {year}, {amount}, {deadline}, {renewal_url}, {site_name}</p>
        
        <!-- New Signup Email -->
        <h3>1. New Signup Confirmation</h3>
        <table class="form-table">
            <tr>
                <th scope="row">Recipients</th>
                <td>
                    <label>
                        <input type="checkbox" name="wdta_email_signup_to_admin" value="1" 
                               <?php checked(get_option('wdta_email_signup_to_admin', 1)); ?>>
                        Send to Admin
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="wdta_email_signup_to_customer" value="1" 
                               <?php checked(get_option('wdta_email_signup_to_customer', 1)); ?>>
                        Also send to Customer
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wdta_email_signup">Email Template</label></th>
                <td>
                    <input type="text" id="wdta_email_signup_subject" name="wdta_email_signup_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_signup_subject', 'Welcome to WDTA Membership')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_signup', 
'Dear {user_name},

Welcome to WDTA (Workplace Drug Testing Australia)!

Your account has been created successfully. You can now log in and manage your membership.

To complete your membership registration, please submit your payment for {year}.

Login here: {renewal_url}

If you have any questions, please don\'t hesitate to contact us.

Best regards,
WDTA Team'),
                        'wdta_email_signup',
                        array(
                            'textarea_rows' => 10,
                            'media_buttons' => false,
                            'teeny' => false,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,link,bullist,numlist,alignleft,aligncenter,alignright',
                            ),
                        )
                    );
                    ?>
                </td>
            </tr>
        </table>
        
        <!-- Payment Confirmation Email -->
        <h3>2. Payment Confirmation</h3>
        <table class="form-table">
            <tr>
                <th scope="row">Recipients</th>
                <td>
                    <label>
                        <input type="checkbox" name="wdta_email_payment_to_admin" value="1" 
                               <?php checked(get_option('wdta_email_payment_to_admin', 1)); ?>>
                        Send to Admin
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="wdta_email_payment_to_customer" value="1" 
                               <?php checked(get_option('wdta_email_payment_to_customer', 1)); ?>>
                        Also send to Customer
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wdta_email_payment">Email Template</label></th>
                <td>
                    <input type="text" id="wdta_email_payment_subject" name="wdta_email_payment_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_payment_subject', 'WDTA Membership Payment Confirmed - {year}')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_payment', 
'Dear {user_name},

Thank you for your WDTA membership payment for {year}.

Payment Details:
Membership fee: $950.00 AUD
Card processing fee (2.2%): $20.90 AUD
Total paid: $970.90 AUD

Your membership is now active and will remain valid until December 31, {year}.

Best regards,
WDTA Team'),
                        'wdta_email_payment',
                        array(
                            'textarea_rows' => 10,
                            'media_buttons' => false,
                            'teeny' => false,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,link,bullist,numlist,alignleft,aligncenter,alignright',
                            ),
                        )
                    );
                    ?>
                </td>
            </tr>
        </table>
        
        <!-- Reminder Emails -->
        <h3>3. Reminder: 1 Month Before (Dec 1st)</h3>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wdta_email_reminder_1month">Email Template</label></th>
                <td>
                    <input type="text" id="wdta_email_reminder_1month_subject" name="wdta_email_reminder_1month_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_reminder_1month_subject', 'WDTA Membership Renewal - Due January 1st')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_reminder_1month', 
'Dear {user_name},

This is a reminder that your WDTA membership for {year} will be due on January 1st, {year}.

The annual membership fee is {amount} AUD and must be paid by {deadline}.

You can renew your membership at: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_reminder_1month',
                        array(
                            'textarea_rows' => 10,
                            'media_buttons' => false,
                            'teeny' => false,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,link,bullist,numlist,alignleft,aligncenter,alignright',
                            ),
                        )
                    );
                    ?>
                </td>
            </tr>
        </table>
        
        <h3>4. Reminder: 1 Week Before (Dec 25th)</h3>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wdta_email_reminder_1week">Email Template</label></th>
                <td>
                    <input type="text" id="wdta_email_reminder_1week_subject" name="wdta_email_reminder_1week_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_reminder_1week_subject', 'WDTA Membership - Due in 1 Week')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_reminder_1week', 
'Dear {user_name},

Your WDTA membership renewal is due in one week (January 1st, {year}).

Please ensure your payment of {amount} AUD is made by {deadline}.

Renew now at: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_reminder_1week',
                        array(
                            'textarea_rows' => 10,
                            'media_buttons' => false,
                            'teeny' => false,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,link,bullist,numlist,alignleft,aligncenter,alignright',
                            ),
                        )
                    );
                    ?>
                </td>
            </tr>
        </table>
        
        <h3>5. Reminder: 1 Day Before (Dec 31st)</h3>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wdta_email_reminder_1day">Email Template</label></th>
                <td>
                    <input type="text" id="wdta_email_reminder_1day_subject" name="wdta_email_reminder_1day_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_reminder_1day_subject', 'WDTA Membership - Due Tomorrow')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_reminder_1day', 
'Dear {user_name},

This is a reminder that your WDTA membership renewal is due tomorrow (January 1st, {year}).

Payment of {amount} AUD must be made by {deadline}.

Renew immediately at: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_reminder_1day',
                        array(
                            'textarea_rows' => 10,
                            'media_buttons' => false,
                            'teeny' => false,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,link,bullist,numlist,alignleft,aligncenter,alignright',
                            ),
                        )
                    );
                    ?>
                </td>
            </tr>
        </table>
        
        <h3>6. Overdue: 1 Day After (Jan 2nd)</h3>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wdta_email_overdue_1day">Email Template</label></th>
                <td>
                    <input type="text" id="wdta_email_overdue_1day_subject" name="wdta_email_overdue_1day_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_overdue_1day_subject', 'WDTA Membership - Payment Overdue')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_overdue_1day', 
'Dear {user_name},

Your WDTA membership for {year} is now overdue. The payment of {amount} was due on January 1st, {year}.

To maintain access to member-only content, please renew by {deadline}.

Renew now: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_overdue_1day',
                        array(
                            'textarea_rows' => 10,
                            'media_buttons' => false,
                            'teeny' => false,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,link,bullist,numlist,alignleft,aligncenter,alignright',
                            ),
                        )
                    );
                    ?>
                </td>
            </tr>
        </table>
        
        <h3>7. Overdue: 1 Week After (Jan 8th)</h3>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wdta_email_overdue_1week">Email Template</label></th>
                <td>
                    <input type="text" id="wdta_email_overdue_1week_subject" name="wdta_email_overdue_1week_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_overdue_1week_subject', 'WDTA Membership - 1 Week Overdue')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_overdue_1week', 
'Dear {user_name},

Your WDTA membership payment for {year} remains outstanding. The payment of {amount} is now one week overdue.

Please renew by {deadline} to avoid losing access to member-only content.

Renew now: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_overdue_1week',
                        array(
                            'textarea_rows' => 10,
                            'media_buttons' => false,
                            'teeny' => false,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,link,bullist,numlist,alignleft,aligncenter,alignright',
                            ),
                        )
                    );
                    ?>
                </td>
            </tr>
        </table>
        
        <h3>8. Overdue: End of January (Jan 31st)</h3>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wdta_email_overdue_end_jan">Email Template</label></th>
                <td>
                    <input type="text" id="wdta_email_overdue_end_jan_subject" name="wdta_email_overdue_end_jan_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_overdue_end_jan_subject', 'WDTA Membership - End of January Notice')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_overdue_end_jan', 
'Dear {user_name},

Your WDTA membership for {year} is still outstanding.

Payment of {amount} must be received by {deadline} to maintain your membership and access.

Renew now: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_overdue_end_jan',
                        array(
                            'textarea_rows' => 10,
                            'media_buttons' => false,
                            'teeny' => false,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,link,bullist,numlist,alignleft,aligncenter,alignright',
                            ),
                        )
                    );
                    ?>
                </td>
            </tr>
        </table>
        
        <h3>9. Overdue: End of February (Feb 28/29)</h3>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wdta_email_overdue_end_feb">Email Template</label></th>
                <td>
                    <input type="text" id="wdta_email_overdue_end_feb_subject" name="wdta_email_overdue_end_feb_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_overdue_end_feb_subject', 'WDTA Membership - Urgent: End of February')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_overdue_end_feb', 
'Dear {user_name},

This is an urgent reminder that your WDTA membership payment for {year} is overdue.

You have until {deadline} to pay the {amount} membership fee. After this date, you will lose access to all member-only content.

Renew now: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_overdue_end_feb',
                        array(
                            'textarea_rows' => 10,
                            'media_buttons' => false,
                            'teeny' => false,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,link,bullist,numlist,alignleft,aligncenter,alignright',
                            ),
                        )
                    );
                    ?>
                </td>
            </tr>
        </table>
        
        <h3>10. Final Notice: End of March (Mar 31st)</h3>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wdta_email_overdue_end_mar">Email Template</label></th>
                <td>
                    <input type="text" id="wdta_email_overdue_end_mar_subject" name="wdta_email_overdue_end_mar_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_overdue_end_mar_subject', 'WDTA Membership - FINAL NOTICE')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_overdue_end_mar', 
'Dear {user_name},

FINAL NOTICE: This is your last day to renew your WDTA membership for {year}.

If payment of {amount} is not received by midnight tonight ({deadline}), your membership will expire and you will lose access to all member-only content.

Renew immediately: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_overdue_end_mar',
                        array(
                            'textarea_rows' => 10,
                            'media_buttons' => false,
                            'teeny' => false,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,link,bullist,numlist,alignleft,aligncenter,alignright',
                            ),
                        )
                    );
                    ?>
                </td>
            </tr>
        </table>
        
        <?php submit_button('Save Email Settings', 'primary', 'wdta_emails_submit'); ?>
    </form>
</div>
