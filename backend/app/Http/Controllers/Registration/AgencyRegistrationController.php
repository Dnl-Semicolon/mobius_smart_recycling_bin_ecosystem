<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgencyRegistrationRequest;
use App\Services\ApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AgencyRegistrationController extends Controller
{
    public function create(): View
    {
        return view('registration.agency');
    }

    public function store(StoreAgencyRegistrationRequest $request, ApplicationService $service): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('agencies', 'public');
        }

        $service->registerAgency($data);

        return redirect()->route('registration.success')
            ->with('success', 'Your agency application has been submitted.');
    }
}
