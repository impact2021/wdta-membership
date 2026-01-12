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
     * Get logo URL from settings or use default
     */
    private static function get_logo_url() {
        return get_option('wdta_org_logo_url', 'https://www.wdta.org.au/wp-content/uploads/2025/11/Workplace-Drug-Testing-Association.png');
    }
    
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
        $logo_url = self::get_logo_url();
        
        $upload_dir = wp_upload_dir();
        $logo_cache_dir = $upload_dir['basedir'] . '/wdta-receipts';
        
        // Create a unique filename based on the URL to handle logo changes
        $logo_filename = 'wdta-logo-' . md5($logo_url) . '.png';
        $logo_cache_file = $logo_cache_dir . '/' . $logo_filename;
        
        // Create cache directory if it doesn't exist
        if (!file_exists($logo_cache_dir)) {
            wp_mkdir_p($logo_cache_dir);
        }
        
        // Download logo if not cached or older than 7 days
        if (!file_exists($logo_cache_file) || (time() - filemtime($logo_cache_file)) > (7 * 24 * 60 * 60)) {
            $response = wp_remote_get($logo_url, array(
                'timeout' => 30,
                'sslverify' => true
            ));
            
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $image_data = wp_remote_retrieve_body($response);
                if (!empty($image_data)) {
                    file_put_contents($logo_cache_file, $image_data);
                } else {
                    error_log('WDTA PDF Receipt: Downloaded logo is empty from URL: ' . $logo_url);
                }
            } else {
                $error_message = is_wp_error($response) ? $response->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code($response);
                error_log('WDTA PDF Receipt: Failed to download logo from URL: ' . $logo_url . ' - Error: ' . $error_message);
            }
        }
        
        // Verify the cached file is valid before returning
        if (file_exists($logo_cache_file)) {
            // Check if file is actually a valid image (suppress warnings for corrupted files)
            $image_info = @getimagesize($logo_cache_file);
            if ($image_info !== false) {
                return $logo_cache_file;
            } else {
                error_log('WDTA PDF Receipt: Cached logo file is not a valid image: ' . $logo_cache_file);
                // Delete invalid file (suppress errors if file is already gone or permission denied)
                if (@unlink($logo_cache_file) === false && file_exists($logo_cache_file)) {
                    error_log('WDTA PDF Receipt: Failed to delete invalid cached logo: ' . $logo_cache_file);
                }
            }
        }
        
        return false;
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
        try {
            $user = get_userdata($user_id);
            if (!$user) {
                error_log('WDTA PDF Receipt: User not found - ID: ' . $user_id);
                return false;
            }
            
            // Get organization details from settings
            $org_name = get_option('wdta_org_name', 'Workplace Drug Testing Association');
            $org_address = get_option('wdta_org_address', '');
            $org_abn = get_option('wdta_org_abn', '');
            $org_phone = get_option('wdta_org_phone', '');
            $org_email = get_option('wdta_org_email', 'admin@wdta.org.au');
            $org_website = get_option('wdta_org_website', 'https://www.wdta.org.au');
            
            // Load FPDF
            self::load_fpdf();
            
            // Create PDF instance
            $pdf = new FPDF();
            $pdf->AddPage();
            
            // Define colors
            $primary_color = array(33, 113, 181); // Professional blue
            $gray_light = array(245, 245, 245);
            $gray_medium = array(200, 200, 200);
            
            // Header with logo
            $logo_path = self::get_logo_path();
            if ($logo_path && file_exists($logo_path)) {
                try {
                    $pdf->Image($logo_path, 15, 15, 60);
                } catch (Exception $e) {
                    // Logo failed, continue without it
                    error_log('WDTA PDF Receipt: Failed to add logo - ' . $e->getMessage());
                }
            } else {
                error_log('WDTA PDF Receipt: Logo not available, generating receipt without logo');
            }
            
            // Organization details in header (top right)
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetTextColor($primary_color[0], $primary_color[1], $primary_color[2]);
            $pdf->SetXY(120, 15);
            $pdf->MultiCell(0, 6, $org_name, 0, 'R');
            
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetTextColor(80, 80, 80);
            
            if (!empty($org_address)) {
                $pdf->SetX(120);
                $pdf->MultiCell(0, 4, $org_address, 0, 'R');
            }
            
            if (!empty($org_phone)) {
                $pdf->SetX(120);
                $pdf->Cell(0, 4, 'Phone: ' . $org_phone, 0, 1, 'R');
            }
            if (!empty($org_email)) {
                $pdf->SetX(120);
                $pdf->Cell(0, 4, 'Email: ' . $org_email, 0, 1, 'R');
            }
            if (!empty($org_website)) {
                $pdf->SetX(120);
                $pdf->Cell(0, 4, 'Web: ' . $org_website, 0, 1, 'R');
            }
            if (!empty($org_abn)) {
                $pdf->SetX(120);
                $pdf->Cell(0, 4, 'ABN: ' . $org_abn, 0, 1, 'R');
            }
            
            $pdf->Ln(10);
            
            // Title with decorative line
            $pdf->SetFont('Arial', 'B', 24);
            $pdf->SetTextColor($primary_color[0], $primary_color[1], $primary_color[2]);
            $pdf->Cell(0, 12, 'TAX RECEIPT', 0, 1, 'C');
            
            // Decorative line under title
            $pdf->SetDrawColor($primary_color[0], $primary_color[1], $primary_color[2]);
            $pdf->SetLineWidth(0.5);
            $pdf->Line(15, $pdf->GetY() + 2, 195, $pdf->GetY() + 2);
            $pdf->Ln(8);
            
            // Reset colors and line width
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetLineWidth(0.2);
            $pdf->SetTextColor(0, 0, 0);
            
            // Receipt details section with styled header
            $pdf->SetFillColor($primary_color[0], $primary_color[1], $primary_color[2]);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, 'Receipt Details', 0, 1, 'L', true);
            
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', '', 10);
            
            // Receipt number
            $receipt_number = 'WDTA-' . $year . '-' . str_pad($membership->id, 6, '0', STR_PAD_LEFT);
            $pdf->SetFillColor($gray_light[0], $gray_light[1], $gray_light[2]);
            $pdf->Cell(70, 7, 'Receipt Number:', 'LTB', 0, 'L', true);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 7, $receipt_number, 'RTB', 1);
            
            // Date issued
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(70, 7, 'Date Issued:', 'LB', 0, 'L', true);
            $pdf->Cell(0, 7, wdta_format_date(current_time('mysql')), 'RB', 1);
            
            // Payment date
            $payment_date = !empty($membership->payment_date) ? wdta_format_date($membership->payment_date) : wdta_format_date(current_time('mysql'));
            $pdf->Cell(70, 7, 'Payment Date:', 'LB', 0, 'L', true);
            $pdf->Cell(0, 7, $payment_date, 'RB', 1);
            
            // Payment method
            $payment_method = $membership->payment_method === 'stripe' ? 'Credit Card (Stripe)' : 'Bank Transfer';
            $pdf->Cell(70, 7, 'Payment Method:', 'LB', 0, 'L', true);
            $pdf->Cell(0, 7, $payment_method, 'RB', 1);
            
            $pdf->Ln(5);
            
            // Member information section with styled header
            $pdf->SetFillColor($primary_color[0], $primary_color[1], $primary_color[2]);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, 'Member Information', 0, 1, 'L', true);
            
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetFillColor($gray_light[0], $gray_light[1], $gray_light[2]);
            
            $pdf->Cell(70, 7, 'Member Name:', 'LTB', 0, 'L', true);
            $pdf->Cell(0, 7, $user->display_name, 'RTB', 1);
            
            $pdf->Cell(70, 7, 'Email:', 'LB', 0, 'L', true);
            $pdf->Cell(0, 7, $user->user_email, 'RB', 1);
            
            $pdf->Cell(70, 7, 'Membership Year:', 'LB', 0, 'L', true);
            $pdf->Cell(0, 7, $year, 'RB', 1);
            
            $pdf->Cell(70, 7, 'Valid From:', 'LB', 0, 'L', true);
            $pdf->Cell(0, 7, 'January 1, ' . $year, 'RB', 1);
            
            $pdf->Cell(70, 7, 'Valid Until:', 'LB', 0, 'L', true);
            $expiry_display = !empty($membership->expiry_date) ? wdta_format_date($membership->expiry_date) : 'December 31, ' . $year;
            $pdf->Cell(0, 7, $expiry_display, 'RB', 1);
            
            $pdf->Ln(5);
            
            // Payment breakdown section with styled header
            $pdf->SetFillColor($primary_color[0], $primary_color[1], $primary_color[2]);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, 'Payment Breakdown', 0, 1, 'L', true);
            
            // Table header
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor($gray_medium[0], $gray_medium[1], $gray_medium[2]);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(130, 8, 'Description', 'LTRB', 0, 'L', true);
            $pdf->Cell(0, 8, 'Amount (AUD)', 'LTRB', 1, 'R', true);
            
            // Get membership base price
            $base_price = floatval(get_option('wdta_membership_price', 950.00));
            
            // Membership fee
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(130, 7, 'Annual Membership Fee', 'LR', 0);
            $pdf->Cell(0, 7, '$' . number_format($base_price, 2), 'LR', 1, 'R');
            
            // Stripe surcharge if applicable
            $total = $base_price;
            if ($membership->payment_method === 'stripe') {
                $surcharge = $base_price * 0.022; // 2.2% surcharge
                $pdf->Cell(130, 7, 'Credit Card Processing Fee (2.2%)', 'LR', 0);
                $pdf->Cell(0, 7, '$' . number_format($surcharge, 2), 'LR', 1, 'R');
                $total = $base_price + $surcharge;
            }
            
            // Total with styling
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetFillColor($gray_light[0], $gray_light[1], $gray_light[2]);
            $pdf->Cell(130, 9, 'Total Paid', 'LTRB', 0, 'L', true);
            $pdf->SetTextColor($primary_color[0], $primary_color[1], $primary_color[2]);
            $pdf->Cell(0, 9, '$' . number_format($total, 2), 'LTRB', 1, 'R', true);
            
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Ln(10);
            
            // Footer notes with subtle styling
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetFillColor($gray_light[0], $gray_light[1], $gray_light[2]);
            $pdf->MultiCell(0, 5, 'Thank you for your membership with ' . $org_name . '. This receipt confirms your payment and active membership status for the ' . $year . ' membership year.', 0, 'L');
            
            $pdf->Ln(3);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->MultiCell(0, 4, 'This is a computer-generated receipt and serves as proof of payment. For any queries, please contact us at ' . $org_email . '.', 0, 'L');
            
            // Add footer with page number
            $pdf->SetY(-15);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 10, $org_name . ' | ' . $org_website . ' | Page 1 of 1', 0, 0, 'C');
            
            // Return PDF as string
            return $pdf->Output('S');
        } catch (Exception $e) {
            error_log('WDTA PDF Receipt: Exception during PDF generation - ' . $e->getMessage());
            return false;
        }
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
