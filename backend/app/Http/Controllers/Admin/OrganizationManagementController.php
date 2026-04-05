<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Organization;
use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
            ->whereNotNull('email_verified_at')
            ->with('reviewer')
            ->latest()
            ->paginate(15, ['*'], 'req_page');

        $pendingCount = RegistrationRequest::where('status', 'pending')
            ->whereNotNull('email_verified_at')
            ->count();

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

    public function showRequest(RegistrationRequest $registrationRequest): View
    {
        $registrationRequest->load('reviewer');

        return view('admin.registration-requests.show', [
            'registrationRequest' => $registrationRequest,
        ]);
    }

    public function approveRequest(Request $request, RegistrationRequest $registrationRequest): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Create the Organization
        $org = Organization::create([
            'name' => $registrationRequest->company_name,
            'type' => $registrationRequest->type,
            'description' => $registrationRequest->description ?? '',
            'logo_path' => '',
            'website' => '',
            'is_active' => true,
        ]);

        // Create a Brand under this org (company name as brand name)
        Brand::create([
            'organization_id' => $org->id,
            'name' => Str::before($registrationRequest->company_name, ' Sdn'),
            'slug' => Str::slug(Str::before($registrationRequest->company_name, ' Sdn')),
            'logo_path' => '',
            'description' => $registrationRequest->description ?? '',
            'website' => '',
            'point_multiplier' => 1.00,
            'is_active' => true,
        ]);

        // Create Brand Owner user account
        $tempPassword = Str::random(12);
        User::create([
            'organization_id' => $org->id,
            'name' => $registrationRequest->contact_name,
            'email' => $registrationRequest->contact_email,
            'password' => Hash::make($tempPassword),
            'phone' => $registrationRequest->contact_phone,
            'phone_verified_at' => $registrationRequest->phone_verified_at,
            'email_verified_at' => $registrationRequest->email_verified_at,
            'profile_photo_path' => '',
            'roles' => json_encode(['brand_owner']),
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
        ]);

        $registrationRequest->update([
            'status' => 'approved',
            'admin_notes' => ($request->input('admin_notes', '') ?: '')."\nOrg #{$org->id} created. Temp password: {$tempPassword}",
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.organizations.index', ['tab' => 'organizations'])
            ->with('success', "Approved! Organization \"{$org->name}\" created with brand owner account for {$registrationRequest->contact_email}.");
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
