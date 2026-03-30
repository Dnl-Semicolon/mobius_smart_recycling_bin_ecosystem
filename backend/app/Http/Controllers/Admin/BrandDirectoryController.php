<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = in_array($request->input('per_page'), [18, 30, 60]) ? (int) $request->input('per_page') : 18;
        $status = $request->input('status');

        $brands = Brand::query()
            ->withCount('outlets')
            ->with('adminUser')
            ->when($request->input('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($status === 'available', fn ($q) => $q->whereNull('user_id'))
            ->when($status === 'claimed', fn ($q) => $q->whereNotNull('user_id'))
            ->orderBy('name')
            ->paginate($perPage);

        $statusCounts = [
            'all' => Brand::count(),
            'available' => Brand::whereNull('user_id')->count(),
            'claimed' => Brand::whereNotNull('user_id')->count(),
        ];

        return view('admin.brand-directory.index', compact('brands', 'statusCounts'));
    }

    public function create(): View
    {
        return view('admin.brand-directory.edit', ['brand' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('brands', 'public');
        }
        unset($data['logo']);

        $data['status'] = 'approved';
        $data['active'] = true;

        Brand::create($data);

        return redirect()->route('admin.brand-directory.index')
            ->with('success', "Brand \"{$data['name']}\" added to directory.");
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brand-directory.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('brands', 'public');
        }
        unset($data['logo']);

        $brand->update($data);

        return redirect()->route('admin.brand-directory.index')
            ->with('success', "Brand \"{$brand->name}\" updated.");
    }
}
