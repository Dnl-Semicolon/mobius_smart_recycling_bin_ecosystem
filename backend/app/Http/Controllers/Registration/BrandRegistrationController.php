<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandApplicationRequest;
use App\Models\Brand;
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandRegistrationController extends Controller
{
    public function create(): View
    {
        return view('registration.brand');
    }

    public function store(StoreBrandApplicationRequest $request, ApplicationService $service): RedirectResponse
    {
        $data = $request->validated();

        if ($data['flow'] === 'claim') {
            $service->registerBrandClaim($data);
        } else {
            if ($request->hasFile('logo')) {
                $data['logo_path'] = $request->file('logo')->store('brands', 'public');
            }
            $service->registerNewBrand($data);
        }

        return redirect()->route('registration.success')
            ->with('success', 'Your brand application has been submitted.');
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        $brands = Brand::query()
            ->where('is_active', true)
            ->whereDoesntHave('applications', fn ($q) => $q->pending())
            ->when($query, fn ($q) => $q->where('name', 'like', "%{$query}%"))
            ->select('id', 'name', 'slug', 'logo_path', 'website', 'description')
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json($brands);
    }
}
