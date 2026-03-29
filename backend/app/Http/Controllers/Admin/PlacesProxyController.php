<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PlacesProxyController extends Controller
{
    /**
     * Proxy Google Places Autocomplete (New) requests.
     * Keeps the API key server-side and avoids CORS issues.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2|max:200']);

        $apiKey = config('services.google_maps.api_key');

        if (! $apiKey) {
            return response()->json(['suggestions' => []], 200);
        }

        $response = Http::withHeaders([
            'X-Goog-Api-Key' => $apiKey,
        ])->post('https://places.googleapis.com/v1/places:autocomplete', [
            'input' => $request->query('q'),
            'includedRegionCodes' => ['my'],
            'languageCode' => 'en',
        ]);

        return response()->json($response->json());
    }

    /**
     * Proxy Google Place Details (New) for coordinates + formatted address.
     */
    public function details(Request $request): JsonResponse
    {
        $request->validate(['place_id' => 'required|string|max:300']);

        $apiKey = config('services.google_maps.api_key');

        if (! $apiKey) {
            return response()->json(['error' => 'No API key configured'], 500);
        }

        $placeId = $request->query('place_id');

        $response = Http::withHeaders([
            'X-Goog-Api-Key' => $apiKey,
            'X-Goog-FieldMask' => 'displayName,formattedAddress,location',
        ])->get("https://places.googleapis.com/v1/places/{$placeId}");

        return response()->json($response->json());
    }
}
