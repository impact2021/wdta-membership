<?php
/**
 * PDF Receipt Generator for WDTA Membership
 * 
 * Generates PDF receipts for membership payments using a lightweight PDF library
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDTA_PDF_Receipt {
    
    /**
     * WDTA logo URL
     */
    const LOGO_URL = 'https://www.wdta.org.au/wp-content/uploads/2025/11/Workplace-Drug-Testing-Association.png';
    
    /**
     * Load FPDF library
     */
    private static function load_fpdf() {
        if (!class_exists('FPDF')) {
            require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/lib-fpdf/fpdf.php';
        }
    }
    
    /**
     * Download and cache logo
     */
    private static function get_logo_path() {
        $upload_dir = wp_upload_dir();
        $logo_cache_dir = $upload_dir['basedir'] . '/wdta-receipts';
        $logo_cache_file = $logo_cache_dir . '/wdta-logo.png';
        
        // Create cache directory if it doesn't exist
        if (!file_exists($logo_cache_dir)) {
            wp_mkdir_p($logo_cache_dir);
        }
        
        // Download logo if not cached or older than 30 days
        if (!file_exists($logo_cache_file) || (time() - filemtime($logo_cache_file)) > (30 * 24 * 60 * 60)) {
            $response = wp_remote_get(self::LOGO_URL, array(
                'timeout' => 30,
                'sslverify' => true
            ));
            
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $image_data = wp_remote_retrieve_body($response);
                file_put_contents($logo_cache_file, $image_data);
            }
        }
        
        return file_exists($logo_cache_file) ? $logo_cache_file : false;
    }
    
    /**
     * Generate receipt PDF
     * 
     * @param int $user_id User ID
     * @param int $year Membership year
     * @param object $membership Membership data
     * @return string|false PDF content or false on failure
     */
    public static function generate_receipt($user_id, $year, $membership) {
        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }
        
        // Load FPDF
        self::load_fpdf();
        
        // Create PDF instance
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Header with logo
        $logo_path = self::get_logo_path();
        if ($logo_path && file_exists($logo_path)) {
            try {
                $pdf->Image($logo_path, 10, 10, 50);
            } catch (Exception $e) {
                // Logo failed, continue without it
                error_log('WDTA PDF Receipt: Failed to add logo - ' . $e->getMessage());
            }
        }
        
        // Title
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, 'MEMBERSHIP RECEIPT', 0, 1, 'R');
        $pdf->Ln(10);
        
        // Receipt details section
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Receipt Details', 0, 1);
        $pdf->SetFont('Arial', '', 11);
        
        // Receipt number
        $receipt_number = 'WDTA-' . $year . '-' . str_pad($membership->id, 6, '0', STR_PAD_LEFT);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(70, 8, 'Receipt Number:', 1, 0, 'L', true);
        $pdf->Cell(0, 8, $receipt_number, 1, 1);
        
        // Date
        $payment_date = !empty($membership->payment_date) ? wdta_format_date($membership->payment_date) : wdta_format_date(current_time('mysql'));
        $pdf->Cell(70, 8, 'Payment Date:', 1, 0, 'L', true);
        $pdf->Cell(0, 8, $payment_date, 1, 1);
        
        // Payment method
        $payment_method = $membership->payment_method === 'stripe' ? 'Credit Card (Stripe)' : 'Bank Transfer';
        $pdf->Cell(70, 8, 'Payment Method:', 1, 0, 'L', true);
        $pdf->Cell(0, 8, $payment_method, 1, 1);
        
        $pdf->Ln(5);
        
        // Member information section
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Member Information', 0, 1);
        $pdf->SetFont('Arial', '', 11);
        
        $pdf->Cell(70, 8, 'Member Name:', 1, 0, 'L', true);
        $pdf->Cell(0, 8, $user->display_name, 1, 1);
        
        $pdf->Cell(70, 8, 'Email:', 1, 0, 'L', true);
        $pdf->Cell(0, 8, $user->user_email, 1, 1);
        
        $pdf->Cell(70, 8, 'Membership Year:', 1, 0, 'L', true);
        $pdf->Cell(0, 8, $year, 1, 1);
        
        $pdf->Cell(70, 8, 'Valid Until:', 1, 0, 'L', true);
        $pdf->Cell(0, 8, 'December 31, ' . $year, 1, 1);
        
        $pdf->Ln(5);
        
        // Payment breakdown section
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Payment Breakdown', 0, 1);
        
        // Table header
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(120, 8, 'Description', 1, 0, 'L', true);
        $pdf->Cell(0, 8, 'Amount (AUD)', 1, 1, 'R', true);
        
        // Get membership base price
        $base_price = floatval(get_option('wdta_membership_price', 950.00));
        
        // Membership fee
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(120, 8, 'Annual Membership Fee', 1, 0);
        $pdf->Cell(0, 8, '$' . number_format($base_price, 2), 1, 1, 'R');
        
        // Stripe surcharge if applicable
        $total = $base_price;
        if ($membership->payment_method === 'stripe') {
            $surcharge = $base_price * 0.022; // 2.2% surcharge
            $pdf->Cell(120, 8, 'Credit Card Processing Fee (2.2%)', 1, 0);
            $pdf->Cell(0, 8, '$' . number_format($surcharge, 2), 1, 1, 'R');
            $total = $base_price + $surcharge;
        }
        
        // Total
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(120, 10, 'Total Paid', 1, 0, 'L', true);
        $pdf->Cell(0, 10, '$' . number_format($total, 2), 1, 1, 'R', true);
        
        $pdf->Ln(8);
        
        // Footer notes
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(0, 5, 'Thank you for your membership with the Workplace Drug Testing Association. This receipt confirms your payment and active membership status for the ' . $year . ' membership year.');
        
        $pdf->Ln(3);
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->MultiCell(0, 4, 'This is a computer-generated receipt and serves as proof of payment. For any queries, please contact us at admin@wdta.org.au');
        
        // Add footer
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Workplace Drug Testing Association - www.wdta.org.au - Page 1', 0, 0, 'C');
        
        // Return PDF as string
        return $pdf->Output('S');
    }
    
    /**
     * Save receipt to file
     * 
     * @param int $user_id User ID
     * @param int $year Membership year
     * @param object $membership Membership data
     * @return string|false File path or false on failure
     */
    public static function save_receipt($user_id, $year, $membership) {
        $pdf_content = self::generate_receipt($user_id, $year, $membership);
        
        if (!$pdf_content) {
            return false;
        }
        
        // Create receipts directory
        $upload_dir = wp_upload_dir();
        $receipts_dir = $upload_dir['basedir'] . '/wdta-receipts';
        
        if (!file_exists($receipts_dir)) {
            wp_mkdir_p($receipts_dir);
        }
        
        // Generate filename
        $filename = 'receipt-' . $year . '-user-' . $user_id . '-' . time() . '.pdf';
        $file_path = $receipts_dir . '/' . $filename;
        
        // Save PDF to file
        file_put_contents($file_path, $pdf_content);
        
        return $file_path;
    }
    
    /**
     * Get receipt filename for attachment
     * 
     * @param int $user_id User ID
     * @param int $year Membership year
     * @return string Filename
     */
    public static function get_receipt_filename($user_id, $year) {
        $user = get_userdata($user_id);
        $name = !empty($user->display_name) ? sanitize_file_name($user->display_name) : 'Member';
        return 'WDTA-Receipt-' . $year . '-' . $name . '.pdf';
    }
}
