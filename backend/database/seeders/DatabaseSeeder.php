<?php

namespace Database\Seeders;

use App\Enums\ContractStatus;
use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Brand;
use App\Models\Outlet;
use App\Models\Reward;
use App\Models\User;
use App\Models\Zone;
use App\Services\RouteOptimizationService;
use Database\Factories\OutletFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        /*
        |----------------------------------------------------------------------
        | Users (all passwords are "password")
        |----------------------------------------------------------------------
        */
        // User::factory()->admin()->create([
        //     'name' => 'Admin',
        //     'email' => 'admin@mobius.test',
        // ]);

        // $collector = User::factory()->collector()->create([
        //     'name' => 'Collector',
        //     'email' => 'collector@mobius.test',
        // ]);

        // $testUser = User::factory()->publicUser()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // // $daniel = User::factory()->create([
        // //     'name' => 'Daniel Tan',
        // //     'email' => 'daniel@mobius.test',
        // //     'roles' => ['public_user', 'store_owner', 'collector'],
        // // ]);

        // $storeOwner = User::factory()->create([
        //     'name' => 'Sarah Lim',
        //     'email' => 'storeowner@mobius.test',
        //     'roles' => ['store_owner'],
        // ]);

        /*
        |----------------------------------------------------------------------
        | Brands
        |----------------------------------------------------------------------
        */
        $starbucks = Brand::create([
            'name' => 'Starbucks',
            'slug' => 'starbucks',
            'primary_color' => '#00704A',
            'description' => 'Global coffeehouse chain committed to ethical sourcing and environmental stewardship.',
            'points_multiplier' => 1.50,
            'rewards_budget' => 50000,
            'active' => true,
        ]);

        $chagee = Brand::create([
            'name' => 'CHAGEE',
            'slug' => 'chagee',
            'primary_color' => '#C41E3A',
            'description' => 'Premium Chinese tea chain blending traditional tea culture with modern lifestyle.',
            'points_multiplier' => 1.40,
            'rewards_budget' => 35000,
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

        $mixue = Brand::create([
            'name' => 'Mixue',
            'slug' => 'mixue',
            'primary_color' => '#E8352E',
            'description' => 'Chinese ice cream and tea chain. Affordable drinks, massive scale.',
            'points_multiplier' => 1.30,
            'rewards_budget' => 25000,
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

        $gongcha = Brand::create([
            'name' => 'Gong Cha',
            'slug' => 'gong-cha',
            'primary_color' => '#8B4513',
            'description' => 'Premium tea brand from Taiwan specializing in artisan teas.',
            'points_multiplier' => 1.30,
            'rewards_budget' => 15000,
            'active' => true,
        ]);

        /*
        |----------------------------------------------------------------------
        | Outlets — seed photos from database/seeders/images/outlets/
        |----------------------------------------------------------------------
        */
        $seedImages = database_path('seeders/images/outlets');
        Storage::disk('public')->makeDirectory('outlets');

        $sbuxGurney = Outlet::create([
            'name' => 'Starbucks Reserve Gurney Plaza',
            'brand_id' => $starbucks->id,
            'address' => 'Gurney Plaza, Persiaran Gurney, 10250 George Town, Penang',
            'latitude' => 5.43700000,
            'longitude' => 100.31000000,
            'contact_name' => 'Lim Bee Hoon',
            'contact_phone' => '012-483 7291',
            'operating_hours' => OutletFactory::buildStructuredHours('10:00', '22:00'),
            'contract_status' => ContractStatus::Active,
            'photo_path' => $this->seedPhoto($seedImages, 'starbucks-gurney.jpg'),
            'notes' => 'High traffic mall location. Mock bin MBR-2026-001 is here.',
        ]);

        $chageeGurney = Outlet::create([
            'name' => 'CHAGEE Gurney Plaza Flagship',
            'brand_id' => $chagee->id,
            'address' => '170-G-42, Gurney Plaza, Persiaran Gurney, 10250 George Town, Pulau Pinang, Malaysia',
            'latitude' => 5.43624180,
            'longitude' => 100.30930440,
            'contact_name' => 'Chen Wei Ling',
            'contact_phone' => '010-825 4674',
            'operating_hours' => OutletFactory::buildStructuredHours('10:00', '22:00'),
            'contract_status' => ContractStatus::Active,
            'photo_path' => $this->seedPhoto($seedImages, 'chagee-gurney.jpg'),
        ]);

        $tealiveKomtar = Outlet::create([
            'name' => 'Tealive Komtar',
            'brand_id' => $tealive->id,
            'address' => 'Komtar Walk, Jalan Penang, 10000 George Town, Penang',
            'latitude' => 5.41470000,
            'longitude' => 100.33080000,
            'contact_name' => 'Tan Mei Ying',
            'contact_phone' => '017-492 8833',
            'operating_hours' => OutletFactory::buildStructuredHours('09:00', '21:00'),
            'contract_status' => ContractStatus::Active,
        ]);

        $zusBayanLepas = Outlet::create([
            'name' => 'ZUS Coffee Queensbay',
            'brand_id' => $zus->id,
            'address' => 'Queensbay Mall, 11900 Bayan Lepas, Penang',
            'latitude' => 5.33280000,
            'longitude' => 100.30640000,
            'contact_name' => 'Amir Razak',
            'contact_phone' => '019-384 2019',
            'operating_hours' => OutletFactory::buildStructuredHours('08:00', '22:00'),
            'contract_status' => ContractStatus::Active,
        ]);

        $mixueTanjungTokong = Outlet::create([
            'name' => 'Mixue Tanjung Tokong',
            'brand_id' => $mixue->id,
            'address' => 'Jalan Tanjung Tokong, 10470 Tanjung Tokong, Penang',
            'latitude' => 5.44500000,
            'longitude' => 100.30200000,
            'contact_name' => 'Lee Xin Yi',
            'contact_phone' => '011-283 9102',
            'operating_hours' => OutletFactory::buildStructuredHours('10:00', '22:00'),
            'contract_status' => ContractStatus::Active,
        ]);

        $zusTanjungBungah = Outlet::create([
            'name' => 'ZUS Coffee - Tree Square Tanjung Bungah',
            'brand_id' => $zus->id,
            'address' => '88-G-A05, Jalan Sungai Kelian, 11200 Tanjung Bungah, Pulau Pinang, Malaysia',
            'latitude' => 5.46406970,
            'longitude' => 100.28404670,
            'contact_name' => 'Ahmad Razif bin Hassan',
            'contact_phone' => '012-456 7890',
            'contact_email' => 'razif.hassan@zuscoffee.com',
            'operating_hours' => OutletFactory::buildStructuredHours('08:00', '22:00'),
            'contract_status' => ContractStatus::Active,
            'photo_path' => $this->seedPhoto($seedImages, 'zus-tanjung-bungah.jpg'),
            'notes' => 'Located at Tree Square commercial area, ground floor unit facing main road. Near Tanjung Bungah floating mosque. Parking available at Tree Square basement. High foot traffic area with residential condos nearby.',
        ]);

        /*
        |----------------------------------------------------------------------
        | Bins — pre-filled for route optimization demo (≥80% triggers eligibility)
        |----------------------------------------------------------------------
        */
        $bin1 = Bin::create(['serial_number' => 'MBR-2026-001', 'fill_level' => 95, 'status' => 'active']);
        $bin2 = Bin::create(['serial_number' => 'MBR-2026-002', 'fill_level' => 88, 'status' => 'active']);
        $bin3 = Bin::create(['serial_number' => 'MBR-2026-003', 'fill_level' => 92, 'status' => 'active']);
        $bin4 = Bin::create(['serial_number' => 'MBR-2026-004', 'fill_level' => 85, 'status' => 'active']);
        $bin5 = Bin::create(['serial_number' => 'MBR-2026-005', 'fill_level' => 85, 'status' => 'active']);
        $bin6 = Bin::create(['serial_number' => 'MBR-2026-006', 'fill_level' => 90, 'status' => 'active']);

        // Assign bins to outlets
        BinAssignment::create(['bin_id' => $bin1->id, 'outlet_id' => $sbuxGurney->id, 'assigned_at' => now()]);        // Starbucks — mock bin
        BinAssignment::create(['bin_id' => $bin2->id, 'outlet_id' => $mixueTanjungTokong->id, 'assigned_at' => now()]); // Mixue
        BinAssignment::create(['bin_id' => $bin3->id, 'outlet_id' => $chageeGurney->id, 'assigned_at' => now()]);      // CHAGEE
        BinAssignment::create(['bin_id' => $bin4->id, 'outlet_id' => $tealiveKomtar->id, 'assigned_at' => now()]);     // Tealive
        BinAssignment::create(['bin_id' => $bin5->id, 'outlet_id' => $zusBayanLepas->id, 'assigned_at' => now()]);     // ZUS Queensbay
        BinAssignment::create(['bin_id' => $bin6->id, 'outlet_id' => $zusTanjungBungah->id, 'assigned_at' => now()]);  // ZUS Tanjung Bungah

        /*
        |----------------------------------------------------------------------
        | Store owner → outlet links
        |----------------------------------------------------------------------
        */
        $sbuxGurney->managers()->attach($storeOwner->id, ['role' => 'manager']);
        $chageeGurney->managers()->attach($daniel->id, ['role' => 'manager']);

        /*
        |----------------------------------------------------------------------
        | Rewards
        |----------------------------------------------------------------------
        */
        // Starbucks
        Reward::create(['brand_id' => $starbucks->id, 'name' => 'Free Tall Drink', 'description' => 'Any handcrafted Tall beverage on us.', 'points_cost' => 200, 'stock' => 50, 'sort_order' => 1]);
        Reward::create(['brand_id' => $starbucks->id, 'name' => 'RM5 Off Next Visit', 'description' => 'RM5 discount on your next purchase.', 'points_cost' => 100, 'stock' => 100, 'sort_order' => 2]);
        Reward::create(['brand_id' => $starbucks->id, 'name' => 'Starbucks Tote Bag', 'description' => 'Limited edition reusable tote bag.', 'points_cost' => 500, 'stock' => 20, 'sort_order' => 3]);

        // CHAGEE
        Reward::create(['brand_id' => $chagee->id, 'name' => 'Free Latte', 'description' => 'Any CHAGEE signature latte beverage.', 'points_cost' => 150, 'stock' => 40, 'sort_order' => 1]);
        Reward::create(['brand_id' => $chagee->id, 'name' => 'Size Upgrade', 'description' => 'Free upgrade to Large on any drink.', 'points_cost' => 60, 'stock' => null, 'sort_order' => 2]);

        // Tealive
        Reward::create(['brand_id' => $tealive->id, 'name' => 'Free Classic Tea', 'description' => 'Any classic milk tea series beverage.', 'points_cost' => 120, 'stock' => 80, 'sort_order' => 1]);
        Reward::create(['brand_id' => $tealive->id, 'name' => 'Buy 1 Free 1 Voucher', 'description' => 'Valid for any drink of equal or lesser value.', 'points_cost' => 250, 'stock' => 30, 'sort_order' => 2]);
        Reward::create(['brand_id' => $tealive->id, 'name' => 'Tealive Tumbler', 'description' => 'Reusable Tealive-branded tumbler.', 'points_cost' => 600, 'stock' => 15, 'sort_order' => 3]);

        // ZUS Coffee
        Reward::create(['brand_id' => $zus->id, 'name' => 'Free Americano', 'description' => 'Hot or iced, your choice.', 'points_cost' => 100, 'stock' => 60, 'sort_order' => 1]);
        Reward::create(['brand_id' => $zus->id, 'name' => 'RM3 Off Any Latte', 'description' => 'Applies to any latte variant.', 'points_cost' => 80, 'stock' => null, 'sort_order' => 2]);

        // Mixue
        Reward::create(['brand_id' => $mixue->id, 'name' => 'Free Sundae', 'description' => 'Any Mixue signature ice cream sundae.', 'points_cost' => 80, 'stock' => 100, 'sort_order' => 1]);
        Reward::create(['brand_id' => $mixue->id, 'name' => 'RM2 Off Any Drink', 'description' => 'Valid for any drink on the menu.', 'points_cost' => 50, 'stock' => null, 'sort_order' => 2]);

        // Tiger Sugar
        Reward::create(['brand_id' => $tigerSugar->id, 'name' => 'Free Brown Sugar Boba', 'description' => 'Signature brown sugar boba milk tea.', 'points_cost' => 150, 'stock' => 40, 'sort_order' => 1]);

        // Gong Cha
        Reward::create(['brand_id' => $gongcha->id, 'name' => 'Free Milk Tea', 'description' => 'Any signature milk tea with pearls.', 'points_cost' => 130, 'stock' => 40, 'sort_order' => 1]);

        // Mobius platform rewards (no brand)
        Reward::create(['brand_id' => null, 'name' => 'RM5 GrabFood Voucher', 'description' => 'Valid for any GrabFood order.', 'points_cost' => 300, 'stock' => 25, 'sort_order' => 1]);
        Reward::create(['brand_id' => null, 'name' => 'RM10 Shopee Voucher', 'description' => 'Minimum spend RM30.', 'points_cost' => 500, 'stock' => 10, 'sort_order' => 2]);
        Reward::create(['brand_id' => null, 'name' => 'Mobius Eco Badge', 'description' => 'Digital badge for your profile.', 'points_cost' => 50, 'stock' => null, 'sort_order' => 3]);

        /*
        |----------------------------------------------------------------------
        | Zones (route optimization)
        |----------------------------------------------------------------------
        */
        $this->call(ZoneSeeder::class);

        /*
        |----------------------------------------------------------------------
        | Collection Routes — auto-generate via VROOM (Docker must be running)
        | Falls back to nearest-neighbor if VROOM is unavailable.
        |----------------------------------------------------------------------
        */
        $routeService = new RouteOptimizationService;
        $routeCount = 0;
        foreach (Zone::where('is_active', true)->get() as $zone) {
            $route = $routeService->generateRoute($zone);
            if ($route) {
                $routeCount++;
            }
        }
        $this->command?->info("  → {$routeCount} collection route(s) generated.");
    }

    /**
     * Copy a seed image to storage and return the relative path.
     */
    private function seedPhoto(string $sourceDir, string $filename): ?string
    {
        $source = $sourceDir.'/'.$filename;

        if (! File::exists($source)) {
            return null;
        }

        $dest = 'outlets/'.uniqid('outlet_').'.jpg';
        Storage::disk('public')->put($dest, File::get($source));

        return $dest;
    }
}
