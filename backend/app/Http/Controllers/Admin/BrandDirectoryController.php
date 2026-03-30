<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandDirectoryController extends Controller
{
    public function index(): View
    {
        $brands = Brand::query()
            ->withCount('outlets')
            ->with('adminUser')
            ->orderBy('name')
            ->paginate(30);

        return view('admin.brand-directory.index', compact('brands'));
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
