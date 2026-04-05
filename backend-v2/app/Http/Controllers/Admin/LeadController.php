<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationRequest;
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
}
