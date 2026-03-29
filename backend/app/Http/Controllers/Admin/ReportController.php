<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = Report::query()->with(['user', 'bin', 'resolvedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $reports = $query->latest()
            ->paginate(15)
            ->withQueryString();

        $statuses = ReportStatus::cases();
        $types = ReportType::cases();

        return view('admin.reports.index', compact('reports', 'statuses', 'types'));
    }

    public function show(Report $report): View
    {
        $report->load(['user', 'bin', 'resolvedBy']);

        return view('admin.reports.show', compact('report'));
    }

    public function resolve(Request $request, Report $report): RedirectResponse
    {
        abort_if($report->status === ReportStatus::Resolved, 403, 'This report has already been resolved.');

        $request->validate([
            'resolution' => ['required', 'string', 'max:1000'],
        ]);

        $report->update([
            'status' => ReportStatus::Resolved,
            'resolution' => $request->input('resolution'),
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        return redirect()
            ->route('admin.reports.show', $report)
            ->with('success', 'Report resolved successfully.');
    }
}
