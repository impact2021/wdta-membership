/**
 * WDTA Membership Frontend JavaScript
 */

jQuery(document).ready(function($) {
    
    // Handle membership registration form
    $('#wdta-membership-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $button = $form.find('.wdta-submit-button');
        var $message = $form.find('.wdta-form-message');
        var originalButtonText = $button.text();
        
        // Disable button and show loading
        $button.prop('disabled', true).html(originalButtonText + ' <span class="wdta-loading"></span>');
        $message.hide().removeClass('success error');
        
        // Prepare form data
        var formData = $form.serialize();
        
        // Send AJAX request
        $.ajax({
            url: wdtaMembership.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $message.addClass('success').html(response.data.message).show();
                    $form[0].reset();
                    
                    // Redirect if provided
                    if (response.data.redirect) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 1500);
                    }
                } else {
                    $message.addClass('error').html(response.data.message).show();
                    $button.prop('disabled', false).text(originalButtonText);
                }
            },
            error: function() {
                $message.addClass('error').html('An error occurred. Please try again.').show();
                $button.prop('disabled', false).text(originalButtonText);
            }
        });
    });
    
    // Handle membership renewal form
    $('#wdta-renewal-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $button = $form.find('.wdta-submit-button');
        var $message = $form.find('.wdta-form-message');
        var originalButtonText = $button.text();
        
        // Disable button and show loading
        $button.prop('disabled', true).html(originalButtonText + ' <span class="wdta-loading"></span>');
        $message.hide().removeClass('success error');
        
        // Prepare form data
        var formData = $form.serialize();
        
        // Send AJAX request
        $.ajax({
            url: wdtaMembership.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $message.addClass('success').html(response.data.message).show();
                    
                    // Redirect if provided
                    if (response.data.redirect) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 1500);
                    }
                } else {
                    $message.addClass('error').html(response.data.message).show();
                    $button.prop('disabled', false).text(originalButtonText);
                }
            },
            error: function() {
                $message.addClass('error').html('An error occurred. Please try again.').show();
                $button.prop('disabled', false).text(originalButtonText);
            }
        });
    });
});
