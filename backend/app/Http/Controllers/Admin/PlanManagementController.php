<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanManagementController extends Controller
{
    public function index(): View
    {
        $plans = Plan::query()
            ->withCount('subscriptions')
            ->latest()
            ->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('admin.plans.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'features' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['features'] = $this->parseFeatures($validated['features'] ?? '');

        Plan::create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan \"{$validated['name']}\" has been created.");
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'features' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['features'] = $this->parseFeatures($validated['features'] ?? '');

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan \"{$plan->name}\" has been updated.");
    }

    /**
     * Parse newline-separated features into an array.
     *
     * @return array<int, string>
     */
    private function parseFeatures(string $raw): array
    {
        return collect(explode("\n", $raw))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
