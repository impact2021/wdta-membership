# PDF Receipt Fix - January 2026

## Issue Description
The PDF receipts were displaying only table borders and background colors (gray fills), but **no text content** was visible. This made the receipts completely useless as they contained no actual membership or payment information.

## Root Cause Analysis

### The Bug
There was a critical bug in the FPDF library (`includes/lib-fpdf/fpdf.php`) where font object references in the PDF structure were incorrect.

**How PDF Fonts Work:**
1. Fonts are defined as objects in the PDF (e.g., object 5, object 6, etc.)
2. The resource dictionary maps font names (/F1, /F2) to these object numbers
3. The content stream uses these font names (/F1, /F2) to select fonts
4. When a PDF reader encounters `/F1` in the content, it looks up object 5 in the resource dictionary

**The Bug:**
- When fonts were added to `$this->fonts` array, they were assigned an index `i` (1, 2, 3...)
- This index was used both for the font name (/F1, /F2) AND incorrectly for the object reference
- But the actual PDF object numbers were determined later when `_putfonts()` was called
- Due to the PDF structure, font objects ended up at object numbers 5, 6, 7... not 1, 2, 3...
- So `/F1 1 0 R` was pointing to object 1 (Pages root), not the font!
- PDF readers couldn't find the fonts, so text was invisible

**Example of the bug:**
```
Resource Dictionary (BEFORE FIX):
/Font <<
  /F1 1 0 R    <- Points to object 1 (Pages root, not a font!)
  /F2 2 0 R    <- Points to object 2 (Resources, not a font!)
>>

Object 1: Pages root
Object 2: Resources dictionary  
Object 5: Font Helvetica         <- Actual font location!
Object 6: Font Helvetica-Bold    <- Actual font location!
```

## The Fix

### Changes Made

**File: `includes/lib-fpdf/fpdf.php`**

**1. Modified `_putfonts()` method (line ~385):**
```php
protected function _putfonts() {
    foreach ($this->fonts as $k=>$font) {
        $this->_newobj();
        $this->fonts[$k]['n'] = $this->n;  // Store actual object number
        $this->_put('<</Type /Font');
        $this->_put('/BaseFont /'.$font['name']);
        $this->_put('/Subtype /Type1');
        $this->_put('/Encoding /WinAnsiEncoding');
        $this->_put('>>');
        $this->_put('endobj');
    }
}
```

**2. Modified `_putresources()` method (line ~362):**
```php
$this->_put('/Font <<');
foreach ($this->fonts as $font)
    $this->_put('/F'.$font['i'].' '.$font['n'].' 0 R');  // Use font['n'] instead of font['i']
$this->_put('>>');
```

### Result

**After Fix:**
```
Resource Dictionary:
/Font <<
  /F1 5 0 R    <- Correctly points to object 5 (Helvetica)
  /F2 6 0 R    <- Correctly points to object 6 (Helvetica-Bold)
>>

Object 5: Font Helvetica         <- Correct!
Object 6: Font Helvetica-Bold    <- Correct!
```

## Additional Feature: Resend Receipt Button

Added a "Resend Receipt Email" button on the membership status page for members with completed payments.

### Files Modified/Created:

1. **`templates/membership-status.php`** - Added button HTML for completed payments
2. **`assets/js/frontend.js`** (NEW) - JavaScript handler for button click and AJAX
3. **`includes/class-wdta-membership.php`** - Added AJAX handler method
4. **`assets/css/frontend.css`** - Added styling for button and messages

### How It Works:

1. Button appears only when `payment_status === 'completed'`
2. On click, sends AJAX request to `wdta_resend_receipt_email` action
3. Server validates user identity and payment status
4. Calls `WDTA_Membership_Email::send_payment_confirmation()` to resend email
5. Shows success/error message to user

## Testing Performed

1. Created test PDFs with multiple font styles
2. Verified font object references are correct
3. Confirmed all text content is visible in generated PDFs
4. Tested receipt structure matches documented format

## Expected PDF Output

The receipt PDF now includes ALL of the following sections with visible text:

1. **Header** - Organization logo (if available), name, address, phone, email, website, ABN
2. **Title** - "MEMBERSHIP RECEIPT" in large bold text
3. **Receipt Details** - Receipt number, dates, payment method
4. **Member Information** - Name, email, membership year, validity dates
5. **Payment Breakdown** - Itemized fees and total
6. **Footer** - Thank you message, contact info, page number

All table cells now display their text content, not just borders and background colors.

## Files Changed

- `includes/lib-fpdf/fpdf.php` - Fixed font object references
- `templates/membership-status.php` - Added resend receipt button
- `assets/js/frontend.js` - NEW: Frontend JavaScript for AJAX
- `includes/class-wdta-membership.php` - Added AJAX handler and script enqueuing
- `assets/css/frontend.css` - Added styling for button and messages

## Security Considerations

- Resend receipt action requires user authentication
- Nonce verification for AJAX requests
- User can only resend their own receipts
- Only available for completed payments

## Verification Steps

To verify the fix:

1. Generate a PDF receipt (admin or frontend)
2. Open the PDF in any PDF reader
3. Verify all text is visible:
   - Organization details in header
   - Receipt title
   - All table cell labels and values
   - Member information
   - Payment amounts
   - Footer text

4. Test resend button (if logged in with completed payment):
   - Button should be visible on membership status page
   - Click should send email with PDF attachment
   - Success message should appear
