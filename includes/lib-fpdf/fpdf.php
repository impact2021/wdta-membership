<?php
/**
 * Minimal FPDF Implementation for WDTA Membership Receipts
 * Based on FPDF 1.85 (http://www.fpdf.org)
 * License: Permissive (FPDF license)
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FPDF_VERSION', '1.85');

class FPDF {
    protected $page = 0;
    protected $n = 2;
    protected $buffer = '';
    protected $pages = array();
    protected $state = 0;
    protected $compress = true;
    protected $k;
    protected $DefOrientation;
    protected $CurOrientation;
    protected $w, $h;
    protected $wPt, $hPt;
    protected $lMargin, $tMargin, $rMargin, $bMargin;
    protected $x, $y;
    protected $lasth = 0;
    protected $LineWidth;
    protected $fonts = array();
    protected $FontFamily = '';
    protected $FontStyle = '';
    protected $FontSizePt = 12;
    protected $FontSize;
    protected $CurrentFont;
    protected $DrawColor = '0 G';
    protected $FillColor = '0 g';
    protected $TextColor = '0 g';
    protected $ColorFlag = false;
    protected $images = array();
    protected $offsets = array();
    protected $cMargin;
    protected $metadata = array();
    
    function __construct($orientation='P', $unit='mm', $size='A4') {
        // Scale factor
        if ($unit=='mm')
            $this->k = 72/25.4;
        else
            $this->k = 72;
        
        // Page size A4
        $this->w = 595.28/$this->k;
        $this->h = 841.89/$this->k;
        $this->wPt = 595.28;
        $this->hPt = 841.89;
        
        $this->DefOrientation = 'P';
        $this->CurOrientation = 'P';
        
        // Margins (10mm)
        $margin = 10;
        $this->lMargin = $margin;
        $this->tMargin = $margin;
        $this->rMargin = $margin;
        $this->bMargin = $margin;
        $this->cMargin = $margin/10;
        
        // Line width
        $this->LineWidth = 0.567/$this->k;
        
        // Metadata
        $this->metadata = array('Producer' => 'FPDF '.FPDF_VERSION);
    }
    
    function AddPage() {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->FontFamily = '';
    }
    
    function SetFont($family, $style='', $size=12) {
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size/$this->k;
        
        $fontkey = $family.$style;
        if (!isset($this->fonts[$fontkey])) {
            $this->fonts[$fontkey] = array('i' => count($this->fonts)+1, 'type' => 'core', 'name' => $this->_getfontname($family, $style));
        }
        $this->CurrentFont = &$this->fonts[$fontkey];
        
        if ($this->page > 0) {
            $this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
        }
    }
    
    function SetFillColor($r, $g=null, $b=null) {
        if ($g===null)
            $this->FillColor = sprintf('%.3F g', $r/255);
        else
            $this->FillColor = sprintf('%.3F %.3F %.3F rg', $r/255, $g/255, $b/255);
        $this->ColorFlag = true;
        if ($this->page > 0)
            $this->_out($this->FillColor);
    }
    
    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false) {
        $k = $this->k;
        if ($w==0)
            $w = $this->w - $this->rMargin - $this->x;
        
        $s = '';
        if ($fill || $border==1) {
            $op = $fill ? ($border==1 ? 'B' : 'f') : 'S';
            $s = sprintf('%.2F %.2F %.2F %.2F re %s ', $this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k, $op);
        }
        
        if ($txt!=='') {
            $dx = $align=='R' ? $w - $this->cMargin - $this->GetStringWidth($txt) : ($align=='C' ? ($w - $this->GetStringWidth($txt))/2 : $this->cMargin);
            $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET', ($this->x+$dx)*$k, ($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k, $this->_escape($txt));
        }
        
        if ($s)
            $this->_out($s);
        
        $this->lasth = $h;
        if ($ln>0) {
            $this->y += $h;
            if ($ln==1)
                $this->x = $this->lMargin;
        } else
            $this->x += $w;
    }
    
    function MultiCell($w, $h, $txt, $border=0, $align='L') {
        if ($w==0)
            $w = $this->w - $this->rMargin - $this->x;
        
        $wmax = ($w - 2*$this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $lines = explode("\n", $s);
        
        foreach ($lines as $line) {
            $this->Cell($w, $h, $line, $border, 1, $align);
        }
        
        $this->x = $this->lMargin;
    }
    
    function Ln($h=null) {
        $this->x = $this->lMargin;
        $this->y += ($h===null ? $this->lasth : $h);
    }
    
    function GetStringWidth($s) {
        return strlen($s) * $this->FontSize * 0.5;
    }
    
    function SetY($y) {
        $this->y = $y;
        $this->x = $this->lMargin;
    }
    
    function Image($file, $x, $y, $w=0, $h=0) {
        if (!isset($this->images[$file])) {
            $info = $this->_parseimage($file);
            $info['i'] = count($this->images) + 1;
            $this->images[$file] = $info;
        } else {
            $info = $this->images[$file];
        }
        
        if ($w==0 && $h==0) {
            $w = $info['w'] / $this->k * 72/96;
            $h = $info['h'] / $this->k * 72/96;
        }
        if ($w==0)
            $w = $h * $info['w'] / $info['h'];
        if ($h==0)
            $h = $w * $info['h'] / $info['w'];
        
        $this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q', $w*$this->k, $h*$this->k, $x*$this->k, ($this->h-($y+$h))*$this->k, $info['i']));
    }
    
    function Output($dest='I', $name='doc.pdf') {
        if ($this->state < 3)
            $this->Close();
        
        if ($dest=='S')
            return $this->buffer;
        if ($dest=='I') {
            header('Content-Type: application/pdf');
            header('Content-Length: '.strlen($this->buffer));
            echo $this->buffer;
        } elseif ($dest=='D') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="'.$name.'"');
            echo $this->buffer;
        } elseif ($dest=='F') {
            file_put_contents($name, $this->buffer);
        }
        return '';
    }
    
    function Close() {
        if ($this->state==3)
            return;
        if ($this->page==0)
            $this->AddPage();
        $this->_enddoc();
    }
    
    protected function _parseimage($file) {
        $info = getimagesize($file);
        if (!$info)
            die('Cannot get image info: '.$file);
        
        if ($info[2]==2) { // JPEG
            return array('w'=>$info[0], 'h'=>$info[1], 'cs'=>'DeviceRGB', 'bpc'=>8, 'f'=>'DCTDecode', 'data'=>file_get_contents($file));
        } elseif ($info[2]==3) { // PNG
            return array('w'=>$info[0], 'h'=>$info[1], 'cs'=>'DeviceRGB', 'bpc'=>8, 'f'=>'FlateDecode', 'data'=>'');
        }
        die('Unsupported image type: '.$file);
    }
    
    protected function _getfontname($family, $style) {
        $family = strtolower($family);
        $style = strtoupper($style);
        
        if ($family=='arial' || $family=='helvetica') {
            if ($style=='B') return 'Helvetica-Bold';
            if ($style=='I') return 'Helvetica-Oblique';
            if ($style=='BI') return 'Helvetica-BoldOblique';
            return 'Helvetica';
        }
        return 'Helvetica';
    }
    
    protected function _escape($s) {
        return str_replace(array('\\','(',')',"\r"), array('\\\\','\\(','\\)','\\r'), $s);
    }
    
    protected function _out($s) {
        if ($this->state==2)
            $this->pages[$this->page] .= $s."\n";
        else
            $this->buffer .= $s."\n";
    }
    
    protected function _put($s) {
        $this->buffer .= $s."\n";
    }
    
    protected function _newobj() {
        $this->n++;
        $this->offsets[$this->n] = strlen($this->buffer);
        $this->_put($this->n.' 0 obj');
    }
    
    protected function _enddoc() {
        $this->_putheader();
        $this->_putpages();
        $this->_putresources();
        
        // Info
        $this->_newobj();
        $this->_put('<<');
        $this->_put('/Producer (FPDF '.FPDF_VERSION.')');
        $this->_put('/CreationDate (D:'.date('YmdHis').')');
        $this->_put('>>');
        $this->_put('endobj');
        
        // Catalog
        $this->_newobj();
        $this->_put('<<');
        $this->_put('/Type /Catalog');
        $this->_put('/Pages 1 0 R');
        $this->_put('>>');
        $this->_put('endobj');
        
        // Cross-ref
        $o = strlen($this->buffer);
        $this->_put('xref');
        $this->_put('0 '.($this->n+1));
        $this->_put('0000000000 65535 f ');
        for ($i=1; $i<=$this->n; $i++)
            $this->_put(sprintf('%010d 00000 n ', $this->offsets[$i]));
        
        // Trailer
        $this->_put('trailer');
        $this->_put('<<');
        $this->_put('/Size '.($this->n+1));
        $this->_put('/Root '.$this->n.' 0 R');
        $this->_put('/Info '.($this->n-1).' 0 R');
        $this->_put('>>');
        $this->_put('startxref');
        $this->_put($o);
        $this->_put('%%EOF');
        $this->state = 3;
    }
    
    protected function _putheader() {
        $this->_put('%PDF-1.3');
    }
    
    protected function _putpages() {
        $nb = $this->page;
        for ($n=1; $n<=$nb; $n++) {
            $this->_newobj();
            $this->_put('<</Type /Page');
            $this->_put('/Parent 1 0 R');
            $this->_put('/MediaBox [0 0 '.$this->wPt.' '.$this->hPt.']');
            $this->_put('/Resources 2 0 R');
            $this->_put('/Contents '.($this->n+1).' 0 R>>');
            $this->_put('endobj');
            
            // Page content
            $this->_newobj();
            $p = ($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
            $this->_put('<<'.($this->compress ? '/Filter /FlateDecode ' : '').'/Length '.strlen($p).'>>');
            $this->_put('stream');
            $this->_put($p);
            $this->_put('endstream');
            $this->_put('endobj');
        }
        
        // Pages root
        $this->offsets[1] = strlen($this->buffer);
        $this->_put('1 0 obj');
        $this->_put('<</Type /Pages');
        $kids = '/Kids [';
        for ($i=1; $i<=$nb; $i++)
            $kids .= (3+2*($i-1)).' 0 R ';
        $this->_put($kids.']');
        $this->_put('/Count '.$nb);
        $this->_put('>>');
        $this->_put('endobj');
    }
    
    protected function _putresources() {
        $this->_putfonts();
        $this->_putimages();
        
        // Resource dictionary
        $this->offsets[2] = strlen($this->buffer);
        $this->_put('2 0 obj');
        $this->_put('<<');
        $this->_put('/ProcSet [/PDF /Text /ImageC]');
        $this->_put('/Font <<');
        foreach ($this->fonts as $font)
            $this->_put('/F'.$font['i'].' '.$font['i'].' 0 R');
        $this->_put('>>');
        if (count($this->images)>0) {
            $this->_put('/XObject <<');
            foreach ($this->images as $image)
                $this->_put('/I'.$image['i'].' '.$image['i'].' 0 R');
            $this->_put('>>');
        }
        $this->_put('>>');
        $this->_put('endobj');
    }
    
    protected function _putfonts() {
        foreach ($this->fonts as $font) {
            $this->_newobj();
            $this->_put('<</Type /Font');
            $this->_put('/BaseFont /'.$font['name']);
            $this->_put('/Subtype /Type1');
            $this->_put('/Encoding /WinAnsiEncoding');
            $this->_put('>>');
            $this->_put('endobj');
        }
    }
    
    protected function _putimages() {
        foreach ($this->images as $file=>$info) {
            $this->_newobj();
            $this->_put('<</Type /XObject');
            $this->_put('/Subtype /Image');
            $this->_put('/Width '.$info['w']);
            $this->_put('/Height '.$info['h']);
            $this->_put('/ColorSpace /'.$info['cs']);
            $this->_put('/BitsPerComponent '.$info['bpc']);
            $this->_put('/Filter /'.$info['f']);
            $this->_put('/Length '.strlen($info['data']).'>>');
            $this->_put('stream');
            $this->_put($info['data']);
            $this->_put('endstream');
            $this->_put('endobj');
        }
    }
}
