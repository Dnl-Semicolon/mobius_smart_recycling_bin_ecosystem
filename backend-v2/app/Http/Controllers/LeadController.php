<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Plan;
use App\Models\RegistrationRequest;
use App\Support\PhoneNormalizer;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function create(): Response
    {
        $plans = Plan::where('is_active', true)
            ->orderByRaw('price_monthly = 0, price_monthly')
            ->get(['id', 'name']);

        return Inertia::render('GetStarted', [
            'plans' => $plans,
            'selectedPlanId' => request()->query('plan'),
        ]);
    }

    public function store(StoreLeadRequest $request)
    {
        $validated = $request->validated();

        RegistrationRequest::create([
            ...$validated,
            'contact_phone' => PhoneNormalizer::normalize($validated['contact_phone']),
            'status' => 'pending',
        ]);

        return redirect()->route('get-started.confirmation');
    }

    public function confirmation(): Response
    {
        return Inertia::render('GetStartedConfirmation');
    }
}
