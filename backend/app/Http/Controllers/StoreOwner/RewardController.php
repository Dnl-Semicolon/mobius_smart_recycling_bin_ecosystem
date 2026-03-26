<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Services\StoreOwnerContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RewardController extends Controller
{
    public function __construct(private StoreOwnerContext $context) {}

    public function index(Request $request): View
    {
        $ctx = $this->context->resolve($request->user());

        $rewards = $ctx->brand->rewards()
            ->withCount('redemptions')
            ->orderBy('sort_order')
            ->get();

        $brand = $ctx->brand;
        $isHQ = $ctx->isHQ;

        return view('store-owner.rewards.index', compact('brand', 'rewards', 'isHQ'));
    }

    public function create(Request $request): View
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403);

        $brand = $ctx->brand;

        return view('store-owner.rewards.create', compact('brand'));
    }

    public function store(Request $request): RedirectResponse
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'points_cost' => ['required', 'integer', 'min:1'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $ctx->brand->rewards()->create([
            ...$validated,
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('store.rewards.index')
            ->with('success', 'Reward created successfully.');
    }

    public function edit(Request $request, Reward $reward): View
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403);
        abort_unless($reward->brand_id === $ctx->brand->id, 403);

        $brand = $ctx->brand;

        return view('store-owner.rewards.edit', compact('brand', 'reward'));
    }

    public function update(Request $request, Reward $reward): RedirectResponse
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403);
        abort_unless($reward->brand_id === $ctx->brand->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'points_cost' => ['required', 'integer', 'min:1'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $reward->update([
            ...$validated,
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('store.rewards.index')
            ->with('success', 'Reward updated successfully.');
    }

    public function destroy(Request $request, Reward $reward): RedirectResponse
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403);
        abort_unless($reward->brand_id === $ctx->brand->id, 403);

        $reward->delete();

        return redirect()->route('store.rewards.index')
            ->with('success', 'Reward deleted.');
    }
}
