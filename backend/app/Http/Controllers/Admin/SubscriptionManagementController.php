<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Contracts\View\View;

class SubscriptionManagementController extends Controller
{
    public function index(): View
    {
        $subscriptions = Subscription::query()
            ->with(['organization', 'plan'])
            ->latest()
            ->paginate(15);

        return view('admin.subscriptions.index', compact('subscriptions'));
    }
}
