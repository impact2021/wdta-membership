/**
 * Frontend JavaScript for WDTA Membership plugin
 */

jQuery(document).ready(function($) {
    // Resend receipt email button handler
    $(document).on('click', '.wdta-resend-receipt-btn', function(e) {
        e.preventDefault();
        
        // Check if wdtaFrontend is defined
        if (typeof wdtaFrontend === 'undefined') {
            alert('Error: Frontend scripts not loaded properly. Please refresh the page.');
            return;
        }
        
        var button = $(this);
        var userId = button.data('user-id');
        var year = button.data('year');
        var messageSpan = button.siblings('.wdta-receipt-message');
        var originalText = button.text();
        
        // Disable button and show loading state
        button.prop('disabled', true).text('Sending...');
        messageSpan.hide().removeClass('wdta-success wdta-error');
        
        $.ajax({
            url: wdtaFrontend.ajaxurl,
            type: 'POST',
            data: {
                action: 'wdta_resend_receipt_email',
                nonce: wdtaFrontend.nonce,
                user_id: userId,
                year: year
            },
            success: function(response) {
                button.prop('disabled', false).text(originalText);
                
                if (response.success) {
                    messageSpan.addClass('wdta-success')
                               .text(response.data.message)
                               .fadeIn();
                    // Hide success message after 5 seconds
                    setTimeout(function() {
                        messageSpan.fadeOut();
                    }, 5000);
                } else {
                    messageSpan.addClass('wdta-error')
                               .text('Error: ' + response.data.message)
                               .fadeIn();
                }
            },
            error: function() {
                button.prop('disabled', false).text(originalText);
                messageSpan.addClass('wdta-error')
                           .text('An error occurred while sending the email. Please try again.')
                           .fadeIn();
            }
        });
    });
});
