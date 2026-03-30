<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveAgencyRequest;
use App\Http\Requests\ApproveBrandApplicationRequest;
use App\Http\Requests\RejectApplicationRequest;
use App\Models\BrandApplication;
use App\Models\CollectorAgency;
use App\Services\ApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(private ApplicationService $service) {}

    public function brandApplications(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $applications = BrandApplication::query()
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($status === 'all', fn ($q) => $q->whereIn('status', ['pending', 'approved', 'rejected']))
            ->with(['brand', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.applications.brands.index', [
            'applications' => $applications,
            'currentStatus' => $status,
            'counts' => [
                'pending' => BrandApplication::pending()->count(),
                'approved' => BrandApplication::approved()->count(),
                'rejected' => BrandApplication::rejected()->count(),
            ],
        ]);
    }

    public function showBrandApplication(BrandApplication $brandApplication): View
    {
        $brandApplication->load(['brand', 'user', 'reviewer']);

        return view('admin.applications.brands.show', ['application' => $brandApplication]);
    }

    public function approveBrand(ApproveBrandApplicationRequest $request, BrandApplication $brandApplication): RedirectResponse
    {
        $this->service->approveBrandApplication($brandApplication, $request->user(), [
            'points_multiplier' => $request->validated('points_multiplier'),
            'rewards_budget' => $request->validated('rewards_budget'),
        ]);

        return redirect()->route('admin.applications.brands.index')
            ->with('success', "Application for \"{$brandApplication->brand_name}\" has been approved.");
    }

    public function rejectBrand(RejectApplicationRequest $request, BrandApplication $brandApplication): RedirectResponse
    {
        $this->service->rejectBrandApplication($brandApplication, $request->user(), $request->validated('rejection_reason'));

        return redirect()->route('admin.applications.brands.index')
            ->with('success', "Application for \"{$brandApplication->brand_name}\" has been rejected.");
    }

    public function agencyApplications(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $agencies = CollectorAgency::query()
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($status === 'all', fn ($q) => $q->whereIn('status', ['pending', 'approved', 'rejected']))
            ->with('adminUser')
            ->latest()
            ->paginate(20);

        return view('admin.applications.agencies.index', [
            'agencies' => $agencies,
            'currentStatus' => $status,
            'counts' => [
                'pending' => CollectorAgency::pending()->count(),
                'approved' => CollectorAgency::approved()->count(),
                'rejected' => CollectorAgency::rejected()->count(),
            ],
        ]);
    }

    public function showAgencyApplication(CollectorAgency $collectorAgency): View
    {
        $collectorAgency->load('adminUser', 'reviewer');

        return view('admin.applications.agencies.show', ['agency' => $collectorAgency]);
    }

    public function approveAgency(ApproveAgencyRequest $request, CollectorAgency $collectorAgency): RedirectResponse
    {
        $this->service->approveAgency($collectorAgency, $request->user());

        return redirect()->route('admin.applications.agencies.index')
            ->with('success', "Agency \"{$collectorAgency->name}\" has been approved.");
    }

    public function rejectAgency(RejectApplicationRequest $request, CollectorAgency $collectorAgency): RedirectResponse
    {
        $this->service->rejectAgency($collectorAgency, $request->user(), $request->validated('rejection_reason'));

        return redirect()->route('admin.applications.agencies.index')
            ->with('success', "Agency \"{$collectorAgency->name}\" has been rejected.");
    }
}
