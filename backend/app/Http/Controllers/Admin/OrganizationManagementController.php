<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\RegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationManagementController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'organizations');

        $organizations = Organization::query()
            ->withCount(['brands', 'users'])
            ->with('subscription.plan')
            ->latest()
            ->paginate(15, ['*'], 'org_page');

        $requests = RegistrationRequest::query()
            ->with('reviewer')
            ->latest()
            ->paginate(15, ['*'], 'req_page');

        $pendingCount = RegistrationRequest::where('status', 'pending')->count();

        return view('admin.organizations.index', [
            'organizations' => $organizations,
            'requests' => $requests,
            'currentTab' => $tab,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function show(Organization $organization): View
    {
        $organization->load([
            'brands.outlets',
            'users',
            'subscription.plan',
        ]);

        return view('admin.organizations.show', compact('organization'));
    }

    public function approveRequest(Request $request, RegistrationRequest $registrationRequest): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $registrationRequest->update([
            'status' => 'approved',
            'admin_notes' => $request->input('admin_notes', ''),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.organizations.index', ['tab' => 'requests'])
            ->with('success', "Registration request from \"{$registrationRequest->company_name}\" has been approved.");
    }

    public function rejectRequest(Request $request, RegistrationRequest $registrationRequest): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        $registrationRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->input('admin_notes'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.organizations.index', ['tab' => 'requests'])
            ->with('success', "Registration request from \"{$registrationRequest->company_name}\" has been rejected.");
    }
}
