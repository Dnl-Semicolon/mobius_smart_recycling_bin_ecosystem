<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = AppNotification::query()->with('user');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $notifications = $query->latest()
            ->paginate(20)
            ->withQueryString();

        $types = NotificationType::cases();

        return view('admin.notifications.index', compact('notifications', 'types'));
    }
}
