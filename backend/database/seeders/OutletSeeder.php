<?php

namespace Database\Seeders;

use App\Enums\ContractStatus;
use App\Models\Outlet;
use Illuminate\Database\Seeder;

class OutletSeeder extends Seeder
{
    public function run(): void
    {
        $outlets = [
            [
                'name' => 'Starbucks KLCC',
                'address' => 'Lot 241, Level 2, Suria KLCC, Kuala Lumpur',
                'latitude' => 3.1588,
                'longitude' => 101.7116,
                'contact_name' => 'Ahmad Rahman',
                'contact_phone' => '012-345 6789',
                'contact_email' => 'klcc@example.com',
                'operating_hours' => '10:00-22:00',
                'contract_status' => ContractStatus::Active,
                'notes' => 'High traffic location. 3 bins allocated.',
            ],
            [
                'name' => 'Coffee Bean Pavilion',
                'address' => '168, Jalan Bukit Bintang, 55100 Kuala Lumpur',
                'latitude' => 3.1490,
                'longitude' => 101.7131,
                'contact_name' => 'Siti Aminah',
                'contact_phone' => '012-456 7890',
                'contact_email' => 'pavilion@example.com',
                'operating_hours' => '10:00-22:00',
                'contract_status' => ContractStatus::Active,
                'notes' => null,
            ],
            [
                'name' => 'ZUS Coffee Mid Valley',
                'address' => 'Mid Valley Megamall, Lingkaran Syed Putra, 59200 Kuala Lumpur',
                'latitude' => 3.1177,
                'longitude' => 101.6773,
                'contact_name' => 'Lee Wei Ming',
                'contact_phone' => '013-567 8901',
                'contact_email' => 'midvalley@example.com',
                'operating_hours' => '10:00-22:00',
                'contract_status' => ContractStatus::Active,
                'notes' => null,
            ],
            [
                'name' => 'Tealive SS2',
                'address' => '55, Jalan SS 2/75, SS 2, 47300 Petaling Jaya',
                'latitude' => 3.1180,
                'longitude' => 101.6197,
                'contact_name' => 'Tan Mei Ling',
                'contact_phone' => '014-678 9012',
                'contact_email' => 'ss2@example.com',
                'operating_hours' => '11:00-23:00',
                'contract_status' => ContractStatus::Active,
                'notes' => 'Popular student area.',
            ],
            [
                'name' => 'Gong Cha Sunway Pyramid',
                'address' => 'Sunway Pyramid, 3, Jalan PJS 11/15, Bandar Sunway',
                'latitude' => 3.0733,
                'longitude' => 101.6067,
                'contact_name' => 'Wong Chee Keong',
                'contact_phone' => '015-789 0123',
                'contact_email' => 'sunway@example.com',
                'operating_hours' => '10:00-22:00',
                'contract_status' => ContractStatus::Active,
                'notes' => null,
            ],
            [
                'name' => 'Food Republic Pavilion',
                'address' => 'Level 1, Pavilion KL, 168 Jalan Bukit Bintang',
                'latitude' => 3.1489,
                'longitude' => 101.7129,
                'contact_name' => 'Krishnan Nair',
                'contact_phone' => '016-890 1234',
                'contact_email' => 'foodrep@example.com',
                'operating_hours' => '10:00-22:00',
                'contract_status' => ContractStatus::Active,
                'notes' => 'High volume food court. 4 bins allocated.',
            ],
            [
                'name' => 'Signature Food Court Mid Valley',
                'address' => 'Level 3, Mid Valley Megamall, 59200 Kuala Lumpur',
                'latitude' => 3.1175,
                'longitude' => 101.6771,
                'contact_name' => 'Muthu Raj',
                'contact_phone' => '017-901 2345',
                'contact_email' => 'signature@example.com',
                'operating_hours' => '10:00-22:00',
                'contract_status' => ContractStatus::Active,
                'notes' => null,
            ],
            [
                'name' => 'UM Engineering Cafe',
                'address' => 'Faculty of Engineering, University of Malaya, 50603 Kuala Lumpur',
                'latitude' => 3.1209,
                'longitude' => 101.6538,
                'contact_name' => 'Dr. Fatimah Hassan',
                'contact_phone' => '018-012 3456',
                'contact_email' => 'umeng@example.com',
                'operating_hours' => '08:00-18:00',
                'contract_status' => ContractStatus::Pending,
                'notes' => 'Pilot program with university. Contract pending approval.',
            ],
            [
                'name' => 'Nexus Bangsar South Lobby',
                'address' => 'Nexus Tower, 7, Jalan Kerinchi, Bangsar South',
                'latitude' => 3.1106,
                'longitude' => 101.6654,
                'contact_name' => 'Jenny Lim',
                'contact_phone' => '019-123 4567',
                'contact_email' => 'nexus@example.com',
                'operating_hours' => '07:00-21:00',
                'contract_status' => ContractStatus::Active,
                'notes' => 'Corporate building. 2 bins in lobby.',
            ],
            [
                'name' => 'Chatime Cheras (Closed)',
                'address' => '123, Jalan Cheras, 56100 Kuala Lumpur',
                'latitude' => 3.1066,
                'longitude' => 101.7322,
                'contact_name' => 'Former Contact',
                'contact_phone' => '010-234 5678',
                'contact_email' => 'closed@example.com',
                'operating_hours' => null,
                'contract_status' => ContractStatus::Inactive,
                'notes' => 'Contract terminated. Outlet closed in Dec 2024.',
            ],
        ];

        foreach ($outlets as $outletData) {
            Outlet::create($outletData);
        }
    }
}
