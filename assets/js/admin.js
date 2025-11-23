/**
 * Admin JavaScript for WDTA Membership plugin
 */

jQuery(document).ready(function($) {
    
    // Approve membership
    $('.wdta-approve-membership').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var userId = button.data('user-id');
        var year = button.data('year');
        
        if (!confirm('Are you sure you want to approve this membership?')) {
            return;
        }
        
        button.prop('disabled', true).text('Processing...');
        
        $.ajax({
            url: wdtaAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'wdta_approve_membership',
                nonce: wdtaAdmin.nonce,
                user_id: userId,
                year: year
            },
            success: function(response) {
                if (response.success) {
                    alert('Membership approved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                    button.prop('disabled', false).text('Approve');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                button.prop('disabled', false).text('Approve');
            }
        });
    });
    
    // Reject membership
    $('.wdta-reject-membership').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var userId = button.data('user-id');
        var year = button.data('year');
        
        if (!confirm('Are you sure you want to reject this membership?')) {
            return;
        }
        
        button.prop('disabled', true).text('Processing...');
        
        $.ajax({
            url: wdtaAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'wdta_reject_membership',
                nonce: wdtaAdmin.nonce,
                user_id: userId,
                year: year
            },
            success: function(response) {
                if (response.success) {
                    alert('Membership rejected.');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                    button.prop('disabled', false).text('Reject');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                button.prop('disabled', false).text('Reject');
            }
        });
    });
    
});
