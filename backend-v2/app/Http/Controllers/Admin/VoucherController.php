<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VoucherTemplate;
use Inertia\Inertia;
use Inertia\Response;

class VoucherController extends Controller
{
    public function index(): Response
    {
        $vouchers = VoucherTemplate::query()
            ->with('brand:id,name')
            ->withCount('claims')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->through(fn (VoucherTemplate $v) => [
                'id' => $v->id,
                'name' => $v->name,
                'brand' => $v->brand->name,
                'type' => $v->type,
                'value' => $v->value,
                'points_required' => $v->points_required,
                'quota' => $v->quota,
                'claimed_count' => $v->claimed_count,
                'claims_count' => $v->claims_count,
                'valid_from' => $v->valid_from->format('Y-m-d'),
                'valid_until' => $v->valid_until->format('Y-m-d'),
                'is_active' => $v->is_active,
            ]);

        return Inertia::render('Admin/Vouchers/Index', [
            'vouchers' => $vouchers,
        ]);
    }
}
