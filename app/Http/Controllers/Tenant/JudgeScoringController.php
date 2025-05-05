<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Score;

class JudgeScoringController extends Controller
{
    private function setTenantConnection($slug)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        $databaseName = 'tenant_' . str_replace('-', '_', $tenant->slug);
        
        Config::set('database.connections.tenant', [
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
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public function index($slug)
    {
        $this->setTenantConnection($slug);
        
        // Get the current judge's email from the session
        $judgeEmail = session('tenant_user.email');
        $judge = DB::connection('tenant')
            ->table('judges')
            ->where('email', $judgeEmail)
            ->first();

        if (!$judge) {
            return redirect()->back()->with('error', 'Judge not found.');
        }
        
        // Get all event assignments that need scoring
        $assignments = [];
        
        // Get all event assignments
        $eventAssignments = DB::connection('tenant')
            ->table('event_contestant_categories as ecc')
            ->join('events as e', 'e.id', '=', 'ecc.event_id')
            ->join('contestants as c', 'c.id', '=', 'ecc.contestant_id')
            ->join('categories as cat', 'cat.id', '=', 'ecc.category_id')
            ->leftJoin('scores as s', function($join) use ($judge) {
                $join->on('s.event_id', '=', 'ecc.event_id')
                     ->on('s.contestant_id', '=', 'ecc.contestant_id')
                     ->on('s.category_id', '=', 'ecc.category_id')
                     ->where('s.judge_id', '=', $judge->id);
            })
            ->select(
                'ecc.event_id',
                'ecc.contestant_id',
                'ecc.category_id',
                'e.name as event_name',
                'c.name as contestant_name',
                'cat.name as category_name',
                's.weighted_score'
            )
            ->where('ecc.status', 'confirmed')
            ->get();

        // Organize the data by event
        foreach ($eventAssignments as $assignment) {
            if (!isset($assignments[$assignment->event_id])) {
                $assignments[$assignment->event_id] = [
                    'event_name' => $assignment->event_name,
                    'contestants' => [],
                    'categories' => [],
                    'scores' => []
                ];
            }
            
            $assignments[$assignment->event_id]['contestants'][$assignment->contestant_id] = $assignment->contestant_name;
            $assignments[$assignment->event_id]['categories'][$assignment->category_id] = $assignment->category_name;
            
            if ($assignment->weighted_score !== null) {
                if (!isset($assignments[$assignment->event_id]['scores'][$assignment->category_id])) {
                    $assignments[$assignment->event_id]['scores'][$assignment->category_id] = [];
                }
                $assignments[$assignment->event_id]['scores'][$assignment->category_id][$assignment->contestant_id] = $assignment->weighted_score;
            }
        }

        return view('tenant.judges.scoring.index', compact('assignments', 'slug'));
    }

    public function score($slug, $eventId, $contestantId, $categoryId)
    {
        $this->setTenantConnection($slug);
        
        $judgeEmail = session('tenant_user.email');
        $judge = DB::connection('tenant')
            ->table('judges')
            ->where('email', $judgeEmail)
            ->first();

        if (!$judge) {
            return redirect()->back()->with('error', 'Judge not found.');
        }

        // Fetch event data
        $event = DB::connection('tenant')
            ->table('events')
            ->where('id', $eventId)
            ->first();

        // Fetch contestant data
        $contestant = DB::connection('tenant')
            ->table('contestants')
            ->where('id', $contestantId)
            ->first();

        // Fetch category data
        $category = DB::connection('tenant')
            ->table('categories')
            ->where('id', $categoryId)
            ->first();

        if (!$event || !$contestant || !$category) {
            return redirect()->back()->with('error', 'Required data not found.');
        }

        // Fetch existing score if any
        $existingScore = DB::connection('tenant')
            ->table('scores')
            ->where('event_id', $eventId)
            ->where('contestant_id', $contestantId)
            ->where('category_id', $categoryId)
            ->where('judge_id', $judge->id)
            ->first();

        return view('tenant.judges.scoring.score', compact(
            'event',
            'contestant',
            'category',
            'existingScore',
            'slug'
        ));
    }

    public function store(Request $request, $slug)
    {
        $this->setTenantConnection($slug);
        
        $request->validate([
            'event_id' => 'required|exists:tenant.events,id',
            'contestant_id' => 'required|exists:tenant.contestants,id',
            'category_id' => 'required|exists:tenant.categories,id',
            'raw_score' => 'required|numeric|min:1|max:10',
            'comments' => 'nullable|string'
        ]);

        $category = DB::connection('tenant')
            ->table('categories')
            ->where('id', $request->category_id)
            ->first();

        if (!$category) {
            return redirect()->back()->with('error', 'Category not found.');
        }

        $rawScore = $request->raw_score;
        // Scale the 1-10 score to percentage basis for weighting calculation
        $scaledScore = ($rawScore / 10) * 100; 
        $weightedScore = ($scaledScore * $category->percentage) / 100;

        $judgeEmail = session('tenant_user.email');
        $judge = DB::connection('tenant')
            ->table('judges')
            ->where('email', $judgeEmail)
            ->first();

        if (!$judge) {
            return redirect()->back()->with('error', 'Judge not found.');
        }

        try {
            $now = now();
            
            // Check if score already exists
            $existingScore = DB::connection('tenant')
                ->table('scores')
                ->where([
                    'contestant_id' => $request->contestant_id,
                    'judge_id' => $judge->id,
                    'category_id' => $request->category_id,
                    'event_id' => $request->event_id
                ])
                ->first();

            if ($existingScore) {
                // Update existing score
                DB::connection('tenant')->table('scores')
                    ->where([
                        'contestant_id' => $request->contestant_id,
                        'judge_id' => $judge->id,
                        'category_id' => $request->category_id,
                        'event_id' => $request->event_id
                    ])
                    ->update([
                        'raw_score' => $rawScore,
                        'weighted_score' => $weightedScore,
                        'comments' => $request->comments,
                        'updated_at' => $now
                    ]);
            } else {
                // Insert new score
                DB::connection('tenant')->table('scores')->insert([
                    'contestant_id' => $request->contestant_id,
                    'judge_id' => $judge->id,
                    'category_id' => $request->category_id,
                    'event_id' => $request->event_id,
                    'raw_score' => $rawScore,
                    'weighted_score' => $weightedScore,
                    'comments' => $request->comments,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }

            return redirect()
                ->route('tenant.judges.scoring.index', ['slug' => $slug])
                ->with('success', 'Score has been saved successfully.');
        } catch (\Exception $e) {
            \Log::error('Score save error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to save score. Please try again.')
                ->withInput();
        }
    }
} 