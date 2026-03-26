<?php

use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Brand;
use App\Models\Outlet;
use App\Models\User;
use App\Services\StoreOwnerContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves HQ user context with all brand outlets', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $outlet2 = Outlet::factory()->create(['brand_id' => $brand->id]);

    $context = app(StoreOwnerContext::class)->resolve($user);

    expect($context->brand->id)->toBe($brand->id)
        ->and($context->isHQ)->toBeTrue()
        ->and($context->outlets)->toHaveCount(2)
        ->and($context->outlets->pluck('id')->toArray())
        ->toContain($outlet1->id, $outlet2->id);
});

it('resolves branch manager context with only assigned outlets', function () {
    $hqUser = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $hqUser->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $outlet2 = Outlet::factory()->create(['brand_id' => $brand->id]);

    $branchUser = User::factory()->storeOwner()->create();
    $outlet1->managers()->attach($branchUser->id, ['role' => 'manager']);

    $context = app(StoreOwnerContext::class)->resolve($branchUser);

    expect($context->brand->id)->toBe($brand->id)
        ->and($context->isHQ)->toBeFalse()
        ->and($context->outlets)->toHaveCount(1)
        ->and($context->outlets->first()->id)->toBe($outlet1->id);
});

it('collects bin IDs across all scoped outlets', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $outlet2 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $bin1 = Bin::factory()->create();
    $bin2 = Bin::factory()->create();
    BinAssignment::create(['bin_id' => $bin1->id, 'outlet_id' => $outlet1->id, 'assigned_at' => now()]);
    BinAssignment::create(['bin_id' => $bin2->id, 'outlet_id' => $outlet2->id, 'assigned_at' => now()]);

    $context = app(StoreOwnerContext::class)->resolve($user);

    expect($context->binIds)->toContain($bin1->id, $bin2->id)
        ->and($context->binIds)->toHaveCount(2);
});

it('aborts 403 for user with no brand association', function () {
    $user = User::factory()->storeOwner()->create();

    app(StoreOwnerContext::class)->resolve($user);
})->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);

it('filters outlets when outlet ID is provided', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $outlet2 = Outlet::factory()->create(['brand_id' => $brand->id]);

    $context = app(StoreOwnerContext::class)->resolve($user, $outlet1->id);

    expect($context->outlets)->toHaveCount(1)
        ->and($context->outlets->first()->id)->toBe($outlet1->id)
        ->and($context->selectedOutlet->id)->toBe($outlet1->id);
});

it('rejects outlet filter for outlet not in scope', function () {
    $hqUser = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $hqUser->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);

    $branchUser = User::factory()->storeOwner()->create();
    $outlet1->managers()->attach($branchUser->id, ['role' => 'manager']);

    $otherOutlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    app(StoreOwnerContext::class)->resolve($branchUser, $otherOutlet->id);
})->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);
