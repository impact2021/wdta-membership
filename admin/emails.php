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
        
        <h2>Welcome & Confirmation Emails</h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label>Additional Admin Recipients</label></th>
                <td>
                    <input type="text" id="wdta_payment_admin_recipients" name="wdta_payment_admin_recipients" 
                           value="<?php echo esc_attr(get_option('wdta_payment_admin_recipients', '')); ?>" 
                           class="large-text" placeholder="email1@example.com, email2@example.com">
                    <p class="description">Comma-separated email addresses to receive copies of payment notifications (in addition to site admin)</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="wdta_email_stripe_confirmation">Stripe Payment Confirmation</label></th>
                <td>
                    <input type="text" id="wdta_email_stripe_confirmation_subject" name="wdta_email_stripe_confirmation_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_stripe_confirmation_subject', 'WDTA Membership Payment Confirmed - {year}')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_stripe_confirmation_body', 
'Dear {user_name},

Thank you for your WDTA membership payment for {year}.

Payment Details:
Membership fee: $950.00 AUD
Card processing fee (2.2%): $20.90 AUD
Total paid: $970.90 AUD

Your membership is now active and will remain valid until December 31, {year}.

Best regards,
WDTA Team'),
                        'wdta_email_stripe_confirmation_body',
                        array(
                            'textarea_rows' => 12,
                            'media_buttons' => false,
                            'teeny' => false,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,link,bullist,numlist,alignleft,aligncenter,alignright',
                            ),
                        )
                    );
                    ?>
                    <p class="description">Sent when a Stripe payment is successfully completed</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="wdta_email_bank_pending">Bank Transfer Pending (Admin Notification)</label></th>
                <td>
                    <input type="text" id="wdta_email_bank_pending_subject" name="wdta_email_bank_pending_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_bank_pending_subject', 'New Bank Transfer Submission - WDTA Membership {year}')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_bank_pending_body', 
'A new bank transfer payment has been submitted:

User: {user_name} ({user_email})
Year: {year}
Reference: {reference}
Amount: $950 AUD

Please verify the payment and update the membership status in the admin panel.
{admin_url}'),
                        'wdta_email_bank_pending_body',
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
                    <p class="description">Sent to admin when a user submits bank transfer details</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="wdta_email_bank_approved">Bank Transfer Approved (User Confirmation)</label></th>
                <td>
                    <input type="text" id="wdta_email_bank_approved_subject" name="wdta_email_bank_approved_subject" 
                           value="<?php echo esc_attr(get_option('wdta_email_bank_approved_subject', 'WDTA Membership Activated - {year}')); ?>" 
                           class="large-text" placeholder="Email Subject">
                    <br><br>
                    <?php 
                    wp_editor(
                        get_option('wdta_email_bank_approved_body', 
'Dear {user_name},

Your bank transfer payment of $950.00 AUD for {year} has been verified.

Your WDTA membership is now active and will remain valid until December 31, {year}.

Best regards,
WDTA Team'),
                        'wdta_email_bank_approved_body',
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
                    <p class="description">Sent when admin approves a bank transfer payment</p>
                </td>
            </tr>
        </table>
        
        <h2>Payment Reminder Emails</h2>
        
        <div id="wdta-reminders-container">
            <?php
            // Get existing reminders or set default
            $reminders = get_option('wdta_email_reminders', array());
            
            // If no reminders exist, create one default reminder
            if (empty($reminders)) {
                $reminders = array(
                    array(
                        'id' => 1,
                        'enabled' => true,
                        'timing' => 30,
                        'unit' => 'days',
                        'period' => 'before',
                        'subject' => 'WDTA Membership Renewal - Due January 1st',
                        'body' => 'Dear {user_name},

This is a reminder that your WDTA membership for {year} will be due on January 1st, {year}.

The annual membership fee is ${amount} AUD and must be paid by {deadline}.

You can renew your membership at: {renewal_url}

Best regards,
WDTA Team'
                    )
                );
            }
            
            foreach ($reminders as $index => $reminder) {
                $reminder_id = isset($reminder['id']) ? $reminder['id'] : ($index + 1);
                $enabled = isset($reminder['enabled']) ? $reminder['enabled'] : true;
                $timing = isset($reminder['timing']) ? $reminder['timing'] : 30;
                $unit = isset($reminder['unit']) ? $reminder['unit'] : 'days';
                $period = isset($reminder['period']) ? $reminder['period'] : 'before';
                $subject = isset($reminder['subject']) ? $reminder['subject'] : '';
                $body = isset($reminder['body']) ? $reminder['body'] : '';
                ?>
                <div class="wdta-reminder-item" data-reminder-id="<?php echo esc_attr($reminder_id); ?>">
                    <div class="wdta-reminder-header" style="background: #f5f5f5; padding: 15px; margin: 15px 0; border-left: 4px solid #2271b1;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="flex: 1;">
                                <label style="margin-bottom: 10px; display: block;">
                                    <input type="checkbox" 
                                           name="wdta_reminders[<?php echo esc_attr($reminder_id); ?>][enabled]" 
                                           value="1" 
                                           <?php checked($enabled, true); ?>>
                                    <strong>Enable this reminder</strong>
                                </label>
                                <div style="margin-top: 10px;">
                                    <label>Send 
                                        <input type="number" 
                                               name="wdta_reminders[<?php echo esc_attr($reminder_id); ?>][timing]" 
                                               value="<?php echo esc_attr($timing); ?>" 
                                               min="1" 
                                               style="width: 60px;">
                                        <select name="wdta_reminders[<?php echo esc_attr($reminder_id); ?>][unit]">
                                            <option value="days" <?php selected($unit, 'days'); ?>>day(s)</option>
                                            <option value="weeks" <?php selected($unit, 'weeks'); ?>>week(s)</option>
                                        </select>
                                        <select name="wdta_reminders[<?php echo esc_attr($reminder_id); ?>][period]">
                                            <option value="before" <?php selected($period, 'before'); ?>>BEFORE</option>
                                            <option value="after" <?php selected($period, 'after'); ?>>AFTER</option>
                                        </select>
                                        membership expires (Dec 31)
                                    </label>
                                </div>
                            </div>
                            <button type="button" class="button wdta-remove-reminder" style="margin-left: 20px;">Remove Reminder</button>
                        </div>
                    </div>
                    <div class="wdta-reminder-content" style="padding: 15px; border: 1px solid #ddd; border-top: none;">
                        <input type="hidden" 
                               name="wdta_reminders[<?php echo esc_attr($reminder_id); ?>][id]" 
                               value="<?php echo esc_attr($reminder_id); ?>">
                        
                        <label><strong>Email Subject:</strong></label>
                        <input type="text" 
                               name="wdta_reminders[<?php echo esc_attr($reminder_id); ?>][subject]" 
                               value="<?php echo esc_attr($subject); ?>" 
                               class="large-text" 
                               placeholder="Email Subject">
                        <br><br>
                        
                        <label><strong>Email Body:</strong></label>
                        <?php 
                        wp_editor(
                            $body,
                            'wdta_reminder_body_' . $reminder_id,
                            array(
                                'textarea_name' => 'wdta_reminders[' . $reminder_id . '][body]',
                                'textarea_rows' => 10,
                                'media_buttons' => false,
                                'teeny' => false,
                                'tinymce' => array(
                                    'toolbar1' => 'bold,italic,underline,link,bullist,numlist,alignleft,aligncenter,alignright',
                                ),
                            )
                        );
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        
        <p>
            <button type="button" id="wdta-add-reminder" class="button button-secondary">+ Add Another Reminder</button>
        </p>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var reminderCounter = <?php echo count($reminders); ?>;
            
            // Add new reminder
            $('#wdta-add-reminder').on('click', function() {
                reminderCounter++;
                var newReminderId = Date.now(); // Use timestamp for unique ID
                
                var reminderHtml = '<div class="wdta-reminder-item" data-reminder-id="' + newReminderId + '">' +
                    '<div class="wdta-reminder-header" style="background: #f5f5f5; padding: 15px; margin: 15px 0; border-left: 4px solid #2271b1;">' +
                        '<div style="display: flex; align-items: center; justify-content: space-between;">' +
                            '<div style="flex: 1;">' +
                                '<label style="margin-bottom: 10px; display: block;">' +
                                    '<input type="checkbox" name="wdta_reminders[' + newReminderId + '][enabled]" value="1" checked>' +
                                    '<strong>Enable this reminder</strong>' +
                                '</label>' +
                                '<div style="margin-top: 10px;">' +
                                    '<label>Send ' +
                                        '<input type="number" name="wdta_reminders[' + newReminderId + '][timing]" value="7" min="1" style="width: 60px;">' +
                                        '<select name="wdta_reminders[' + newReminderId + '][unit]">' +
                                            '<option value="days" selected>day(s)</option>' +
                                            '<option value="weeks">week(s)</option>' +
                                        '</select>' +
                                        '<select name="wdta_reminders[' + newReminderId + '][period]">' +
                                            '<option value="before" selected>BEFORE</option>' +
                                            '<option value="after">AFTER</option>' +
                                        '</select>' +
                                        ' membership expires (Dec 31)' +
                                    '</label>' +
                                '</div>' +
                            '</div>' +
                            '<button type="button" class="button wdta-remove-reminder" style="margin-left: 20px;">Remove Reminder</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="wdta-reminder-content" style="padding: 15px; border: 1px solid #ddd; border-top: none;">' +
                        '<input type="hidden" name="wdta_reminders[' + newReminderId + '][id]" value="' + newReminderId + '">' +
                        '<label><strong>Email Subject:</strong></label>' +
                        '<input type="text" name="wdta_reminders[' + newReminderId + '][subject]" value="" class="large-text" placeholder="Email Subject">' +
                        '<br><br>' +
                        '<label><strong>Email Body:</strong></label>' +
                        '<textarea name="wdta_reminders[' + newReminderId + '][body]" rows="10" class="large-text">Dear {user_name},\n\nThis is a reminder about your WDTA membership for {year}.\n\nThe annual membership fee is ${amount} AUD and must be paid by {deadline}.\n\nYou can renew your membership at: {renewal_url}\n\nBest regards,\nWDTA Team</textarea>' +
                    '</div>' +
                '</div>';
                
                $('#wdta-reminders-container').append(reminderHtml);
            });
            
            // Remove reminder
            $(document).on('click', '.wdta-remove-reminder', function() {
                if (confirm('Are you sure you want to remove this reminder?')) {
                    $(this).closest('.wdta-reminder-item').remove();
                }
            });
        });
        </script>
        
        <?php submit_button('Save Email Templates', 'primary', 'wdta_emails_submit'); ?>
    </form>
</div>
