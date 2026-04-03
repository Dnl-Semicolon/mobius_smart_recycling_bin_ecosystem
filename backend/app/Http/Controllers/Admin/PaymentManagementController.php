<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Contracts\View\View;

class PaymentManagementController extends Controller
{
    public function index(): View
    {
        $payments = Payment::query()
            ->with(['organization', 'subscription.plan'])
            ->latest('paid_at')
            ->paginate(15);

        return view('admin.payments.index', compact('payments'));
    }
}
