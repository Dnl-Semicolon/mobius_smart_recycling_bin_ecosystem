<?php

namespace Database\Seeders;

use App\Enums\BinStatus;
use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Outlet;
use Illuminate\Database\Seeder;

class BinSeeder extends Seeder
{
    public function run(): void
    {
        $outlets = Outlet::pluck('id', 'name');

        $bins = [
            ['serial' => 'MBR-2024-001', 'fill' => 25, 'status' => BinStatus::Active, 'outlet' => 'Starbucks Gurney Plaza'],
            ['serial' => 'MBR-2024-002', 'fill' => 15, 'status' => BinStatus::Active, 'outlet' => 'Starbucks Gurney Plaza'],
            ['serial' => 'MBR-2024-003', 'fill' => 30, 'status' => BinStatus::Active, 'outlet' => 'Starbucks Gurney Plaza'],
            ['serial' => 'MBR-2024-004', 'fill' => 45, 'status' => BinStatus::Active, 'outlet' => 'Starbucks Gurney Paragon'],
            ['serial' => 'MBR-2024-005', 'fill' => 22, 'status' => BinStatus::Active, 'outlet' => 'Starbucks 1st Avenue Mall'],
            ['serial' => 'MBR-2024-006', 'fill' => 55, 'status' => BinStatus::Active, 'outlet' => 'Starbucks Sunway Carnival Mall'],
            ['serial' => 'MBR-2024-007', 'fill' => 68, 'status' => BinStatus::Active, 'outlet' => 'Tealive Prangin Mall'],
            ['serial' => 'MBR-2024-008', 'fill' => 72, 'status' => BinStatus::Active, 'outlet' => 'CHAGEE Gurney Plaza'],
            ['serial' => 'MBR-2024-009', 'fill' => 85, 'status' => BinStatus::Active, 'outlet' => 'CHAGEE Gurney Plaza'],
            ['serial' => 'MBR-2024-010', 'fill' => 92, 'status' => BinStatus::Active, 'outlet' => 'CHAGEE Gurney Plaza'],
            ['serial' => 'MBR-2024-011', 'fill' => 80, 'status' => BinStatus::Active, 'outlet' => 'Tealive Bayan Baru'],
            ['serial' => 'MBR-2024-012', 'fill' => 0, 'status' => BinStatus::Maintenance, 'outlet' => null],
            ['serial' => 'MBR-2024-013', 'fill' => 35, 'status' => BinStatus::Active, 'outlet' => 'Oldtown White Coffee Gurney Plaza'],
            ['serial' => 'MBR-2024-014', 'fill' => 40, 'status' => BinStatus::Active, 'outlet' => null],
            ['serial' => 'MBR-2024-015', 'fill' => 0, 'status' => BinStatus::Inactive, 'outlet' => null],
        ];

        foreach ($bins as $binData) {
            $bin = Bin::create([
                'serial_number' => $binData['serial'],
                'fill_level' => $binData['fill'],
                'status' => $binData['status'],
            ]);

            if ($binData['outlet'] && isset($outlets[$binData['outlet']])) {
                BinAssignment::create([
                    'bin_id' => $bin->id,
                    'outlet_id' => $outlets[$binData['outlet']],
                    'assigned_at' => now()->subDays(rand(30, 180)),
                    'unassigned_at' => null,
                ]);
            }
        }

        $this->createHistoricalAssignments($outlets);
    }

    private function createHistoricalAssignments($outlets): void
    {
        $bin6 = Bin::where('serial_number', 'MBR-2024-006')->first();
        if ($bin6 && isset($outlets['Starbucks Gurney Paragon'])) {
            BinAssignment::create([
                'bin_id' => $bin6->id,
                'outlet_id' => $outlets['Starbucks Gurney Paragon'],
                'assigned_at' => now()->subDays(200),
                'unassigned_at' => now()->subDays(90),
            ]);
        }

        $bin12 = Bin::where('serial_number', 'MBR-2024-012')->first();
        if ($bin12 && isset($outlets['Oldtown White Coffee Gurney Plaza'])) {
            BinAssignment::create([
                'bin_id' => $bin12->id,
                'outlet_id' => $outlets['Oldtown White Coffee Gurney Plaza'],
                'assigned_at' => now()->subDays(150),
                'unassigned_at' => now()->subDays(7),
            ]);
        }
    }
}
