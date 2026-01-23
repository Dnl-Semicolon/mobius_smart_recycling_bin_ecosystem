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
                'name' => 'Starbucks Gurney Plaza',
                'address' => 'Lot 170-G-11, Gurney Plaza, Persiaran Gurney, 10250 George Town, Penang',
                'latitude' => 5.4370,
                'longitude' => 100.3100,
                'contact_name' => 'Lim Bee Hoon',
                'contact_phone' => '012-483 7291',
                'contact_email' => 'gurneyplaza@example.com',
                'operating_hours' => '10:00-22:00',
                'contract_status' => ContractStatus::Active,
                'notes' => 'High traffic mall location. 3 bins allocated.',
            ],
            [
                'name' => 'Starbucks Gurney Paragon',
                'address' => 'Lot G-25, Gurney Paragon Mall, Persiaran Gurney, 10250 George Town, Penang',
                'latitude' => 5.4378,
                'longitude' => 100.3108,
                'contact_name' => 'Tan Kah Wei',
                'contact_phone' => '012-519 4603',
                'contact_email' => 'gurneyparagon@example.com',
                'operating_hours' => '10:00-22:00',
                'contract_status' => ContractStatus::Active,
                'notes' => null,
            ],
            [
                'name' => 'Starbucks Sunway Carnival Mall',
                'address' => 'Sunway Carnival Mall, 3068, Jalan Todak, 13700 Seberang Jaya, Penang',
                'latitude' => 5.3983,
                'longitude' => 100.3957,
                'contact_name' => 'Mohd Faizal bin Ismail',
                'contact_phone' => '013-472 8156',
                'contact_email' => 'sunwaycarnival@example.com',
                'operating_hours' => '10:00-22:00',
                'contract_status' => ContractStatus::Active,
                'notes' => null,
            ],
            [
                'name' => 'Starbucks 1st Avenue Mall',
                'address' => '1st Avenue Mall, 182, Jalan Magazine, 10300 George Town, Penang',
                'latitude' => 5.4145,
                'longitude' => 100.3310,
                'contact_name' => 'R. Priya',
                'contact_phone' => '014-638 2947',
                'contact_email' => '1stavenue@example.com',
                'operating_hours' => '10:00-22:00',
                'contract_status' => ContractStatus::Active,
                'notes' => null,
            ],
            [
                'name' => 'CHAGEE Gurney Plaza',
                'address' => 'Unit 170-B1-20/21 & 170-G-42, Gurney Plaza, Persiaran Gurney, 10250 George Town, Penang',
                'latitude' => 5.4372,
                'longitude' => 100.3098,
                'contact_name' => 'Chen Wei Ling',
                'contact_phone' => '016-754 3182',
                'contact_email' => 'chagee.gurney@example.com',
                'operating_hours' => '10:00-22:00',
                'contract_status' => ContractStatus::Active,
                'notes' => 'First CHAGEE flagship in Penang. High traffic, 3 bins allocated.',
            ],
            [
                'name' => 'Tealive Prangin Mall',
                'address' => 'Prangin Mall, Jalan Dr Lim Chwee Leong, 10100 George Town, Penang',
                'latitude' => 5.4144,
                'longitude' => 100.3285,
                'contact_name' => 'Nurul Aisyah binti Ahmad',
                'contact_phone' => '017-295 4718',
                'contact_email' => 'tealive.prangin@example.com',
                'operating_hours' => '10:00-22:00',
                'contract_status' => ContractStatus::Active,
                'notes' => null,
            ],
            [
                'name' => 'Tealive Bayan Baru',
                'address' => 'Petronas, Jalan Sungai Nibong, 11900 Bayan Baru, Penang',
                'latitude' => 5.3195,
                'longitude' => 100.2830,
                'contact_name' => 'Wong Siew Mei',
                'contact_phone' => '012-467 9305',
                'contact_email' => 'tealive.bayanbaru@example.com',
                'operating_hours' => '08:00-22:00',
                'contract_status' => ContractStatus::Active,
                'notes' => null,
            ],
            [
                'name' => 'Tealive Gurney Plaza',
                'address' => 'Lot 170-B1-K3, Gurney Plaza, Persiaran Gurney, 10250 George Town, Penang',
                'latitude' => 5.4368,
                'longitude' => 100.3102,
                'contact_name' => 'Kavitha a/p Subramaniam',
                'contact_phone' => '018-371 6429',
                'contact_email' => 'tealive.gurney@example.com',
                'operating_hours' => '10:00-22:00',
                'contract_status' => ContractStatus::Pending,
                'notes' => 'New franchise outlet. Contract pending final approval from HQ.',
            ],
            [
                'name' => 'Oldtown White Coffee Gurney Plaza',
                'address' => 'Lot 170-G-08, Gurney Plaza, Persiaran Gurney, 10250 George Town, Penang',
                'latitude' => 5.4371,
                'longitude' => 100.3099,
                'contact_name' => 'Ahmad Zulkifli bin Osman',
                'contact_phone' => '019-582 3041',
                'contact_email' => 'oldtown.gurney@example.com',
                'operating_hours' => '08:00-22:00',
                'contract_status' => ContractStatus::Active,
                'notes' => null,
            ],
            [
                'name' => 'Arang Coffee Bayan Lepas',
                'address' => '38, Jalan Mahsuri, 11950 Bayan Lepas, Penang',
                'latitude' => 5.3120,
                'longitude' => 100.2780,
                'contact_name' => 'Lee Chong Huat',
                'contact_phone' => '010-847 2563',
                'contact_email' => 'arang.bl@example.com',
                'operating_hours' => null,
                'contract_status' => ContractStatus::Inactive,
                'notes' => 'Contract terminated. Outlet permanently closed in Nov 2024.',
            ],
        ];

        foreach ($outlets as $outletData) {
            Outlet::create($outletData);
        }
    }
}
