<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Organization;
use App\Models\RegistrationRequest;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function index(): Response
    {
        $leads = RegistrationRequest::query()
            ->with('selectedPlan:id,name')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->through(fn (RegistrationRequest $lead) => [
                'id' => $lead->id,
                'company_name' => $lead->company_name,
                'contact_name' => $lead->contact_name,
                'contact_email' => $lead->contact_email,
                'contact_phone' => $lead->contact_phone,
                'type' => $lead->type,
                'selected_plan' => $lead->selectedPlan?->name,
                'status' => $lead->status,
                'created_at' => $lead->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Admin/Leads/Index', [
            'leads' => $leads,
        ]);
    }

    public function show(RegistrationRequest $lead): Response
    {
        $lead->load('selectedPlan:id,name,price_monthly', 'reviewer:id,name');

        return Inertia::render('Admin/Leads/Show', [
            'lead' => [
                'id' => $lead->id,
                'company_name' => $lead->company_name,
                'contact_name' => $lead->contact_name,
                'contact_email' => $lead->contact_email,
                'contact_phone' => $lead->contact_phone,
                'type' => $lead->type,
                'description' => $lead->description,
                'selected_plan' => $lead->selectedPlan,
                'status' => $lead->status,
                'admin_notes' => $lead->admin_notes,
                'reviewed_by' => $lead->reviewer?->name,
                'reviewed_at' => $lead->reviewed_at?->format('Y-m-d H:i'),
                'created_at' => $lead->created_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    public function convert(RegistrationRequest $lead): Response
    {
        if ($lead->status !== 'pending') {
            return back();
        }

        $password = Str::random(12);

        $result = DB::transaction(function () use ($lead, $password) {
            $org = Organization::create([
                'name' => $lead->company_name,
                'type' => $lead->type,
                'is_active' => true,
            ]);

            $brand = Brand::create([
                'organization_id' => $org->id,
                'name' => $lead->company_name,
                'is_active' => true,
            ]);

            $user = User::create([
                'organization_id' => $org->id,
                'name' => $lead->contact_name,
                'email' => $lead->contact_email,
                'phone' => $lead->contact_phone,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'roles' => ['brand_owner'],
            ]);

            $subscription = null;
            if ($lead->selected_plan_id) {
                $subscription = Subscription::create([
                    'organization_id' => $org->id,
                    'plan_id' => $lead->selected_plan_id,
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => now()->addYear(),
                    'renews_at' => now()->addYear()->subMonth(),
                ]);
            }

            $lead->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            return compact('org', 'brand', 'user', 'subscription');
        });

        return Inertia::render('Admin/Leads/Converted', [
            'lead' => $lead->company_name,
            'organization' => $result['org']->name,
            'brand' => $result['brand']->name,
            'user_email' => $result['user']->email,
            'user_password' => $password,
            'plan' => $result['subscription'] ? $lead->selectedPlan->name : null,
        ]);
    }

    public function reject(RegistrationRequest $lead): RedirectResponse
    {
        $lead->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.leads.index');
    }
}
