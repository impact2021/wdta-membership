# WDTA Membership PDF Receipt Feature

## Overview

The WDTA Membership plugin now automatically generates and emails PDF receipts when memberships are activated.

## Receipt Structure

### Header
- **WDTA Logo**: Downloaded from https://www.wdta.org.au/wp-content/uploads/2025/11/Workplace-Drug-Testing-Association.png
- **Title**: "MEMBERSHIP RECEIPT" (right-aligned, bold, 20pt)

### Receipt Details Section
```
┌─────────────────────────────────────────────────────┐
│ Receipt Details                                      │
├────────────────────────┬─────────────────────────────┤
│ Receipt Number:        │ WDTA-2026-000123           │
├────────────────────────┼─────────────────────────────┤
│ Payment Date:          │ 11/01/2026                 │
├────────────────────────┼─────────────────────────────┤
│ Payment Method:        │ Credit Card (Stripe)       │
│                        │ or Bank Transfer           │
└────────────────────────┴─────────────────────────────┘
```

### Member Information Section
```
┌─────────────────────────────────────────────────────┐
│ Member Information                                   │
├────────────────────────┬─────────────────────────────┤
│ Member Name:           │ John Smith                 │
├────────────────────────┼─────────────────────────────┤
│ Email:                 │ john.smith@example.com     │
├────────────────────────┼─────────────────────────────┤
│ Membership Year:       │ 2026                       │
├────────────────────────┼─────────────────────────────┤
│ Valid Until:           │ December 31, 2026          │
└────────────────────────┴─────────────────────────────┘
```

### Payment Breakdown Section

**For Stripe Payments:**
```
┌────────────────────────────────────────┬─────────────┐
│ Description                             │ Amount (AUD)│
├────────────────────────────────────────┼─────────────┤
│ Annual Membership Fee                  │ $950.00     │
├────────────────────────────────────────┼─────────────┤
│ Credit Card Processing Fee (2.2%)      │ $20.90      │
├────────────────────────────────────────┼─────────────┤
│ Total Paid                             │ $970.90     │
└────────────────────────────────────────┴─────────────┘
```

**For Bank Transfers:**
```
┌────────────────────────────────────────┬─────────────┐
│ Description                             │ Amount (AUD)│
├────────────────────────────────────────┼─────────────┤
│ Annual Membership Fee                  │ $950.00     │
├────────────────────────────────────────┼─────────────┤
│ Total Paid                             │ $950.00     │
└────────────────────────────────────────┴─────────────┘
```

### Footer Notes
- Thank you message confirming membership activation
- Contact information for queries (admin@wdta.org.au)
- Note that it's a computer-generated receipt
- WDTA website URL at bottom of page

## Technical Implementation

### Files Added
1. **includes/class-wdta-pdf-receipt.php** - Main PDF receipt generator class
2. **includes/lib-fpdf/fpdf.php** - Minimal FPDF library for PDF generation

### Integration Points
1. **Stripe Payments** (`class-wdta-payment-stripe.php`)
   - Generates PDF after successful payment
   - Attaches to confirmation email
   
2. **Bank Transfer Approvals** (`class-wdta-payment-bank.php`)
   - Generates PDF when admin approves payment
   - Attaches to approval confirmation email

### Features
- **Automatic Generation**: No manual intervention required
- **Email Attachment**: PDF automatically attached to confirmation emails
- **Logo Caching**: WDTA logo is cached locally for 30 days to improve performance
- **Temporary Storage**: PDF files are created temporarily and deleted after email is sent
- **Professional Format**: A4 size, professional layout with tables and formatting

### Email Integration
The confirmation email templates have been updated to include:
> "A PDF receipt is attached to this email for your records."

### Receipt Numbering
Format: `WDTA-{YEAR}-{PADDED_ID}`
Example: `WDTA-2026-000123`

Where:
- YEAR = Membership year
- PADDED_ID = Membership ID padded to 6 digits with leading zeros

## Usage

### For Members
Members will automatically receive a PDF receipt attached to their confirmation email when:
1. They complete a Stripe payment (immediate)
2. Their bank transfer payment is approved by admin

### For Administrators
No configuration needed! The feature works automatically once the plugin is updated to version 3.7.

### Receipt Storage
- PDFs are generated on-demand when emails are sent
- Temporarily stored in `wp-content/uploads/wdta-receipts/`
- Automatically cleaned up after email is sent
- WDTA logo cached in same directory for performance

## Testing

Tested successfully with:
- ✅ PDF generation from membership data
- ✅ Receipt number formatting
- ✅ File saving and cleanup
- ✅ Valid PDF structure (PDF 1.3 format)
- ✅ All receipt sections properly formatted

Test output:
```
✅ SUCCESS: PDF generated successfully
   - PDF size: 1937 bytes
   - Receipt format: WDTA-2026-000123
✅ SUCCESS: PDF saved successfully
✅ SUCCESS: Filename generated: WDTA-Receipt-2026-John-Smith.pdf
```
