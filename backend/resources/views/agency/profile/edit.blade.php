<x-layouts.agency title="Settings">
    <x-slot:header>Settings</x-slot:header>

    @include('partials.profile-form', [
        'user' => auth()->user(),
        'action' => route('agency.profile.update'),
        'passwordAction' => route('agency.profile.password'),
    ])
</x-layouts.agency>
