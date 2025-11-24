/**
 * Admin JavaScript for WDTA Membership plugin
 */

jQuery(document).ready(function($) {
    console.log('WDTA Admin JS loaded');
    console.log('wdtaAdmin object:', wdtaAdmin);
    console.log('Edit buttons found:', $('.wdta-edit-membership').length);
    
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
    
    // Edit membership - open modal
    $('.wdta-edit-membership').on('click', function(e) {
        e.preventDefault();
        console.log('Edit button clicked');
        
        var button = $(this);
        var userId = button.data('user-id');
        var year = button.data('year');
        var paymentStatus = button.data('payment-status');
        var status = button.data('status');
        var paymentAmount = button.data('payment-amount');
        var expiryDate = button.data('expiry-date');
        
        console.log('User ID:', userId, 'Year:', year, 'Expiry:', expiryDate);
        
        // Populate form
        $('#edit-user-id').val(userId);
        $('#edit-year').val(year);
        $('#edit-payment-status').val(paymentStatus);
        $('#edit-status').val(status);
        $('#edit-payment-amount').val(paymentAmount);
        $('#edit-expiry-date').val(expiryDate);
        
        console.log('Showing modal');
        // Show modal
        $('#wdta-edit-membership-modal').show();
    });
    
    // Close modal
    $('.wdta-modal-close').on('click', function(e) {
        e.preventDefault();
        $('#wdta-edit-membership-modal').hide();
    });
    
    // Close modal on overlay click
    $('.wdta-modal-overlay').on('click', function() {
        $('#wdta-edit-membership-modal').hide();
    });
    
    // Edit membership - save changes
    $('#wdta-edit-membership-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var originalText = submitButton.text();
        
        submitButton.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: wdtaAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'wdta_update_membership',
                nonce: wdtaAdmin.nonce,
                user_id: $('#edit-user-id').val(),
                year: $('#edit-year').val(),
                payment_status: $('#edit-payment-status').val(),
                status: $('#edit-status').val(),
                payment_amount: $('#edit-payment-amount').val(),
                expiry_date: $('#edit-expiry-date').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('Membership updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                    submitButton.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                submitButton.prop('disabled', false).text(originalText);
            }
        });
    });
    
});
