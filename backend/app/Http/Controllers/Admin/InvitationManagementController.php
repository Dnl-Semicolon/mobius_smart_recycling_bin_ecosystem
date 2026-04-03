<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvitationManagementController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $invitations = Invitation::query()
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->with(['organization', 'invitedBy', 'approvedBy'])
            ->latest()
            ->paginate(15);

        $counts = [
            'pending' => Invitation::where('status', 'pending')->count(),
            'approved' => Invitation::where('status', 'approved')->count(),
            'accepted' => Invitation::where('status', 'accepted')->count(),
            'rejected' => Invitation::where('status', 'rejected')->count(),
        ];

        return view('admin.invitations.index', [
            'invitations' => $invitations,
            'currentStatus' => $status,
            'counts' => $counts,
        ]);
    }

    public function approve(Request $request, Invitation $invitation): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $invitation->update([
            'status' => 'approved',
            'admin_notes' => $request->input('admin_notes', ''),
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.invitations.index')
            ->with('success', "Invitation for \"{$invitation->email}\" has been approved.");
    }

    public function reject(Request $request, Invitation $invitation): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        $invitation->update([
            'status' => 'rejected',
            'admin_notes' => $request->input('admin_notes'),
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.invitations.index')
            ->with('success', "Invitation for \"{$invitation->email}\" has been rejected.");
    }
}
