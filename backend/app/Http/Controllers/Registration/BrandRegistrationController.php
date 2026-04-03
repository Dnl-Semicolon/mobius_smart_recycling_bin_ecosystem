<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Models\RegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandRegistrationController extends Controller
{
    public function create(): View
    {
        return view('registration.brand');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'type' => ['required', 'in:beverage_company,recycling_company,government'],
            'description' => ['required', 'string'],
        ]);

        $validated['status'] = 'pending';
        $validated['admin_notes'] = '';

        RegistrationRequest::create($validated);

        return redirect()->route('registration.success')
            ->with('success', 'Your application has been submitted successfully.');
    }
}
