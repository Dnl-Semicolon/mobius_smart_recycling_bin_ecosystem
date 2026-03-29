<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Outlet;
use App\Models\Reward;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        // --- Malaysian F&B brands that produce recyclable cup waste ---

        $starbucks = Brand::create([
            'name' => 'Starbucks',
            'slug' => 'starbucks',
            'primary_color' => '#00704A',
            'description' => 'Global coffeehouse chain committed to ethical sourcing and environmental stewardship.',
            'points_multiplier' => 1.50,
            'rewards_budget' => 50000,
            'active' => true,
        ]);

        $tigerSugar = Brand::create([
            'name' => 'Tiger Sugar',
            'slug' => 'tiger-sugar',
            'primary_color' => '#F5A623',
            'description' => 'Taiwanese brown sugar boba tea chain.',
            'points_multiplier' => 1.30,
            'rewards_budget' => 20000,
            'active' => true,
        ]);

        $tealive = Brand::create([
            'name' => 'Tealive',
            'slug' => 'tealive',
            'primary_color' => '#E91E63',
            'description' => 'Malaysia\'s largest lifestyle tea brand with 800+ outlets.',
            'points_multiplier' => 1.50,
            'rewards_budget' => 40000,
            'active' => true,
        ]);

        $zus = Brand::create([
            'name' => 'ZUS Coffee',
            'slug' => 'zus-coffee',
            'primary_color' => '#1A1A1A',
            'description' => 'Specialty coffee made accessible. 500+ outlets across Malaysia.',
            'points_multiplier' => 1.40,
            'rewards_budget' => 30000,
            'active' => true,
        ]);

        $gongcha = Brand::create([
            'name' => 'Gong Cha',
            'slug' => 'gong-cha',
            'primary_color' => '#8B4513',
            'description' => 'Premium tea brand from Taiwan specializing in artisan teas.',
            'points_multiplier' => 1.30,
            'rewards_budget' => 15000,
            'active' => true,
        ]);

        // --- Assign brands to existing outlets ---
        $outlets = Outlet::all();
        $brands = [$starbucks, $tigerSugar, $tealive, $zus, $gongcha];

        foreach ($outlets as $i => $outlet) {
            $outlet->update(['brand_id' => $brands[$i % count($brands)]->id]);
        }

        // --- Seed rewards for each brand ---

        // Starbucks rewards
        Reward::create(['brand_id' => $starbucks->id, 'name' => 'Free Tall Drink', 'description' => 'Any handcrafted Tall beverage on us.', 'points_cost' => 200, 'stock' => 50, 'sort_order' => 1]);
        Reward::create(['brand_id' => $starbucks->id, 'name' => 'RM5 Off Next Visit', 'description' => 'RM5 discount on your next purchase.', 'points_cost' => 100, 'stock' => 100, 'sort_order' => 2]);
        Reward::create(['brand_id' => $starbucks->id, 'name' => 'Starbucks Tote Bag', 'description' => 'Limited edition reusable tote bag.', 'points_cost' => 500, 'stock' => 20, 'sort_order' => 3]);

        // Tiger Sugar rewards
        Reward::create(['brand_id' => $tigerSugar->id, 'name' => 'Free Brown Sugar Boba', 'description' => 'Signature brown sugar boba milk tea.', 'points_cost' => 150, 'stock' => 40, 'sort_order' => 1]);
        Reward::create(['brand_id' => $tigerSugar->id, 'name' => 'Size Upgrade', 'description' => 'Free upgrade to Large on any drink.', 'points_cost' => 50, 'stock' => null, 'sort_order' => 2]);

        // Tealive rewards
        Reward::create(['brand_id' => $tealive->id, 'name' => 'Free Classic Tea', 'description' => 'Any classic milk tea series beverage.', 'points_cost' => 120, 'stock' => 80, 'sort_order' => 1]);
        Reward::create(['brand_id' => $tealive->id, 'name' => 'Buy 1 Free 1 Voucher', 'description' => 'Valid for any drink of equal or lesser value.', 'points_cost' => 250, 'stock' => 30, 'sort_order' => 2]);
        Reward::create(['brand_id' => $tealive->id, 'name' => 'Tealive Tumbler', 'description' => 'Reusable Tealive-branded tumbler. Save the planet in style.', 'points_cost' => 600, 'stock' => 15, 'sort_order' => 3]);

        // ZUS Coffee rewards
        Reward::create(['brand_id' => $zus->id, 'name' => 'Free Americano', 'description' => 'Hot or iced, your choice.', 'points_cost' => 100, 'stock' => 60, 'sort_order' => 1]);
        Reward::create(['brand_id' => $zus->id, 'name' => 'RM3 Off Any Latte', 'description' => 'Applies to any latte variant.', 'points_cost' => 80, 'stock' => null, 'sort_order' => 2]);

        // Gong Cha rewards
        Reward::create(['brand_id' => $gongcha->id, 'name' => 'Free Milk Tea', 'description' => 'Any signature milk tea with pearls.', 'points_cost' => 130, 'stock' => 40, 'sort_order' => 1]);

        // --- Mobius platform rewards (no brand) ---
        Reward::create(['brand_id' => null, 'name' => 'RM5 GrabFood Voucher', 'description' => 'Valid for any GrabFood order.', 'points_cost' => 300, 'stock' => 25, 'sort_order' => 1]);
        Reward::create(['brand_id' => null, 'name' => 'RM10 Shopee Voucher', 'description' => 'Minimum spend RM30.', 'points_cost' => 500, 'stock' => 10, 'sort_order' => 2]);
        Reward::create(['brand_id' => null, 'name' => 'Mobius Eco Badge', 'description' => 'Digital badge for your profile. Show your commitment to sustainability.', 'points_cost' => 50, 'stock' => null, 'sort_order' => 3]);
    }
}
