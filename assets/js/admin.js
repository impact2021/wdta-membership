/**
 * Admin JavaScript for WDTA Membership plugin
 */

jQuery(document).ready(function($) {
    // Add membership - open modal (using event delegation)
    // This handler doesn't require wdtaAdmin, so register it first
    $(document).on('click', '.wdta-add-membership', function(e) {
        e.preventDefault();
        
        // Reset form
        $('#wdta-add-membership-form')[0].reset();
        
        // Set default values
        var currentYear = new Date().getFullYear();
        $('#add-year').val(currentYear);
        $('#add-expiry-date').val(currentYear + '-12-31');
        $('#add-payment-amount').val('950.00');
        
        // Show modal by adding class
        $('#wdta-add-membership-modal').addClass('wdta-modal-active');
    });
    
    // Update expiry date when year changes
    $(document).on('change', '#add-year', function() {
        var selectedYear = $(this).val();
        $('#add-expiry-date').val(selectedYear + '-12-31');
    });
    
    // Close add membership modal (using event delegation)
    $(document).on('click', '#wdta-add-membership-modal .wdta-modal-close, #wdta-add-membership-modal .wdta-modal-overlay', function(e) {
        e.preventDefault();
        $('#wdta-add-membership-modal').removeClass('wdta-modal-active');
    });
    
    // Edit membership - open modal (using event delegation)
    $(document).on('click', '.wdta-edit-membership', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var userId = button.data('user-id');
        var year = button.data('year');
        var paymentStatus = button.data('payment-status');
        var status = button.data('status');
        var paymentAmount = button.data('payment-amount');
        var expiryDate = button.data('expiry-date');
        
        // Populate form
        $('#edit-user-id').val(userId);
        $('#edit-year').val(year);
        $('#edit-payment-status').val(paymentStatus);
        $('#edit-status').val(status);
        $('#edit-payment-amount').val(paymentAmount);
        $('#edit-expiry-date').val(expiryDate);
        
        // Show modal by adding class
        $('#wdta-edit-membership-modal').addClass('wdta-modal-active');
    });
    
    // Close edit membership modal (using event delegation)
    $(document).on('click', '#wdta-edit-membership-modal .wdta-modal-close', function(e) {
        e.preventDefault();
        $('#wdta-edit-membership-modal').removeClass('wdta-modal-active');
    });
    
    // Close edit membership modal on overlay click (using event delegation)
    $(document).on('click', '#wdta-edit-membership-modal .wdta-modal-overlay', function() {
        $('#wdta-edit-membership-modal').removeClass('wdta-modal-active');
    });
    
    // Check if wdtaAdmin is defined for AJAX operations
    if (typeof wdtaAdmin === 'undefined') {
        console.error('wdtaAdmin object is not defined! Ajax calls will fail.');
        return;
    }
    
    // Approve membership (using event delegation)
    $(document).on('click', '.wdta-approve-membership', function(e) {
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
    
    // Reject membership (using event delegation)
    $(document).on('click', '.wdta-reject-membership', function(e) {
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
    
    // Edit membership - save changes (using event delegation)
    $(document).on('submit', '#wdta-edit-membership-form', function(e) {
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
    
    // Delete membership (using event delegation)
    $(document).on('click', '.wdta-delete-membership', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var userId = button.data('user-id');
        var year = button.data('year');
        
        if (!confirm('Are you sure you want to delete this membership? This action cannot be undone.')) {
            return;
        }
        
        button.prop('disabled', true).text('Deleting...');
        
        $.ajax({
            url: wdtaAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'wdta_delete_membership',
                nonce: wdtaAdmin.nonce,
                user_id: userId,
                year: year
            },
            success: function(response) {
                if (response.success) {
                    alert('Membership deleted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                    button.prop('disabled', false).text('Delete');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                button.prop('disabled', false).text('Delete');
            }
        });
    });
    
    // Add membership - save (using event delegation)
    $(document).on('submit', '#wdta-add-membership-form', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var originalText = submitButton.text();
        
        // Validate user selection
        var userId = $('#add-user-id').val();
        if (!userId) {
            alert('Please select a user');
            return;
        }
        
        submitButton.prop('disabled', true).text('Adding...');
        
        $.ajax({
            url: wdtaAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'wdta_add_membership',
                nonce: wdtaAdmin.nonce,
                user_id: userId,
                year: $('#add-year').val(),
                payment_method: $('#add-payment-method').val(),
                payment_status: $('#add-payment-status').val(),
                status: $('#add-status').val(),
                payment_amount: $('#add-payment-amount').val(),
                expiry_date: $('#add-expiry-date').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('Membership added successfully!');
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
