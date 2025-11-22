<?php
/**
 * Login form shortcode template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if user is already logged in
if (is_user_logged_in()) {
    echo '<div class="wdta-login-shortcode">';
    echo '<p>You are already logged in. <a href="' . wp_logout_url(get_permalink()) . '">Logout</a></p>';
    echo '</div>';
    return;
}
?>

<div class="wdta-login-shortcode">
    <div class="wdta-login-box-shortcode">
        <h3>Member Login</h3>
        
        <form id="wdta-login-form-shortcode" class="wdta-login-form-inline">
            <div id="wdta-login-message-shortcode"></div>
            
            <div class="wdta-form-group">
                <label for="wdta_username">Username or Email</label>
                <input type="text" id="wdta_username" name="username" required 
                       placeholder="Enter your username or email">
            </div>
            
            <div class="wdta-form-group">
                <label for="wdta_password">Password</label>
                <input type="password" id="wdta_password" name="password" required 
                       placeholder="Enter your password">
            </div>
            
            <div class="wdta-form-group wdta-remember-me">
                <label>
                    <input type="checkbox" id="wdta_remember" name="remember" value="1">
                    Remember Me
                </label>
            </div>
            
            <div class="wdta-form-group">
                <button type="submit" class="wdta-login-button-shortcode" id="wdta-login-submit-shortcode">
                    <span class="button-text">Log In</span>
                    <span class="button-spinner" style="display:none;">●●●</span>
                </button>
            </div>
            
            <div class="wdta-form-footer">
                <a href="<?php echo wp_lostpassword_url(get_permalink()); ?>">Lost your password?</a>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#wdta-login-form-shortcode').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $button = $('#wdta-login-submit-shortcode');
        var $message = $('#wdta-login-message-shortcode');
        
        // Disable button and show spinner
        $button.prop('disabled', true);
        $button.find('.button-text').hide();
        $button.find('.button-spinner').show();
        $message.removeClass('error success').html('');
        
        // Prepare data
        var formData = {
            username: $('#wdta_username').val(),
            password: $('#wdta_password').val(),
            remember: $('#wdta_remember').is(':checked') ? '1' : '0'
        };
        
        // AJAX login request
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php?action=wdta_ajax_login'); ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $message.addClass('success').html(response.data.message || 'Login successful! Redirecting...');
                    // Redirect after short delay
                    setTimeout(function() {
                        window.location.href = response.data.redirect || '<?php echo get_permalink(); ?>';
                    }, 500);
                } else {
                    $message.addClass('error').html(response.data.message || 'Login failed. Please try again.');
                    // Re-enable button
                    $button.prop('disabled', false);
                    $button.find('.button-text').show();
                    $button.find('.button-spinner').hide();
                }
            },
            error: function() {
                $message.addClass('error').html('An error occurred. Please try again.');
                // Re-enable button
                $button.prop('disabled', false);
                $button.find('.button-text').show();
                $button.find('.button-spinner').hide();
            }
        });
    });
});
</script>
