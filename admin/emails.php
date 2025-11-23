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
        
        <h2>Email Configuration</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label>Inactive Users Report</label></th>
                <td>
                    <label>
                        <input type="checkbox" name="wdta_email_inactive_report_enabled" value="1" 
                               <?php checked(get_option('wdta_email_inactive_report_enabled', '1'), '1'); ?>>
                        Send inactive users report on January 1st
                    </label>
                    <p class="description">Email will be sent to site admin listing all inactive members</p>
                    <br>
                    <label for="wdta_inactive_report_emails">Report Recipients (comma-separated emails):</label>
                    <input type="text" id="wdta_inactive_report_emails" name="wdta_inactive_report_emails" 
                           value="<?php echo esc_attr(get_option('wdta_inactive_report_emails', get_option('admin_email'))); ?>" 
                           class="large-text">
                </td>
            </tr>
        </table>
        
        <h2>Payment Reminder Emails</h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wdta_email_reminder_1month">1 Month Before (Dec 1st)</label></th>
                <td>
                    <label style="margin-bottom: 10px; display: block;">
                        <input type="checkbox" name="wdta_email_reminder_1month_enabled" value="1" 
                               <?php checked(get_option('wdta_email_reminder_1month_enabled', '1'), '1'); ?>>
                        Enable this reminder email
                    </label>
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
                    <label style="margin-bottom: 10px; display: block;">
                        <input type="checkbox" name="wdta_email_reminder_1week_enabled" value="1" 
                               <?php checked(get_option('wdta_email_reminder_1week_enabled', '1'), '1'); ?>>
                        Enable this reminder email
                    </label>
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
                    <label style="margin-bottom: 10px; display: block;">
                        <input type="checkbox" name="wdta_email_reminder_1day_enabled" value="1" 
                               <?php checked(get_option('wdta_email_reminder_1day_enabled', '1'), '1'); ?>>
                        Enable this reminder email
                    </label>
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
        
        <?php submit_button('Save Email Templates', 'primary', 'wdta_emails_submit'); ?>
    </form>
</div>
