<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $action == 'lost_password' ? 'Lost Password' : 'Member Login'; ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body class="wdta-custom-login-page">
    <div class="wdta-login-wrapper">
        <div class="wdta-login-container">
            <div class="wdta-login-header">
                <?php if (has_custom_logo()): ?>
                    <div class="wdta-login-logo">
                        <?php the_custom_logo(); ?>
                    </div>
                <?php else: ?>
                    <h1><?php bloginfo('name'); ?></h1>
                <?php endif; ?>
            </div>
            
            <?php if ($action == 'lost_password'): ?>
                <!-- Lost Password Form -->
                <div class="wdta-login-box">
                    <h2>Reset Password</h2>
                    <p>Enter your username or email address and we'll send you a link to reset your password.</p>
                    
                    <form id="wdta-lost-password-form" class="wdta-login-form">
                        <div id="wdta-login-message"></div>
                        
                        <div class="wdta-form-group">
                            <label for="user_login">Username or Email</label>
                            <input type="text" id="user_login" name="user_login" required 
                                   placeholder="Enter your username or email">
                        </div>
                        
                        <div class="wdta-form-group">
                            <button type="submit" class="wdta-login-button" id="wdta-lost-password-submit">
                                <span class="button-text">Reset Password</span>
                                <span class="button-spinner" style="display:none;">●●●</span>
                            </button>
                        </div>
                        
                        <div class="wdta-form-footer">
                            <a href="<?php echo home_url('/member-login/'); ?>">← Back to Login</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- Login Form -->
                <div class="wdta-login-box">
                    <h2>Member Login</h2>
                    
                    <form id="wdta-login-form" class="wdta-login-form">
                        <div id="wdta-login-message"></div>
                        
                        <div class="wdta-form-group">
                            <label for="username">Username or Email</label>
                            <input type="text" id="username" name="username" required 
                                   placeholder="Enter your username or email">
                        </div>
                        
                        <div class="wdta-form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required 
                                   placeholder="Enter your password">
                        </div>
                        
                        <div class="wdta-form-group wdta-remember-me">
                            <label>
                                <input type="checkbox" name="remember" id="remember" value="1">
                                Remember me
                            </label>
                        </div>
                        
                        <div class="wdta-form-group">
                            <button type="submit" class="wdta-login-button" id="wdta-login-submit">
                                <span class="button-text">Log In</span>
                                <span class="button-spinner" style="display:none;">●●●</span>
                            </button>
                        </div>
                        
                        <div class="wdta-form-footer">
                            <a href="<?php echo home_url('/member-login/lost-password/'); ?>">Lost your password?</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <div class="wdta-login-footer">
                <p><a href="<?php echo home_url(); ?>">← Back to <?php bloginfo('name'); ?></a></p>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Login form
        $('#wdta-login-form').on('submit', function(e) {
            e.preventDefault();
            
            var submitBtn = $('#wdta-login-submit');
            var buttonText = submitBtn.find('.button-text');
            var spinner = submitBtn.find('.button-spinner');
            
            submitBtn.prop('disabled', true);
            buttonText.hide();
            spinner.show();
            $('#wdta-login-message').html('');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'wdta_custom_login',
                    nonce: '<?php echo wp_create_nonce('wdta_login_nonce'); ?>',
                    username: $('#username').val(),
                    password: $('#password').val(),
                    remember: $('#remember').is(':checked')
                },
                success: function(response) {
                    if (response.success) {
                        $('#wdta-login-message').html('<div class="wdta-success">Login successful! Redirecting...</div>');
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 500);
                    } else {
                        $('#wdta-login-message').html('<div class="wdta-error">' + response.data.message + '</div>');
                        submitBtn.prop('disabled', false);
                        buttonText.show();
                        spinner.hide();
                    }
                },
                error: function() {
                    $('#wdta-login-message').html('<div class="wdta-error">An error occurred. Please try again.</div>');
                    submitBtn.prop('disabled', false);
                    buttonText.show();
                    spinner.hide();
                }
            });
        });
        
        // Lost password form
        $('#wdta-lost-password-form').on('submit', function(e) {
            e.preventDefault();
            
            var submitBtn = $('#wdta-lost-password-submit');
            var buttonText = submitBtn.find('.button-text');
            var spinner = submitBtn.find('.button-spinner');
            
            submitBtn.prop('disabled', true);
            buttonText.hide();
            spinner.show();
            $('#wdta-login-message').html('');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'wdta_lost_password',
                    nonce: '<?php echo wp_create_nonce('wdta_login_nonce'); ?>',
                    user_login: $('#user_login').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#wdta-login-message').html('<div class="wdta-success">' + response.data.message + '</div>');
                        $('#wdta-lost-password-form')[0].reset();
                    } else {
                        $('#wdta-login-message').html('<div class="wdta-error">' + response.data.message + '</div>');
                    }
                    submitBtn.prop('disabled', false);
                    buttonText.show();
                    spinner.hide();
                },
                error: function() {
                    $('#wdta-login-message').html('<div class="wdta-error">An error occurred. Please try again.</div>');
                    submitBtn.prop('disabled', false);
                    buttonText.show();
                    spinner.hide();
                }
            });
        });
    });
    </script>
    
    <?php wp_footer(); ?>
</body>
</html>
