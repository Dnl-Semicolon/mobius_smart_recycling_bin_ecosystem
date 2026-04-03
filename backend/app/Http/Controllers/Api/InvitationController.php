<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $invitations = Invitation::query()
            ->with(['organization', 'invitedBy', 'approvedBy'])
            ->when($request->user()->isBrandOwner(), function ($query) use ($request) {
                $query->where('organization_id', $request->user()->organization_id);
            })
            ->latest()
            ->paginate(20);

        return response()->json($invitations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:store_owner,collector'],
        ]);

        $validated['invited_by'] = $request->user()->id;
        $validated['status'] = 'pending';
        $validated['admin_notes'] = '';

        $invitation = Invitation::create($validated);
        $invitation->load(['organization', 'invitedBy']);

        return response()->json(['data' => $invitation, 'message' => 'Invitation created successfully.'], 201);
    }

    public function approve(Invitation $invitation): JsonResponse
    {
        $invitation->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $invitation->load(['organization', 'invitedBy', 'approvedBy']);

        return response()->json(['data' => $invitation, 'message' => 'Invitation approved.']);
    }

    public function reject(Request $request, Invitation $invitation): JsonResponse
    {
        $invitation->update([
            'status' => 'rejected',
            'admin_notes' => $request->input('admin_notes', $invitation->admin_notes),
        ]);

        $invitation->load(['organization', 'invitedBy']);

        return response()->json(['data' => $invitation, 'message' => 'Invitation rejected.']);
    }

    public function accept(Invitation $invitation): JsonResponse
    {
        $invitation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $invitation->load(['organization', 'invitedBy']);

        return response()->json(['data' => $invitation, 'message' => 'Invitation accepted.']);
    }
}
