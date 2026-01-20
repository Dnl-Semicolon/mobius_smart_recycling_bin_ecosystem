<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Logging\WideEvent;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(private WideEvent $wideEvent) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $this->wideEvent->enrich('business.address.action', 'index_not_implemented');

        return response()->json([
            'data' => [],
            'message' => 'Not implemented.',
        ], 501);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->wideEvent->enrich('business.address.action', 'store_not_implemented');

        return response()->json([
            'data' => null,
            'message' => 'Not implemented.',
        ], 501);
    }

    /**
     * Display the specified resource.
     */
    public function show(Address $address): JsonResponse
    {
        $this->wideEvent->enrichMany([
            'business.address.action' => 'show_not_implemented',
            'business.address.id' => $address->id,
        ]);

        return response()->json([
            'data' => null,
            'message' => 'Not implemented.',
        ], 501);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Address $address): JsonResponse
    {
        $this->wideEvent->enrichMany([
            'business.address.action' => 'update_not_implemented',
            'business.address.id' => $address->id,
        ]);

        return response()->json([
            'data' => null,
            'message' => 'Not implemented.',
        ], 501);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address): JsonResponse
    {
        $this->wideEvent->enrichMany([
            'business.address.action' => 'destroy_not_implemented',
            'business.address.id' => $address->id,
        ]);

        return response()->json([
            'data' => null,
            'message' => 'Not implemented.',
        ], 501);
    }
}
