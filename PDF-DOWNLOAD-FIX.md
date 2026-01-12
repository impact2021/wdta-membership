# PDF Download Fix - January 2026

## Issue Description
When clicking the "Download PDF" button in the WDTA admin receipts page, users received an error message: "An error occurred. Please try again."

## Root Cause Analysis

The PDF generation was failing due to **three critical issues** in the minimal FPDF library implementation:

### Issue 1: Image Object References Bug
This was the same bug that was previously fixed for fonts, but it still existed for images:

**The Problem:**
- When images are added to a PDF, they're assigned sequential indices (1, 2, 3...)
- Later, during PDF finalization, these images are written as PDF objects and get actual object numbers (e.g., 5, 6, 7...)
- The resource dictionary needs to reference these actual object numbers
- BUT: The code was using the sequential index instead of the actual object number

**Example:**
```
Resource Dictionary (BEFORE FIX):
/XObject <<
  /I1 1 0 R    <- Points to object 1 (wrong!)
>>

Object 1: Pages root (not an image!)
Object 7: Image XObject (actual image location!)
```

**After Fix:**
```
Resource Dictionary:
/XObject <<
  /I1 7 0 R    <- Correctly points to object 7
>>

Object 7: Image XObject (correct!)
```

### Issue 2: Missing FPDF Methods
The minimal FPDF implementation was missing several methods that the receipt generator relies on:
- `SetTextColor($r, $g, $b)` - For colored text in headers and totals
- `SetDrawColor($r, $g, $b)` - For colored borders and lines
- `SetLineWidth($width)` - For decorative lines
- `Line($x1, $y1, $x2, $y2)` - For drawing separator lines
- `GetY()` - For positioning elements

Without these methods, the receipt generation would fail with "Call to undefined method" errors.

### Issue 3: Missing Partial Border Support
The receipt uses complex table layouts with partial borders like:
- `'LTB'` - Left, Top, Bottom (no right border for adjacent cells)
- `'RTB'` - Right, Top, Bottom
- `'LTRB'` - All borders

The Cell method only supported:
- `0` - No border
- `1` - Full border

Partial border strings were completely ignored, resulting in tables without proper cell borders.

## Solutions Implemented

### Fix 1: Image Object References
**Files Changed:** `includes/lib-fpdf/fpdf.php`

**In `_putimages()` method (line ~465):**
```php
protected function _putimages() {
    foreach ($this->images as $file=>$info) {
        $this->_newobj();
        // Store actual object number AFTER _newobj() increments $this->n
        $this->images[$file]['n'] = $this->n;
        // ... rest of image output
    }
}
```

**In `_putresources()` method (line ~394):**
```php
if (count($this->images)>0) {
    $this->_put('/XObject <<');
    foreach ($this->images as $image)
        $this->_put('/I'.$image['i'].' '.$image['n'].' 0 R');  // Use 'n' not 'i'
    $this->_put('>>');
}
```

This mirrors the existing fix for fonts.

### Fix 2: Added Missing FPDF Methods
**Files Changed:** `includes/lib-fpdf/fpdf.php`

Added the following methods after `SetFillColor()`:

```php
function SetTextColor($r, $g=null, $b=null) {
    // Validate and clamp RGB values to 0-255 range
    $r = max(0, min(255, intval($r)));
    if ($g !== null) {
        $g = max(0, min(255, intval($g)));
        $b = max(0, min(255, intval($b)));
    }
    
    if ($g===null)
        $this->TextColor = sprintf('%.3F g', $r/255);
    else
        $this->TextColor = sprintf('%.3F %.3F %.3F rg', $r/255, $g/255, $b/255);
}

function SetDrawColor($r, $g=null, $b=null) {
    // Validate and clamp RGB values to 0-255 range
    $r = max(0, min(255, intval($r)));
    if ($g !== null) {
        $g = max(0, min(255, intval($g)));
        $b = max(0, min(255, intval($b)));
    }
    
    if ($g===null)
        $this->DrawColor = sprintf('%.3F G', $r/255);
    else
        $this->DrawColor = sprintf('%.3F %.3F %.3F RG', $r/255, $g/255, $b/255);
    if ($this->page > 0)
        $this->_out($this->DrawColor);
}

function SetLineWidth($width) {
    // Validate line width is a positive number
    $width = max(0.001, floatval($width));
    $this->LineWidth = $width;
    if ($this->page > 0)
        $this->_out(sprintf('%.2F w', $width*$this->k));
}

function Line($x1, $y1, $x2, $y2) {
    $this->_out(sprintf('%.2F %.2F m %.2F %.2F l S', 
        $x1*$this->k, ($this->h-$y1)*$this->k, 
        $x2*$this->k, ($this->h-$y2)*$this->k));
}

function GetY() {
    return $this->y;
}
```

### Fix 3: Partial Border Support
**Files Changed:** `includes/lib-fpdf/fpdf.php`

Enhanced the `Cell()` method to handle string border parameters:

```php
function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false) {
    $k = $this->k;
    if ($w==0)
        $w = $this->w - $this->rMargin - $this->x;
    
    $s = '';
    
    // Handle borders
    if (is_string($border)) {
        $x = $this->x;
        $y = $this->y;
        if (strpos($border,'L')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$k, ($this->h-$y)*$k, $x*$k, ($this->h-($y+$h))*$k);
        if (strpos($border,'T')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-$y)*$k);
        if (strpos($border,'R')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', ($x+$w)*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
        if (strpos($border,'B')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$k, ($this->h-($y+$h))*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
    }
    
    // Handle fill and simple border
    if ($fill || $border==1) {
        $op = $fill ? ($border==1 ? 'B' : 'f') : 'S';
        $s .= sprintf('%.2F %.2F %.2F %.2F re %s ', $this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k, $op);
    }
    
    // ... rest of Cell method
}
```

## Testing Performed

### Automated Tests
1. **PHP Syntax Validation** ✅
   - No syntax errors in modified files
   
2. **Basic PDF Generation** ✅
   - Created test PDF with images and text
   - PDF size: 1216 bytes
   - Valid PDF 1.3 header
   
3. **Full Receipt Generation** ✅
   - Mocked WordPress environment
   - Generated complete receipt with all sections
   - PDF size: 2589 bytes
   - All formatting, colors, and borders included

4. **Object Reference Verification** ✅
   - Font references: `/F1 5 0 R`, `/F2 6 0 R`, `/F3 7 0 R` (correct)
   - Image references: `/I1 7 0 R` (when images present, correct)
   - Resource dictionary points to actual object numbers

5. **Code Review** ✅
   - Addressed all review feedback
   - Added input validation for RGB values (0-255 range)
   - Added validation for line width (positive values)
   - Added clarifying comments

6. **Security Scan** ✅
   - CodeQL analysis: No issues found

### Expected User Impact
After this fix:
- ✅ "Download PDF" button works correctly
- ✅ PDF receipts are generated with all content visible
- ✅ Tables display with proper borders
- ✅ Colors and formatting are applied correctly
- ✅ Receipt includes logo (if configured and available)
- ✅ All text content is readable in PDF viewers

## Files Changed

Only one file was modified:
- `includes/lib-fpdf/fpdf.php` - Fixed image references, added missing methods, enhanced border support

## Backward Compatibility

All changes are backward compatible:
- Existing numeric border values (0, 1) still work
- Color methods support both grayscale (single value) and RGB (three values)
- Object reference fix doesn't change PDF structure, just fixes incorrect references
- No changes to public APIs or function signatures

## Security Considerations

- Added input validation to prevent invalid PDF color specifications
- RGB values are clamped to valid range (0-255)
- Line width validated to be positive
- No SQL queries or external data sources involved
- Changes only affect PDF generation logic

## Verification Steps

To verify the fix works:

1. Log in to WordPress admin
2. Navigate to **WDTA Membership > Receipts**
3. Find a membership with completed payment status
4. Click the **"Download PDF"** button
5. PDF should download successfully
6. Open the PDF in any PDF reader
7. Verify all content is visible:
   - Organization header with contact details
   - "TAX RECEIPT" title
   - Receipt details table (Receipt Number, Payment Date, etc.)
   - Member information table
   - Payment breakdown table with borders
   - Footer information

## Related Documentation

- **PDF-FIX-DOCUMENTATION.md** - Previous font object reference fix
- **PDF-RECEIPT-FEATURE.md** - Overall PDF receipt feature documentation
