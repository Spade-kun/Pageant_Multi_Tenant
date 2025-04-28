<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Score;
use Illuminate\Http\Request;

class JudgeScoringController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'contestant_id' => 'required|exists:contestants,id',
            'category_id' => 'required|exists:categories,id',
            'event_id' => 'required|exists:events,id',
            'raw_score' => 'required|numeric|min:0|max:100',
            'criteria_scores' => 'required|array',
            'comments' => 'nullable|string'
        ]);

        $category = Category::findOrFail($request->category_id);
        $weightedScore = $request->raw_score * ($category->weight / 100);

        $score = Score::updateOrCreate(
            [
                'contestant_id' => $request->contestant_id,
                'judge_id' => auth()->id(),
                'category_id' => $request->category_id,
                'event_id' => $request->event_id
            ],
            [
                'raw_score' => $request->raw_score,
                'weighted_score' => $weightedScore,
                'criteria_scores' => $request->criteria_scores,
                'comments' => $request->comments
            ]
        );

        return response()->json([
            'message' => 'Score saved successfully',
            'score' => $score
        ]);
    }

    public function getContestantScores($contestantId, $eventId)
    {
        $scores = Score::where('contestant_id', $contestantId)
            ->where('event_id', $eventId)
            ->with(['category', 'judge'])
            ->get();

        $totalScore = $scores->sum('weighted_score');
        $averageScore = $scores->avg('weighted_score');

        return response()->json([
            'scores' => $scores,
            'total_score' => $totalScore,
            'average_score' => $averageScore
        ]);
    }
} 