<?php

namespace App\Http\Controllers\Api\Example;

use App\Http\Controllers\Controller;
use App\Http\Requests\Example\StorePersonRequest;
use App\Http\Requests\Example\UpdatePersonRequest;
use App\Http\Resources\Example\PersonResource;
use App\Logging\WideEvent;
use App\Models\Example\Person;
use Illuminate\Http\JsonResponse;

class PersonController extends Controller
{
    public function __construct(private WideEvent $wideEvent) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $people = Person::query()->latest()->get();

        $this->wideEvent->enrich('business.person.list_count', $people->count());

        return PersonResource::collection($people)
            ->additional(['message' => 'Persons retrieved successfully.'])
            ->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $person = Person::query()->create([
            'name' => $validated['name'],
            'birthday' => $validated['birthday'],
            'phone' => $validated['phone'],
        ]);

        $address = $person->addresses()->create([
            'line_1' => $validated['line_1'],
            'line_2' => $validated['line_2'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postal_code' => $validated['postal_code'],
        ]);

        $this->wideEvent->enrichMany([
            'business.person.action' => 'created',
            'business.person.id' => $person->id,
            'business.person.name' => $person->name,
            'business.person.birthday' => $person->birthday?->toDateString(),
            'business.person.phone' => $person->phone,
            'business.address.action' => 'created',
            'business.address.id' => $address->id,
            'business.address.line_1' => $address->line_1,
            'business.address.line_2' => $address->line_2,
            'business.address.city' => $address->city,
            'business.address.state' => $address->state,
            'business.address.postal_code' => $address->postal_code,
        ]);

        return PersonResource::make($person->load('addresses'))
            ->additional(['message' => 'Person created successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Person $person): JsonResponse
    {
        $person->load('addresses');
        $address = $person->addresses->first();

        $this->wideEvent->enrichMany([
            'business.person.action' => 'viewed',
            'business.person.id' => $person->id,
            'business.person.name' => $person->name,
            'business.person.birthday' => $person->birthday?->toDateString(),
            'business.person.phone' => $person->phone,
            'business.person.address_count' => $person->addresses->count(),
            'business.address.id' => $address?->id,
            'business.address.city' => $address?->city,
            'business.address.state' => $address?->state,
        ]);

        return PersonResource::make($person)
            ->additional(['message' => 'Person retrieved successfully.'])
            ->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePersonRequest $request, Person $person): JsonResponse
    {
        $validated = $request->validated();

        $person->update([
            'name' => $validated['name'],
            'birthday' => $validated['birthday'],
            'phone' => $validated['phone'],
        ]);

        $addressData = [
            'line_1' => $validated['line_1'],
            'line_2' => $validated['line_2'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postal_code' => $validated['postal_code'],
        ];

        $address = $person->addresses()->first();
        $addressAction = $address ? 'updated' : 'created';

        if ($address) {
            $address->update($addressData);
        } else {
            $address = $person->addresses()->create($addressData);
        }

        $this->wideEvent->enrichMany([
            'business.person.action' => 'updated',
            'business.person.id' => $person->id,
            'business.person.name' => $person->name,
            'business.person.birthday' => $person->birthday?->toDateString(),
            'business.person.phone' => $person->phone,
            'business.address.action' => $addressAction,
            'business.address.id' => $address->id,
            'business.address.line_1' => $address->line_1,
            'business.address.line_2' => $address->line_2,
            'business.address.city' => $address->city,
            'business.address.state' => $address->state,
            'business.address.postal_code' => $address->postal_code,
        ]);

        return PersonResource::make($person->load('addresses'))
            ->additional(['message' => 'Person updated successfully.'])
            ->response();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Person $person): JsonResponse
    {
        $addressCount = $person->addresses()->count();

        $this->wideEvent->enrichMany([
            'business.person.action' => 'deleted',
            'business.person.id' => $person->id,
            'business.person.name' => $person->name,
            'business.person.birthday' => $person->birthday?->toDateString(),
            'business.person.phone' => $person->phone,
            'business.person.address_count' => $addressCount,
        ]);

        $person->delete();

        return response()->json([
            'data' => null,
            'message' => 'Person deleted successfully.',
        ]);
    }
}
