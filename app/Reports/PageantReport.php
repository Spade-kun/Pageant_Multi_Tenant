<?php

namespace App\Reports;

require_once base_path('vendor/setasign/fpdf/fpdf.php');

class PageantReport extends \FPDF
{
    protected $title;
    protected $logoPath;
    protected $institutionName;
    protected $location;

    public function __construct($title, $logoPath, $institutionName, $location)
    {
        // Initialize with Portrait orientation (P), millimeters (mm), and A4 format
        parent::__construct('P', 'mm', 'A4');
        
        $this->title = $title;
        $this->logoPath = $logoPath;
        $this->institutionName = $institutionName;
        $this->location = $location;
        
        // Set document information
        $this->SetCreator('Pageant Management System');
        $this->SetAuthor('BukSU');
        
        // Set compression
        $this->SetCompression(true);
        
        // Set auto page break
        $this->SetAutoPageBreak(true, 15);
        
        // Set default font
        $this->SetFont('Arial', '', 12);
        
        // Initialize page numbering
        $this->AliasNbPages();
    }

    // Page header
    public function Header()
    {
        // Get page width
        $pageWidth = $this->GetPageWidth();
        
        // Set margins
        $leftMargin = 20;
        $this->SetMargins($leftMargin, 20, 20);
        
        // Logo
        if (file_exists($this->logoPath)) {
            $logoWidth = 25;
            $this->Image($this->logoPath, $leftMargin + 30, 15, $logoWidth);
            
            // Calculate text width for centering
            $this->SetFont('Arial', 'B', 14);
            $institutionWidth = $this->GetStringWidth($this->institutionName);
            $this->SetFont('Arial', '', 12);
            $locationWidth = $this->GetStringWidth($this->location);
            
            // Calculate starting X position for both texts
            $textStartX = $leftMargin + $logoWidth + 35;
            $institutionX = $textStartX;
            $locationX = $textStartX + ($institutionWidth - $locationWidth) / 2;
            
            // Institution Name
            $this->SetFont('Arial', 'B', 14);
            $this->SetXY($institutionX, 20);
            $this->Cell($institutionWidth, 6, $this->institutionName, 0, 1);
            
            // Location (centered under institution name)
            $this->SetFont('Arial', '', 12);
            $this->SetXY($locationX, 27);
            $this->Cell($locationWidth, 6, $this->location, 0, 1);
        }
        
        // Add some space before content
        $this->Ln(25);
    }

    // Page footer
    public function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        
        // Set font
        $this->SetFont('Arial', '', 8);
        
        // Calculate widths for each section
        $pageWidth = $this->GetPageWidth();
        $margin = 10;
        $usableWidth = $pageWidth - (2 * $margin);
        
        // Draw line
        $this->Line($margin, $this->GetY(), $pageWidth - $margin, $this->GetY());
        $this->Ln(1);
        
        // Left section - Electronic Generated Report (with less spacing)
        $this->SetX($margin);
        $this->Cell($usableWidth * 0.3, 10, 'Electronic Generated Report', 0, 0, 'L');
        
        // Middle section - Date and Time (adjusted position)
        $this->SetX($margin + ($usableWidth * 0.3));
        $this->Cell($usableWidth * 0.1, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 0, 'C');
        
        // Right section - Page numbers (adjusted position)
        $this->SetX($margin + ($usableWidth * 0.8));
        $this->Cell($usableWidth * 0.2, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }
} 