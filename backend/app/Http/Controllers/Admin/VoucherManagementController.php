<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VoucherAllocation;
use App\Models\VoucherClaim;
use App\Models\VoucherTemplate;
use Illuminate\Contracts\View\View;

class VoucherManagementController extends Controller
{
    public function index(): View
    {
        $templates = VoucherTemplate::query()
            ->with('brand')
            ->withCount(['allocations', 'claims'])
            ->latest()
            ->paginate(15, ['*'], 'templates_page');

        $allocations = VoucherAllocation::query()
            ->with(['voucherTemplate.brand', 'outlet'])
            ->latest()
            ->paginate(15, ['*'], 'allocations_page');

        $claims = VoucherClaim::query()
            ->with(['voucherTemplate.brand', 'voucherAllocation.outlet', 'user'])
            ->latest()
            ->paginate(15, ['*'], 'claims_page');

        return view('admin.vouchers.index', compact('templates', 'allocations', 'claims'));
    }
}
