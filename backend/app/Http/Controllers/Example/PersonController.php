<?php

namespace App\Http\Controllers\Example;

use App\Http\Controllers\Controller;
use App\Http\Requests\Example\StorePersonRequest;
use App\Http\Requests\Example\UpdatePersonRequest;
use App\Logging\WideEvent;
use App\Models\Example\Person;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PersonController extends Controller
{
    public function __construct(private WideEvent $wideEvent) {}

    public function index(): View
    {
        $people = Person::with('addresses')->latest()->get();

        $this->wideEvent->enrich('business.person.list_count', $people->count());

        return view('pages.examples.persons.index', compact('people'));
    }

    public function create(): View
    {
        $states = $this->malaysianStates();

        $this->wideEvent->enrichMany([
            'business.person.form' => 'create',
            'business.person.state_count' => count($states),
        ]);

        return view('pages.examples.persons.create', compact('states'));
    }

    public function store(StorePersonRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $person = Person::create([
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

        return redirect()->route('persons.show', $person);
    }

    public function show(Person $person): View
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

        return view('pages.examples.persons.show', compact('person'));
    }

    public function edit(Person $person): View
    {
        $person->load('addresses');
        $states = $this->malaysianStates();

        $this->wideEvent->enrichMany([
            'business.person.form' => 'edit',
            'business.person.id' => $person->id,
        ]);

        return view('pages.examples.persons.edit', compact('person', 'states'));
    }

    public function update(UpdatePersonRequest $request, Person $person): RedirectResponse
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

        return redirect()->route('persons.show', $person);
    }

    public function destroy(Person $person): RedirectResponse
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

        return redirect()->route('persons.index');
    }

    /**
     * @return array<int, string>
     */
    private function malaysianStates(): array
    {
        return [
            'Johor',
            'Kedah',
            'Kelantan',
            'Melaka',
            'N. Sembilan',
            'Pahang',
            'Penang',
            'Perak',
            'Perlis',
            'Sabah',
            'Sarawak',
            'Selangor',
            'Terengganu',
            'KL',
            'Labuan',
            'Putrajaya',
        ];
    }
}
