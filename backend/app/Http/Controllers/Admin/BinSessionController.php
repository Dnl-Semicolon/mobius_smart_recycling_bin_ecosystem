<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BinSession;
use Illuminate\Contracts\View\View;

class BinSessionController extends Controller
{
    public function index(): View
    {
        $sessions = BinSession::query()
            ->with(['bin.outlet.brand', 'user'])
            ->withCount('detectionEvents')
            ->withSum('recyclingTransactions', 'points')
            ->latest('started_at')
            ->paginate(15);

        return view('admin.bin-sessions.index', compact('sessions'));
    }

    public function show(BinSession $binSession): View
    {
        $binSession->load([
            'bin.outlet.brand',
            'user',
            'detectionEvents.detectedBrand',
            'recyclingTransactions',
        ]);

        return view('admin.bin-sessions.show', compact('binSession'));
    }
}
