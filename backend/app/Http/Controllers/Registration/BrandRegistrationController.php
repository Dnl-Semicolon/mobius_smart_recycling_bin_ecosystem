<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandRegistrationRequest;
use App\Services\ApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BrandRegistrationController extends Controller
{
    public function create(): View
    {
        return view('registration.brand');
    }

    public function store(StoreBrandRegistrationRequest $request, ApplicationService $service): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('brands', 'public');
        }

        $service->registerBrand($data);

        return redirect()->route('registration.success')
            ->with('success', 'Your brand application has been submitted.');
    }
}
