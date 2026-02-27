<x-layouts.admin title="My Profile">
    <x-slot:header>
        My Profile
    </x-slot:header>

    @include('partials.profile-form', [
        'user' => $user,
        'updateRoute' => route('admin.profile.update'),
        'passwordRoute' => route('admin.profile.password'),
    ])
</x-layouts.admin>
