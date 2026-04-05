<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Models\VoucherTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VoucherController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $brand = $user->organization?->brands()->first();

        $vouchers = $brand
            ? VoucherTemplate::where('brand_id', $brand->id)
                ->withCount('claims')
                ->orderByDesc('created_at')
                ->paginate(15)
                ->through(fn (VoucherTemplate $v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'type' => $v->type,
                    'value' => $v->value,
                    'points_required' => $v->points_required,
                    'valid_from' => $v->valid_from->format('Y-m-d'),
                    'valid_until' => $v->valid_until->format('Y-m-d'),
                    'is_active' => $v->is_active,
                    'claims_count' => $v->claims_count,
                ])
            : ['data' => [], 'total' => 0, 'links' => [], 'last_page' => 1];

        return Inertia::render('Brand/Vouchers/Index', [
            'vouchers' => $vouchers,
            'brandName' => $brand?->name,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Brand/Vouchers/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $brand = $user->organization?->brands()->first();

        if (! $brand) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:discount,free_item,cashback'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'points_required' => ['required', 'integer', 'min:1'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:valid_from'],
            'quota' => ['nullable', 'integer', 'min:1'],
        ]);

        VoucherTemplate::create([
            ...$validated,
            'brand_id' => $brand->id,
            'is_active' => true,
        ]);

        return redirect()->route('brand.vouchers.index');
    }
}
