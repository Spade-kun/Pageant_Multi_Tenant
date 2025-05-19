<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Reports\PageantReport;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function setTenantConnection($slug)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        $databaseName = 'tenant_' . str_replace('-', '_', $tenant->slug);
        
        config(['database.connections.tenant' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    private function drawTable($pdf, $headers, $data, $widths, $heights = 7)
    {
        // Calculate total width
        $totalWidth = array_sum($widths);
        
        // Center the table by calculating starting X position
        $startX = ($pdf->GetPageWidth() - $totalWidth) / 2;
        $pdf->SetX($startX);

        // Draw headers
        $pdf->SetFont('Arial', 'B', 9);
        $x = $startX;
        $y = $pdf->GetY();
        foreach ($headers as $i => $header) {
            $pdf->SetXY($x, $y);
            $pdf->Cell($widths[$i], $heights, $header, 1, 0, 'C');
            $x += $widths[$i];
        }
        $pdf->Ln();

        // Draw data
        $pdf->SetFont('Arial', '', 9);
        foreach ($data as $row) {
            $x = $startX;
            $y = $pdf->GetY();
            $maxHeight = $heights;

            // First pass - calculate maximum height needed
            foreach ($row as $i => $cell) {
                $pdf->SetXY($x, $y);
                $pdf->MultiCell($widths[$i], $heights, $cell, 0);
                $cellHeight = $pdf->GetY() - $y;
                $maxHeight = max($maxHeight, $cellHeight);
                $x += $widths[$i];
            }

            // Second pass - draw cells with proper height
            $x = $startX;
            foreach ($row as $i => $cell) {
                $pdf->SetXY($x, $y);
                $pdf->MultiCell($widths[$i], $maxHeight, $cell, 1, 'L');
                $x += $widths[$i];
            }
            $pdf->SetY($y + $maxHeight);
        }
        $pdf->Ln(5);
    }

    public function generateReport(Request $request, $slug)
    {
        $this->setTenantConnection($slug);
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        
        // Create new PDF document with A4 size
        $pdf = new PageantReport(
            '', 
            public_path('assets/img/buksu_logo.png'),
            'Bukidnon State University',
            'Malaybalay City, Bukidnon'
        );
        
        // Set document properties
        $pdf->SetTitle('Pageant Management System Report');
        $pdf->SetAuthor($tenant->name);
        $pdf->SetCreator('Pageant Management System');
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Fetch data from database
        $events = DB::connection('tenant')->table('events')->get();
        $contestants = DB::connection('tenant')->table('contestants')->get();
        $categories = DB::connection('tenant')->table('categories')->get();
        $judges = DB::connection('tenant')->table('judges')->get();
        $users = DB::connection('tenant')->table('users')->get();
        $scores = DB::connection('tenant')->table('scores')->get();

        // 1. Events Section
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'EVENTS', 0, 1, 'C');
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(5);

        $headers = ['Event Name', 'Start Date', 'End Date', 'Location', 'Status'];
        $widths = [50, 30, 30, 40, 30];
        $data = [];
        foreach ($events as $event) {
            $data[] = [
                $event->name,
                date('Y-m-d', strtotime($event->start_date)),
                date('Y-m-d', strtotime($event->end_date)),
                $event->location,
                $event->status
            ];
        }
        $this->drawTable($pdf, $headers, $data, $widths);

        // 2. Contestants Section
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'CONTESTANTS', 0, 1, 'C');
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(5);

        $headers = ['Name', 'Age', 'Gender', 'Representing', 'Registration Date'];
        $widths = [50, 20, 25, 50, 35];
        $data = [];
        foreach ($contestants as $contestant) {
            $data[] = [
                $contestant->name,
                $contestant->age,
                $contestant->gender,
                $contestant->representing,
                date('Y-m-d', strtotime($contestant->registration_date))
            ];
        }
        $this->drawTable($pdf, $headers, $data, $widths);

        // 3. Categories Section
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'CATEGORIES', 0, 1, 'C');
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(5);

        $headers = ['Category Name', 'Description', 'Percentage'];
        $widths = [50, 90, 30];
        $data = [];
        foreach ($categories as $category) {
            $data[] = [
                $category->name,
                $category->description,
                $category->percentage . '%'
            ];
        }
        $this->drawTable($pdf, $headers, $data, $widths);

        // 4. Judges Section
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'JUDGES', 0, 1, 'C');
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(5);

        $headers = ['Name', 'Email', 'Specialty'];
        $widths = [50, 70, 50];
        $data = [];
        foreach ($judges as $judge) {
            $data[] = [
                $judge->name,
                $judge->email,
                $judge->specialty
            ];
        }
        $this->drawTable($pdf, $headers, $data, $widths);

        // 5. Users Section
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'SYSTEM USERS', 0, 1, 'C');
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(5);

        // Group users by role
        $usersByRole = $users->groupBy('role');
        foreach ($usersByRole as $role => $roleUsers) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, strtoupper($role) . 'S', 0, 1);
            
            $headers = ['Name', 'Email', 'Phone'];
            $widths = [50, 70, 50];
            $data = [];
            foreach ($roleUsers as $user) {
                $data[] = [
                    $user->name,
                    $user->email,
                    $user->phone ?? 'N/A'
                ];
            }
            $this->drawTable($pdf, $headers, $data, $widths);
            $pdf->Ln(5);
        }

        // 6. Scores Summary Section
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'SCORES SUMMARY', 0, 1, 'C');
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(5);

        foreach ($events as $event) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, "Event: {$event->name}", 0, 1);

            $headers = ['Contestant', 'Category', 'Raw Score', 'Weighted', 'Judge'];
            $widths = [45, 45, 30, 30, 30];
            $data = [];
            
            $eventScores = $scores->where('event_id', $event->id);
            foreach ($eventScores as $score) {
                $contestant = $contestants->where('id', $score->contestant_id)->first();
                $category = $categories->where('id', $score->category_id)->first();
                $judge = $judges->where('id', $score->judge_id)->first();

                if ($contestant && $category && $judge) {
                    $data[] = [
                        $contestant->name,
                        $category->name,
                        number_format($score->raw_score, 2),
                        number_format($score->weighted_score, 2),
                        $judge->name
                    ];
                }
            }
            $this->drawTable($pdf, $headers, $data, $widths);
            $pdf->Ln(5);
        }

        // Output the PDF
        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="pageant_report.pdf"'
        ]);
    }
} 