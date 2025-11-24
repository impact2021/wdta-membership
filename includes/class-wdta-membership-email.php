<?php
/**
 * Email management class
 */

class WDTA_Membership_Email {
    
    /**
     * Send inactive users report
     */
    public static function send_inactive_users_report() {
        // Check if email is enabled
        if (get_option('wdta_membership_inactive_email_enabled') !== 'yes') {
            return false;
        }
        
        $year = WDTA_Membership_Status::get_current_year();
        $inactive_users = WDTA_Membership_Database::get_inactive_users($year);
        
        if (empty($inactive_users)) {
            return false;
        }
        
        $recipients = get_option('wdta_membership_inactive_email_recipients', get_option('admin_email'));
        $subject = get_option('wdta_membership_inactive_email_subject', 'Inactive WDTA Members Report');
        
        // Build email content
        $message = self::get_email_header();
        $message .= '<h2>' . esc_html($subject) . '</h2>';
        $message .= '<p>The following users have inactive memberships for ' . esc_html($year) . ':</p>';
        $message .= '<table style="width:100%; border-collapse: collapse; margin: 20px 0;">';
        $message .= '<thead>';
        $message .= '<tr style="background-color: #f5f5f5;">';
        $message .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">User ID</th>';
        $message .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Name</th>';
        $message .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Email</th>';
        $message .= '<th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Status</th>';
        $message .= '</tr>';
        $message .= '</thead>';
        $message .= '<tbody>';
        
        foreach ($inactive_users as $user) {
            $message .= '<tr>';
            $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($user->ID) . '</td>';
            $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($user->display_name) . '</td>';
            $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($user->user_email) . '</td>';
            $message .= '<td style="padding: 10px; border: 1px solid #ddd;">Inactive</td>';
            $message .= '</tr>';
        }
        
        $message .= '</tbody>';
        $message .= '</table>';
        $message .= '<p>Total inactive members: ' . count($inactive_users) . '</p>';
        $message .= self::get_email_footer();
        
        // Send email
        $headers = self::get_email_headers('none');
        return wp_mail($recipients, $subject, $message, $headers);
    }
    
    /**
     * Send reminder email to users
     */
    public static function send_reminder_email($reminder_number) {
        $enabled_key = 'wdta_membership_reminder' . $reminder_number . '_enabled';
        
        // Check if reminder is enabled
        if (get_option($enabled_key) !== 'yes') {
            return false;
        }
        
        $year = WDTA_Membership_Status::get_next_year();
        $users = WDTA_Membership_Database::get_users_for_reminder($year);
        
        if (empty($users)) {
            return false;
        }
        
        $subject = get_option('wdta_membership_reminder' . $reminder_number . '_subject', 'WDTA Membership Reminder');
        $headers = self::get_email_headers('reminder');
        
        $sent_count = 0;
        foreach ($users as $user) {
            $message = self::get_reminder_message($user, $year, $reminder_number);
            
            if (wp_mail($user->user_email, $subject, $message, $headers)) {
                $sent_count++;
            }
        }
        
        return $sent_count;
    }
    
    /**
     * Get reminder email message
     */
    private static function get_reminder_message($user, $year, $reminder_number) {
        $message = self::get_email_header();
        
        $message .= '<h2>WDTA Membership Renewal</h2>';
        $message .= '<p>Hello ' . esc_html($user->display_name) . ',</p>';
        
        $timing = get_option('wdta_membership_reminder' . $reminder_number . '_timing', '30');
        $unit = get_option('wdta_membership_reminder' . $reminder_number . '_unit', 'days');
        $period = get_option('wdta_membership_reminder' . $reminder_number . '_period', 'before');
        
        if ($period === 'before') {
            $message .= '<p>This is a reminder that your WDTA membership will expire on December 31st.</p>';
            $message .= '<p>Please renew your membership for ' . esc_html($year) . ' to continue enjoying member benefits.</p>';
        } else {
            $message .= '<p>Your WDTA membership has expired as of December 31st.</p>';
            $message .= '<p>Please renew your membership for ' . esc_html($year) . ' to regain access to member benefits.</p>';
        }
        
        $renewal_url = home_url('/membership-renewal/'); // This should be the page with the renewal shortcode
        $message .= '<p><a href="' . esc_url($renewal_url) . '" style="background-color: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px; display: inline-block;">Renew Membership</a></p>';
        
        $message .= self::get_email_footer();
        
        return $message;
    }
    
    /**
     * Send payment confirmation email
     */
    public static function send_payment_confirmation($user_id, $year, $amount) {
        $user = get_userdata($user_id);
        
        if (!$user) {
            return false;
        }
        
        $subject = 'WDTA Membership Payment Confirmation';
        $headers = self::get_email_headers('signup');
        
        $message = self::get_email_header();
        $message .= '<h2>Payment Confirmation</h2>';
        $message .= '<p>Hello ' . esc_html($user->display_name) . ',</p>';
        $message .= '<p>Thank you for your payment! Your WDTA membership for ' . esc_html($year) . ' is now active.</p>';
        $message .= '<p><strong>Payment Details:</strong></p>';
        $message .= '<ul>';
        $message .= '<li>Membership Year: ' . esc_html($year) . '</li>';
        $message .= '<li>Amount Paid: $' . esc_html(number_format($amount, 2)) . '</li>';
        $message .= '<li>Payment Date: ' . esc_html(date('F j, Y')) . '</li>';
        $message .= '</ul>';
        $message .= '<p>If you have any questions, please contact us.</p>';
        $message .= self::get_email_footer();
        
        return wp_mail($user->user_email, $subject, $message, $headers);
    }
    
    /**
     * Get email headers
     * 
     * @param string $type Type of email: 'reminder', 'signup', or 'none' (no CC)
     */
    private static function get_email_headers($type = 'reminder') {
        $from_email = get_option('wdta_membership_from_email', get_option('admin_email'));
        $from_name = get_option('wdta_membership_from_name', get_bloginfo('name'));
        
        $headers = array();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
        
        // Add CC recipients based on email type
        $cc_recipients = '';
        if ($type === 'signup') {
            $cc_recipients = get_option('wdta_signup_email_cc', 'marketing@wdta.org.au, treasurer@wdta.org.au');
        } elseif ($type === 'reminder') {
            $cc_recipients = get_option('wdta_reminder_email_cc', 'marketing@wdta.org.au');
        }
        // For 'none' type, $cc_recipients stays empty
        
        if (!empty($cc_recipients)) {
            $cc_emails = array_map('trim', explode(',', $cc_recipients));
            foreach ($cc_emails as $cc_email) {
                if (is_email($cc_email)) {
                    $headers[] = 'Cc: ' . $cc_email;
                }
            }
        }
        
        return $headers;
    }
    
    /**
     * Get email header HTML
     */
    private static function get_email_header() {
        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head><meta charset="UTF-8"></head>';
        $html .= '<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">';
        $html .= '<div style="background-color: #f9f9f9; padding: 20px; border-radius: 5px;">';
        
        return $html;
    }
    
    /**
     * Get email footer HTML
     */
    private static function get_email_footer() {
        $html = '</div>';
        $html .= '<div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 12px;">';
        $html .= '<p>&copy; ' . date('Y') . ' ' . esc_html(get_bloginfo('name')) . '. All rights reserved.</p>';
        $html .= '</div>';
        $html .= '</body>';
        $html .= '</html>';
        
        return $html;
    }
}
