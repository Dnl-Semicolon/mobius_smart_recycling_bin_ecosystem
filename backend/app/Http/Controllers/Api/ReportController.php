<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reports = $request->user()
            ->reports()
            ->with('bin')
            ->latest()
            ->paginate(15);

        return response()->json($reports);
    }

    public function store(StoreReportRequest $request): JsonResponse
    {
        $report = Report::create([
            'user_id' => $request->user()->id,
            'bin_id' => $request->validated('bin_id'),
            'type' => $request->validated('type'),
            'description' => $request->validated('description'),
            'status' => 'open',
        ]);

        return response()->json([
            'data' => [
                'id' => $report->id,
                'bin_id' => $report->bin_id,
                'type' => $report->type->value,
                'description' => $report->description,
                'status' => $report->status->value,
                'created_at' => $report->created_at->toIso8601String(),
            ],
            'message' => 'Report submitted successfully.',
        ], 201);
    }
}
