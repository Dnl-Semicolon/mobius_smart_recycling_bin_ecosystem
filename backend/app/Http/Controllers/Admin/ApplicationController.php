<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveAgencyRequest;
use App\Http\Requests\ApproveBrandRequest;
use App\Http\Requests\RejectApplicationRequest;
use App\Models\Brand;
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

        $brands = Brand::query()
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($status === 'all', fn ($q) => $q->whereIn('status', ['pending', 'approved', 'rejected']))
            ->with('adminUser')
            ->latest()
            ->paginate(20);

        return view('admin.applications.brands.index', [
            'brands' => $brands,
            'currentStatus' => $status,
            'counts' => [
                'pending' => Brand::pending()->count(),
                'approved' => Brand::approved()->count(),
                'rejected' => Brand::rejected()->count(),
            ],
        ]);
    }

    public function showBrandApplication(Brand $brand): View
    {
        $brand->load('adminUser', 'reviewer');

        return view('admin.applications.brands.show', compact('brand'));
    }

    public function approveBrand(ApproveBrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->service->approveBrand($brand, $request->user(), [
            'points_multiplier' => $request->validated('points_multiplier'),
            'rewards_budget' => $request->validated('rewards_budget'),
        ]);

        return redirect()->route('admin.applications.brands.index')
            ->with('success', "Brand \"{$brand->name}\" has been approved.");
    }

    public function rejectBrand(RejectApplicationRequest $request, Brand $brand): RedirectResponse
    {
        $this->service->rejectBrand($brand, $request->user(), $request->validated('rejection_reason'));

        return redirect()->route('admin.applications.brands.index')
            ->with('success', "Brand \"{$brand->name}\" has been rejected.");
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
