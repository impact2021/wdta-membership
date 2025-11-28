<?php
/**
 * Scheduled Emails admin template
 * Shows upcoming email reminders for the next 3 months and overdue emails
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current date
$now = new DateTime();
$now->setTime(0, 0, 0);

// Get end date (3 months from now)
$end_date = clone $now;
$end_date->modify('+3 months');

// Get start date for checking overdue (up to 6 months ago)
$overdue_start_date = clone $now;
$overdue_start_date->modify('-6 months');

// Get reminders configuration
$reminders = get_option('wdta_email_reminders', array());

// Get sent reminders to check what's already been sent
$sent_reminders = get_option('wdta_sent_reminders', array());

// Determine current and next year
$current_year = (int) date('Y');
$next_year = $current_year + 1;
$previous_year = $current_year - 1;

// Cache for recipients by year to avoid repeated database queries
$recipients_cache = array();

/**
 * Get recipients for a target year with caching
 */
function wdta_get_cached_recipients($target_year, &$cache) {
    if (!isset($cache[$target_year])) {
        $cache[$target_year] = WDTA_Database::get_users_without_membership($target_year);
    }
    return $cache[$target_year];
}

// Build list of scheduled emails
$scheduled_emails = array();

foreach ($reminders as $reminder) {
    // Skip if disabled
    if (empty($reminder['enabled'])) {
        continue;
    }
    
    $timing = intval($reminder['timing']);
    $unit = isset($reminder['unit']) ? $reminder['unit'] : 'days';
    $period = isset($reminder['period']) ? $reminder['period'] : 'before';
    
    // Convert timing to days
    $days = ($unit === 'weeks') ? $timing * 7 : $timing;
    
    // Calculate send dates for previous, current and next year's expiry
    // This ensures we catch overdue emails from previous year
    $expiry_dates = array(
        $previous_year => new DateTime($previous_year . '-12-31'),
        $current_year => new DateTime($current_year . '-12-31'),
        $next_year => new DateTime($next_year . '-12-31')
    );
    
    foreach ($expiry_dates as $expiry_year => $expiry) {
        $send_date = clone $expiry;
        
        // Adjust send date based on period
        if ($period === 'before') {
            $send_date->modify("-{$days} days");
            // For "before" reminders, target year is the year after expiry
            $target_year = $expiry_year + 1;
        } else {
            $send_date->modify("+{$days} days");
            // For "after" reminders, target year is the expiry year
            $target_year = $expiry_year;
        }
        
        // Check if send date is within our window (either upcoming or overdue but not too old)
        $is_upcoming = ($send_date >= $now && $send_date <= $end_date);
        $is_overdue = ($send_date < $now && $send_date >= $overdue_start_date);
        
        if ($is_upcoming || $is_overdue) {
            // Get reminder ID
            $reminder_id = isset($reminder['id']) ? $reminder['id'] : "reminder_{$timing}_{$unit}_{$period}";
            $sent_key = $reminder_id . '_' . $target_year;
            
            // Check if already sent
            $already_sent = isset($sent_reminders[$sent_key]);
            
            if (!$already_sent) {
                // Get recipients using cache
                $recipients = wdta_get_cached_recipients($target_year, $recipients_cache);
                
                // Only add if there are recipients
                // Skip showing scheduled emails that have no recipients to send to
                if (!empty($recipients)) {
                    $scheduled_emails[] = array(
                        'reminder' => $reminder,
                        'send_date' => $send_date,
                        'target_year' => $target_year,
                        'recipients' => $recipients,
                        'already_sent' => false,
                        'is_overdue' => $is_overdue
                    );
                }
            }
        }
    }
}

// Sort by send date (overdue first, then upcoming)
usort($scheduled_emails, function($a, $b) {
    // Overdue emails come first
    if ($a['is_overdue'] && !$b['is_overdue']) {
        return -1;
    }
    if (!$a['is_overdue'] && $b['is_overdue']) {
        return 1;
    }
    // Within same category, sort by date
    return $a['send_date'] <=> $b['send_date'];
});

/**
 * Calculate time until send date
 * @param DateTime $send_date The target send date
 * @param DateTime $current_time The current time to calculate from
 * @return array Array with 'text' for display and 'overdue' boolean
 */
function wdta_time_until($send_date, $current_time) {
    $interval = $current_time->diff($send_date);
    
    // Check if the send date has passed (invert = 1 means current_time > send_date)
    $is_overdue = $interval->invert === 1;
    
    $parts = array();
    
    if ($interval->days > 0) {
        $parts[] = $interval->days . ' day' . ($interval->days !== 1 ? 's' : '');
    }
    if ($interval->h > 0) {
        $parts[] = $interval->h . ' hour' . ($interval->h !== 1 ? 's' : '');
    }
    if ($interval->i > 0) {
        $parts[] = $interval->i . ' minute' . ($interval->i !== 1 ? 's' : '');
    }
    
    if (empty($parts)) {
        // Time difference is minimal (seconds only) - use the overdue check
        return array(
            'text' => $is_overdue ? 'Just now' : 'Now',
            'overdue' => $is_overdue
        );
    }
    
    $time_text = implode(', ', $parts);
    
    if ($is_overdue) {
        return array(
            'text' => $time_text . ' overdue',
            'overdue' => true
        );
    }
    
    return array(
        'text' => $time_text,
        'overdue' => false
    );
}
?>

<div class="wrap">
    <h1>Scheduled Email Reminders</h1>
    
    <p class="description">This page shows overdue and upcoming email reminders (within the next 3 months), who will receive them, and the status of each. Overdue emails can be sent manually using the "Send Now" button.</p>
    
    <?php if (empty($scheduled_emails)) : ?>
        <div class="notice notice-info">
            <p>No email reminders are scheduled or overdue at this time.</p>
        </div>
    <?php else : ?>
        
        <?php 
        // Create a single DateTime instance for time calculations
        $current_time = new DateTime();
        
        foreach ($scheduled_emails as $email) : 
            $reminder = $email['reminder'];
            $send_date = $email['send_date'];
            $recipients = $email['recipients'];
            $target_year = $email['target_year'];
            $time_until = wdta_time_until($send_date, $current_time);
            
            $timing = intval($reminder['timing']);
            $unit = isset($reminder['unit']) ? $reminder['unit'] : 'days';
            $period = isset($reminder['period']) ? $reminder['period'] : 'before';
            $subject = isset($reminder['subject']) ? $reminder['subject'] : 'No subject';
        ?>
        
        <div class="wdta-scheduled-email-card" style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #2271b1; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 300px;">
                    <h3 style="margin-top: 0; margin-bottom: 10px;">
                        <?php echo esc_html($subject); ?>
                    </h3>
                    <p style="color: #646970; margin-bottom: 5px;">
                        <strong>Timing:</strong> 
                        <?php echo esc_html($timing . ' ' . $unit . ' ' . strtoupper($period) . ' Dec 31'); ?>
                    </p>
                    <p style="color: #646970; margin-bottom: 5px;">
                        <strong>For membership year:</strong> <?php echo esc_html($target_year); ?>
                    </p>
                    <p style="color: #646970; margin-bottom: 0;">
                        <strong>Send date:</strong> 
                        <?php echo esc_html($send_date->format('l, F j, Y')); ?>
                    </p>
                </div>
                
                <div style="text-align: right; min-width: 200px;">
                    <div style="background: <?php echo $time_until['overdue'] ? '#fcf0f1' : '#f0f0f1'; ?>; padding: 15px 20px; border-radius: 4px; display: inline-block;">
                        <div style="font-size: 14px; color: <?php echo $time_until['overdue'] ? '#d63638' : '#646970'; ?>; margin-bottom: 5px;">
                            <?php echo $time_until['overdue'] ? 'Overdue' : 'Time until send'; ?>
                        </div>
                        <div style="font-size: 18px; font-weight: 600; color: <?php echo $time_until['overdue'] ? '#d63638' : '#1d2327'; ?>;">
                            <?php echo esc_html($time_until['text']); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #e0e0e0;">
            
            <div>
                <h4 style="margin-top: 0; margin-bottom: 10px;">
                    Recipients (<?php echo count($recipients); ?> user<?php echo count($recipients) !== 1 ? 's' : ''; ?>)
                    <?php if ($time_until['overdue'] && !empty($recipients)) : ?>
                        <button type="button" class="button button-primary wdta-send-all-now" 
                                data-reminder-id="<?php echo esc_attr(isset($reminder['id']) ? $reminder['id'] : "reminder_{$timing}_{$unit}_{$period}"); ?>"
                                data-target-year="<?php echo esc_attr($target_year); ?>"
                                data-user-ids="<?php echo esc_attr(json_encode(array_map(function($u) { return $u->ID; }, $recipients))); ?>"
                                style="margin-left: 10px;">
                            Send All Now
                        </button>
                    <?php endif; ?>
                </h4>
                
                <?php if (empty($recipients)) : ?>
                    <p style="color: #646970; font-style: italic;">No users currently match the criteria for this reminder.</p>
                    <p class="description">Users who had an active membership in <?php echo esc_html($target_year - 1); ?> but don't have a membership for <?php echo esc_html($target_year); ?> will receive this email.</p>
                <?php else : ?>
                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid #e0e0e0; border-radius: 4px;">
                        <table class="widefat striped" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <?php if ($time_until['overdue']) : ?>
                                        <th style="width: 120px;">Action</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recipients as $user) : ?>
                                    <tr data-user-id="<?php echo esc_attr($user->ID); ?>">
                                        <td><?php echo esc_html($user->display_name); ?></td>
                                        <td><?php echo esc_html($user->user_email); ?></td>
                                        <?php if ($time_until['overdue']) : ?>
                                            <td>
                                                <button type="button" class="button button-small wdta-send-now"
                                                        data-user-id="<?php echo esc_attr($user->ID); ?>"
                                                        data-reminder-id="<?php echo esc_attr(isset($reminder['id']) ? $reminder['id'] : "reminder_{$timing}_{$unit}_{$period}"); ?>"
                                                        data-target-year="<?php echo esc_attr($target_year); ?>">
                                                    Send Now
                                                </button>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php endforeach; ?>
        
    <?php endif; ?>
    
    <div class="notice notice-info" style="margin-top: 30px;">
        <p>
            <strong>Note:</strong> The recipient list shows users who currently qualify for reminders based on their membership status. 
            The actual recipients at send time may differ if users renew their membership before then.
        </p>
    </div>
    
    <p style="margin-top: 20px;">
        <a href="<?php echo esc_url(admin_url('admin.php?page=wdta-emails')); ?>" class="button button-secondary">
            &larr; Back to Email Templates
        </a>
    </p>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Send email to single user
    $(document).on('click', '.wdta-send-now', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var userId = button.data('user-id');
        var reminderId = button.data('reminder-id');
        var targetYear = button.data('target-year');
        var originalText = button.text();
        
        if (!confirm('Send this email now?')) {
            return;
        }
        
        button.prop('disabled', true).text('Sending...');
        
        $.ajax({
            url: wdtaAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'wdta_send_scheduled_email',
                nonce: wdtaAdmin.nonce,
                user_id: userId,
                reminder_id: reminderId,
                target_year: targetYear
            },
            success: function(response) {
                if (response.success) {
                    button.text('Sent!').addClass('button-disabled');
                    button.closest('tr').css('background-color', '#d4edda');
                } else {
                    alert('Error: ' + response.data.message);
                    button.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                button.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Send email to all users
    $(document).on('click', '.wdta-send-all-now', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var reminderId = button.data('reminder-id');
        var targetYear = button.data('target-year');
        var userIds = button.data('user-ids');
        var originalText = button.text();
        
        if (!confirm('Send this email to all ' + userIds.length + ' recipients now?')) {
            return;
        }
        
        button.prop('disabled', true).text('Sending...');
        
        var sent = 0;
        var failed = 0;
        var total = userIds.length;
        
        // Send emails one by one
        function sendNext(index) {
            if (index >= userIds.length) {
                // All done
                button.text('Sent ' + sent + '/' + total);
                if (failed > 0) {
                    alert(sent + ' emails sent successfully. ' + failed + ' failed.');
                } else {
                    alert('All ' + sent + ' emails sent successfully!');
                }
                return;
            }
            
            $.ajax({
                url: wdtaAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wdta_send_scheduled_email',
                    nonce: wdtaAdmin.nonce,
                    user_id: userIds[index],
                    reminder_id: reminderId,
                    target_year: targetYear
                },
                success: function(response) {
                    if (response.success) {
                        sent++;
                        // Update individual row
                        var row = $('tr[data-user-id="' + userIds[index] + '"]');
                        row.find('.wdta-send-now').text('Sent!').addClass('button-disabled').prop('disabled', true);
                        row.css('background-color', '#d4edda');
                    } else {
                        failed++;
                    }
                    button.text('Sending... (' + (index + 1) + '/' + total + ')');
                    sendNext(index + 1);
                },
                error: function() {
                    failed++;
                    sendNext(index + 1);
                }
            });
        }
        
        sendNext(0);
    });
});
</script>
