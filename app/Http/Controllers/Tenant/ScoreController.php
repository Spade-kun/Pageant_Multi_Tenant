<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScoreController extends Controller
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

    public function index($slug)
    {
        $this->setTenantConnection($slug);
        $tenant = Tenant::where('slug', $slug)->firstOrFail();

        // Fetch all required data
        $events = DB::connection('tenant')->table('events')
            ->orderBy('start_date', 'desc')
            ->get();

        $categories = DB::connection('tenant')->table('categories')
            ->orderBy('display_order')
            ->get();

        $contestants = DB::connection('tenant')->table('contestants')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $judges = DB::connection('tenant')->table('judges')
            ->orderBy('name')
            ->get();

        $scores = DB::connection('tenant')->table('scores')
            ->select(
                'scores.*',
                'contestants.name as contestant_name',
                'categories.name as category_name',
                'judges.name as judge_name'
            )
            ->join('contestants', 'scores.contestant_id', '=', 'contestants.id')
            ->join('categories', 'scores.category_id', '=', 'categories.id')
            ->join('judges', 'scores.judge_id', '=', 'judges.id')
            ->orderBy('scores.created_at', 'desc')
            ->get();

        return view('tenant.scores.index', compact(
            'slug',
            'tenant',
            'events',
            'categories',
            'contestants',
            'judges',
            'scores'
        ));
    }

    public function show($slug, $id)
    {
        $this->setTenantConnection($slug);

        $score = DB::connection('tenant')->table('scores')
            ->select(
                'scores.*',
                'contestants.name as contestant_name',
                'contestants.representing',
                'categories.name as category_name',
                'categories.percentage',
                'judges.name as judge_name',
                'events.name as event_name'
            )
            ->join('contestants', 'scores.contestant_id', '=', 'contestants.id')
            ->join('categories', 'scores.category_id', '=', 'categories.id')
            ->join('judges', 'scores.judge_id', '=', 'judges.id')
            ->join('events', 'scores.event_id', '=', 'events.id')
            ->where('scores.id', $id)
            ->first();

        if (!$score) {
            return response()->json(['error' => 'Score not found'], 404);
        }

        return view('tenant.scores.show', compact('score'));
    }
} 