<?php
/**
 * Emails admin template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Display any admin notices
settings_errors('wdta_emails');
?>

<div class="wrap">
    <h1>Email Templates</h1>
    
    <p class="description">Customize all email messages sent to members. Available placeholders: {user_name}, {user_email}, {year}, {amount}, {deadline}, {renewal_url}, {site_name}</p>
    
    <form method="post" action="">
        <?php wp_nonce_field('wdta_emails_action', 'wdta_emails_nonce'); ?>
        
        <h2>Payment Reminder Emails</h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wdta_email_reminder_1month">1 Month Before (Dec 1st)</label></th>
                <td>
                    <input type="text" id="wdta_email_reminder_1month_subject" name="wdta_email_reminder_1month_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_reminder_1month_subject', 'WDTA Membership Renewal - Due January 1st')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_reminder_1month_body', 
'Dear {user_name},

This is a reminder that your WDTA membership for {year} will be due on January 1st, {year}.

The annual membership fee is ${amount} AUD and must be paid by {deadline}.

You can renew your membership at: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_reminder_1month_body',
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
            
            <tr>
                <th scope="row"><label for="wdta_email_reminder_1week">1 Week Before (Dec 25th)</label></th>
                <td>
                    <input type="text" id="wdta_email_reminder_1week_subject" name="wdta_email_reminder_1week_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_reminder_1week_subject', 'WDTA Membership - Due in 1 Week')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_reminder_1week_body', 
'Dear {user_name},

Your WDTA membership renewal is due in one week (January 1st, {year}).

Please ensure your payment of ${amount} AUD is made by {deadline}.

Renew now at: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_reminder_1week_body',
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
            
            <tr>
                <th scope="row"><label for="wdta_email_reminder_1day">1 Day Before (Dec 31st)</label></th>
                <td>
                    <input type="text" id="wdta_email_reminder_1day_subject" name="wdta_email_reminder_1day_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_reminder_1day_subject', 'WDTA Membership - Due Tomorrow')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_reminder_1day_body', 
'Dear {user_name},

Final reminder: Your WDTA membership for {year} is due tomorrow!

Payment deadline: {deadline}
Amount: ${amount} AUD

Renew immediately at: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_reminder_1day_body',
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
            
            <tr>
                <th scope="row"><label for="wdta_email_reminder_1day_overdue">1 Day Overdue (Jan 2nd)</label></th>
                <td>
                    <input type="text" id="wdta_email_reminder_1day_overdue_subject" name="wdta_email_reminder_1day_overdue_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_reminder_1day_overdue_subject', 'WDTA Membership - Payment Overdue')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_reminder_1day_overdue_body', 
'Dear {user_name},

Your WDTA membership payment for {year} is now overdue.

To maintain access to member resources, please complete your payment of ${amount} AUD as soon as possible.

Final deadline: {deadline}

Pay now at: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_reminder_1day_overdue_body',
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
            
            <tr>
                <th scope="row"><label for="wdta_email_reminder_1week_overdue">1 Week Overdue (Jan 8th)</label></th>
                <td>
                    <input type="text" id="wdta_email_reminder_1week_overdue_subject" name="wdta_email_reminder_1week_overdue_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_reminder_1week_overdue_subject', 'WDTA Membership - Urgent: Payment Required')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_reminder_1week_overdue_body', 
'Dear {user_name},

URGENT: Your WDTA membership payment for {year} is now one week overdue.

Amount due: ${amount} AUD
Deadline: {deadline}

Your membership access will be suspended if payment is not received.

Renew now at: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_reminder_1week_overdue_body',
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
            
            <tr>
                <th scope="row"><label for="wdta_email_reminder_month1">End of Month 1 (Jan 31st)</label></th>
                <td>
                    <input type="text" id="wdta_email_reminder_month1_subject" name="wdta_email_reminder_month1_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_reminder_month1_subject', 'WDTA Membership - Final Notice Month 1')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_reminder_month1_body', 
'Dear {user_name},

This is a final notice that your WDTA membership payment for {year} remains unpaid.

Amount outstanding: ${amount} AUD
Final deadline: {deadline}

Please complete your payment immediately to avoid suspension of membership benefits.

Renew now at: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_reminder_month1_body',
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
            
            <tr>
                <th scope="row"><label for="wdta_email_reminder_month2">End of Month 2 (Feb 28/29)</label></th>
                <td>
                    <input type="text" id="wdta_email_reminder_month2_subject" name="wdta_email_reminder_month2_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_reminder_month2_subject', 'WDTA Membership - Final Notice Month 2')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_reminder_month2_body', 
'Dear {user_name},

Your WDTA membership payment for {year} is still outstanding.

Outstanding amount: ${amount} AUD
Absolute deadline: {deadline}

This is your final reminder. Membership access will be terminated if payment is not received by the deadline.

Renew immediately at: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_reminder_month2_body',
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
            
            <tr>
                <th scope="row"><label for="wdta_email_reminder_final">Final Deadline (Mar 31st)</label></th>
                <td>
                    <input type="text" id="wdta_email_reminder_final_subject" name="wdta_email_reminder_final_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_reminder_final_subject', 'WDTA Membership - Access Suspended')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_reminder_final_body', 
'Dear {user_name},

Your WDTA membership for {year} has not been renewed and your access has been suspended.

Outstanding payment: ${amount} AUD

To restore your membership, please complete payment at: {renewal_url}

If you believe this is in error, please contact us immediately.

Best regards,
WDTA Team'),
                        'wdta_email_reminder_final_body',
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
        
        <h2>Transactional Emails</h2>
        <p class="description">These emails are sent when specific events occur. You can configure recipients and enable/disable each email.</p>
        
        <table class="form-table">
            <!-- Signup Confirmation Email -->
            <tr>
                <th scope="row"><label for="wdta_email_signup_confirmation">Signup Confirmation</label></th>
                <td>
                    <p>
                        <label>
                            <input type="checkbox" id="wdta_email_signup_enabled" name="wdta_email_signup_enabled" value="1" 
                                   <?php checked(get_option('wdta_email_signup_enabled', '1'), '1'); ?>>
                            Enable this email
                        </label>
                    </p>
                    <p>
                        <label for="wdta_email_signup_recipient">Recipient Email:</label><br>
                        <input type="email" id="wdta_email_signup_recipient" name="wdta_email_signup_recipient" 
                               value="<?php echo esc_attr(get_option('wdta_email_signup_recipient', get_option('admin_email'))); ?>" 
                               class="regular-text" placeholder="admin@example.com">
                        <p class="description">Who should receive this email? (Leave blank to send to user only)</p>
                    </p>
                    <input type="text" id="wdta_email_signup_subject" name="wdta_email_signup_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_signup_subject', 'Welcome to WDTA Membership')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_signup_body', 
'Dear {user_name},

Thank you for signing up for WDTA membership!

We have received your registration. To complete your membership, please make your payment of ${amount} AUD by {deadline}.

You can make a payment at: {renewal_url}

If you have any questions, please don\'t hesitate to contact us.

Best regards,
WDTA Team'),
                        'wdta_email_signup_body',
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
            
            <!-- Payment Confirmation Email -->
            <tr>
                <th scope="row"><label for="wdta_email_payment_confirmation">Payment Confirmation</label></th>
                <td>
                    <p>
                        <label>
                            <input type="checkbox" id="wdta_email_payment_enabled" name="wdta_email_payment_enabled" value="1" 
                                   <?php checked(get_option('wdta_email_payment_enabled', '1'), '1'); ?>>
                            Enable this email
                        </label>
                    </p>
                    <p>
                        <label for="wdta_email_payment_recipient">Recipient Email:</label><br>
                        <input type="email" id="wdta_email_payment_recipient" name="wdta_email_payment_recipient" 
                               value="<?php echo esc_attr(get_option('wdta_email_payment_recipient', get_option('admin_email'))); ?>" 
                               class="regular-text" placeholder="admin@example.com">
                        <p class="description">Who should receive this email? (Leave blank to send to user only)</p>
                    </p>
                    <input type="text" id="wdta_email_payment_subject" name="wdta_email_payment_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_payment_subject', 'WDTA Membership Payment Confirmed')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_payment_body', 
'Dear {user_name},

Thank you for your payment!

Your WDTA membership for {year} is now active. Your membership is valid until December 31, {year}.

Payment Details:
- Amount: ${amount} AUD
- Payment Method: {payment_method}
- Payment Date: {payment_date}

You now have access to all member-only content on our website.

Best regards,
WDTA Team'),
                        'wdta_email_payment_body',
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
            
            <!-- Grace Period Email -->
            <tr>
                <th scope="row"><label for="wdta_email_grace_period">Grace Period Notification</label></th>
                <td>
                    <p>
                        <label>
                            <input type="checkbox" id="wdta_email_grace_enabled" name="wdta_email_grace_enabled" value="1" 
                                   <?php checked(get_option('wdta_email_grace_enabled', '1'), '1'); ?>>
                            Enable this email
                        </label>
                    </p>
                    <p>
                        <label for="wdta_email_grace_recipient">Recipient Email:</label><br>
                        <input type="email" id="wdta_email_grace_recipient" name="wdta_email_grace_recipient" 
                               value="<?php echo esc_attr(get_option('wdta_email_grace_recipient', get_option('admin_email'))); ?>" 
                               class="regular-text" placeholder="admin@example.com">
                        <p class="description">Who should receive this email? (Leave blank to send to user only)</p>
                    </p>
                    <input type="text" id="wdta_email_grace_subject" name="wdta_email_grace_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_grace_subject', 'WDTA Membership - Grace Period')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_grace_body', 
'Dear {user_name},

Your WDTA membership for {year} is now in the grace period.

You have until {deadline} to renew your membership. After this date, your access to member-only content will be suspended.

Amount due: ${amount} AUD

Renew now at: {renewal_url}

Best regards,
WDTA Team'),
                        'wdta_email_grace_body',
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
            
            <!-- Expiry Email -->
            <tr>
                <th scope="row"><label for="wdta_email_expiry">Membership Expired</label></th>
                <td>
                    <p>
                        <label>
                            <input type="checkbox" id="wdta_email_expiry_enabled" name="wdta_email_expiry_enabled" value="1" 
                                   <?php checked(get_option('wdta_email_expiry_enabled', '1'), '1'); ?>>
                            Enable this email
                        </label>
                    </p>
                    <p>
                        <label for="wdta_email_expiry_recipient">Recipient Email:</label><br>
                        <input type="email" id="wdta_email_expiry_recipient" name="wdta_email_expiry_recipient" 
                               value="<?php echo esc_attr(get_option('wdta_email_expiry_recipient', get_option('admin_email'))); ?>" 
                               class="regular-text" placeholder="admin@example.com">
                        <p class="description">Who should receive this email? (Leave blank to send to user only)</p>
                    </p>
                    <input type="text" id="wdta_email_expiry_subject" name="wdta_email_expiry_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_expiry_subject', 'WDTA Membership Expired')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_expiry_body', 
'Dear {user_name},

Your WDTA membership for {year} has expired.

Your access to member-only content has been suspended. To restore your membership, please make a payment of ${amount} AUD.

Renew now at: {renewal_url}

If you have any questions or believe this is an error, please contact us.

Best regards,
WDTA Team'),
                        'wdta_email_expiry_body',
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
        
        <?php submit_button('Save Email Templates', 'primary', 'wdta_emails_submit'); ?>
    </form>
</div>
