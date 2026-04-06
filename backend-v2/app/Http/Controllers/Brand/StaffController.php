<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\MalaysianMobilePhone;
use App\Rules\UniqueNormalizedEmail;
use App\Rules\UniqueNormalizedPhone;
use App\Support\EmailNormalizer;
use App\Support\PhoneNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    private const LEAD_CONFLICT_MESSAGE = 'Please contact admin to continue with this existing lead.';

    public function index(): Response
    {
        $user = auth()->user();

        $staff = User::where('organization_id', $user->organization_id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'roles' => $u->getRolesArray(),
                'created_at' => $u->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Brand/Staff/Index', [
            'staff' => $staff,
        ]);
    }

    public function create(): Response
    {
        $org = auth()->user()->organization;

        return Inertia::render('Brand/Staff/Create', [
            'limitInfo' => $org?->getLimitInfo('staff_limit'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->merge([
            'email' => EmailNormalizer::normalize($request->input('email')),
        ]);

        $org = $user->organization;
        if ($org && $org->hasReachedLimit('staff_limit')) {
            return back()->withErrors(['limit' => 'Staff limit reached for your plan. Upgrade to add more staff.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'bail',
                'required',
                'email',
                'max:255',
                'unique:users,email',
                new UniqueNormalizedEmail(
                    'registration_requests',
                    column: 'contact_email',
                    scope: fn ($query) => $query->where('status', '!=', 'rejected'),
                    message: self::LEAD_CONFLICT_MESSAGE,
                ),
            ],
            'phone' => [
                'nullable',
                'bail',
                'string',
                'max:20',
                new MalaysianMobilePhone,
                new UniqueNormalizedPhone('users'),
                new UniqueNormalizedPhone(
                    'registration_requests',
                    column: 'contact_phone',
                    scope: fn ($query) => $query->where('status', '!=', 'rejected'),
                    message: self::LEAD_CONFLICT_MESSAGE,
                ),
            ],
        ]);

        $password = Str::random(12);

        User::create([
            'organization_id' => $user->organization_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => PhoneNormalizer::normalize($validated['phone'] ?? null),
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'roles' => ['store_owner'],
        ]);

        return redirect()->route('brand.staff.index')->with('generated_password', $password);
    }
}
