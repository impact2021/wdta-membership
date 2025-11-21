# Inline Stripe Payment Form

## Overview

The WDTA Membership plugin now features an **inline Stripe payment form** that allows users to enter their credit card details directly on your WordPress site without being redirected to Stripe's checkout page.

## Features

### ✨ Key Benefits

- **Seamless Experience**: Users never leave your site
- **Real-time Validation**: Instant feedback on card input
- **Secure Processing**: PCI-compliant via Stripe Elements
- **Professional Design**: Branded styling with Stripe colors
- **Mobile Responsive**: Works perfectly on all devices
- **Error Handling**: Clear error messages for users
- **Loading States**: Visual feedback during payment processing

## Visual Layout

```
┌─────────────────────────────────────────────────────────┐
│  WDTA Membership - 2024                                 │
│  Annual membership fee: $950 AUD                        │
│  Payment must be received by March 31, 2024             │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  Choose Payment Method:                                  │
│                                                          │
│  ┌────────────────────────────────────────────────────┐ │
│  │ Pay with Credit Card                      [Stripe] │ │
│  │ Secure payment via Stripe                          │ │
│  │                                                     │ │
│  │ ┌─────────────────────────────────────────────────┐│ │
│  │ │ Card Number                                      ││ │
│  │ │ MM/YY    CVC    ZIP                              ││ │
│  │ └─────────────────────────────────────────────────┘│ │
│  │                                                     │ │
│  │ [    Pay $950 AUD    ]  ← Inline payment button   │ │
│  └────────────────────────────────────────────────────┘ │
│                                                          │
│                      OR                                  │
│                                                          │
│  ┌────────────────────────────────────────────────────┐ │
│  │ Pay via Bank Transfer                               │ │
│  │                                                     │ │
│  │ Bank Details:                                       │ │
│  │ • Bank: Your Bank Name                              │ │
│  │ • Account: WDTA Account                             │ │
│  │ • BSB: 123-456                                      │ │
│  │ • Account Number: 12345678                          │ │
│  │                                                     │ │
│  │ [Submit Bank Transfer Details]                      │ │
│  └────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

## Technical Implementation

### Stripe Elements Integration

The payment form uses **Stripe Elements**, which provides:

1. **Hosted Input Fields**: Stripe securely hosts the card input fields in an iframe
2. **Automatic Formatting**: Card numbers, expiry dates, and CVCs are auto-formatted
3. **Built-in Validation**: Real-time validation of card information
4. **PCI Compliance**: No card data touches your server

### Payment Flow

```
User enters card details
        ↓
Stripe Elements validates
        ↓
User clicks "Pay $950 AUD"
        ↓
Plugin creates PaymentIntent
        ↓
Stripe processes payment
        ↓
Success → Activate membership
        ↓
Redirect to success message
```

## Configuration

### Requirements

1. **Stripe Account**: Sign up at https://stripe.com
2. **API Keys**: Get from Stripe Dashboard → Developers → API keys
3. **Plugin Settings**: Enter keys in WDTA Membership → Settings

### Setup Steps

1. **Add Stripe Publishable Key**:
   ```
   WDTA Membership → Settings → Stripe Payment Settings
   Publishable Key: pk_test_... (or pk_live_... for production)
   ```

2. **Add Stripe Secret Key**:
   ```
   Secret Key: sk_test_... (or sk_live_... for production)
   ```

3. **Test the Form**:
   - Use test card: `4242 4242 4242 4242`
   - Any future expiry date
   - Any 3-digit CVC
   - Any ZIP code

## Code Structure

### Files Modified

1. **includes/class-wdta-payment-stripe.php**
   - Added `create_payment_intent()` method
   - Added `enqueue_stripe_scripts()` method
   - Enqueues Stripe.js library

2. **templates/membership-form.php**
   - Added inline card element container
   - Stripe Elements initialization JavaScript
   - Payment confirmation handling

3. **assets/css/frontend.css** (NEW)
   - Styling for card input element
   - Payment form layout
   - Error message styling
   - Responsive design

4. **includes/class-wdta-membership.php**
   - Added frontend CSS enqueuing

### JavaScript Flow

```javascript
// 1. Initialize Stripe with public key
var stripe = Stripe(wdtaStripe.publicKey);
var elements = stripe.elements();

// 2. Create and mount card element
var cardElement = elements.create('card', {style: customStyle});
cardElement.mount('#wdta-card-element');

// 3. Create PaymentIntent via AJAX
$.ajax({
    action: 'wdta_create_payment_intent',
    // Returns clientSecret
});

// 4. On form submit, confirm payment
stripe.confirmCardPayment(clientSecret, {
    payment_method: {
        card: cardElement,
        billing_details: { email, name }
    }
});

// 5. Handle success/error
if (result.paymentIntent.status === 'succeeded') {
    // Show success message
    // Reload page to show active membership
}
```

## Styling

### Customization

The card element can be customized in the template file:

```javascript
var style = {
    base: {
        color: '#32325d',
        fontFamily: 'Your-Font-Family',
        fontSize: '16px',
        '::placeholder': {
            color: '#aab7c4'
        }
    },
    invalid: {
        color: '#fa755a',
        iconColor: '#fa755a'
    }
};
```

### CSS Classes

- `.wdta-card-element` - Card input container
- `.wdta-stripe-payment` - Payment option wrapper
- `#wdta-card-errors` - Error message display
- `#wdta-stripe-submit` - Submit button

## Error Handling

### Real-time Validation

- Invalid card number → "Your card number is incomplete"
- Invalid expiry → "Your card's expiration date is incomplete"
- Invalid CVC → "Your card's security code is incomplete"

### Payment Errors

- Insufficient funds → Clear error message displayed
- Card declined → User-friendly explanation
- Network error → Retry prompt

### Visual Feedback

```
During Payment:
[Processing...] ← Button disabled, spinner shown

Success:
✓ Payment successful! Your membership is now active.

Error:
⚠ Your card was declined. Please try another card.
```

## Security

### PCI Compliance

- **No Card Data on Server**: Card information never touches your WordPress server
- **Stripe Hosted Fields**: All sensitive data is handled by Stripe
- **Secure Communication**: All data encrypted in transit
- **Tokenization**: Only tokens are stored, never raw card data

### Best Practices

- Use HTTPS (SSL certificate required)
- Keep Stripe API keys secure
- Never log card information
- Regular security updates

## Testing

### Test Cards

| Card Number | Scenario |
|------------|----------|
| 4242 4242 4242 4242 | Successful payment |
| 4000 0000 0000 0002 | Card declined |
| 4000 0000 0000 9995 | Insufficient funds |
| 4000 0000 0000 0069 | Expired card |

### Test Mode

1. Use test API keys (pk_test_... and sk_test_...)
2. No real money is charged
3. Full payment flow is simulated
4. Switch to live keys for production

## Production Deployment

### Checklist

- [ ] Switch to live API keys (pk_live_... and sk_live_...)
- [ ] Verify SSL certificate is active
- [ ] Test with real payment (small amount)
- [ ] Verify webhook is configured
- [ ] Check email notifications work
- [ ] Test on mobile devices
- [ ] Verify error handling

### Going Live

1. **Update API Keys**:
   ```
   WDTA Membership → Settings
   Switch from test keys to live keys
   ```

2. **Test Payment**:
   - Make a test payment with real card
   - Verify membership is activated
   - Check confirmation email arrives

3. **Monitor**:
   - Watch Stripe Dashboard for payments
   - Check WordPress error logs
   - Monitor member feedback

## Support

### Common Issues

**"Payment system not properly initialized"**
- Verify Stripe public key is set in settings
- Check browser console for JavaScript errors
- Ensure Stripe.js is loading

**Card element not showing**
- Check frontend.css is loaded
- Verify page has `[wdta_membership_form]` shortcode
- Check for JavaScript conflicts

**Payment not processing**
- Verify secret key is set
- Check webhook is configured
- Review WordPress debug.log

### Debugging

Enable WordPress debug mode in wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check logs at: `wp-content/debug.log`

## Benefits Over Redirect

### Inline Form vs. Stripe Checkout

| Feature | Inline Form | Stripe Checkout |
|---------|-------------|-----------------|
| User stays on site | ✅ Yes | ❌ No (redirect) |
| Branding control | ✅ Full control | ⚠️ Limited |
| Mobile experience | ✅ Seamless | ⚠️ Extra steps |
| Customization | ✅ Extensive | ⚠️ Limited |
| Setup complexity | ⚠️ More code | ✅ Simple |
| PCI compliance | ✅ Stripe Elements | ✅ Stripe hosted |

## Future Enhancements

Possible additions:

- [ ] Apple Pay / Google Pay buttons
- [ ] Save card for future payments
- [ ] Payment plans / installments
- [ ] Multiple currency support
- [ ] Invoice generation
- [ ] Receipt download

---

## Quick Reference

**Shortcode**: `[wdta_membership_form]`

**Test Card**: 4242 4242 4242 4242

**Settings**: WDTA Membership → Settings → Stripe Payment Settings

**Documentation**: See README.md and INSTALL.md

**Support**: Check WordPress debug.log for errors
