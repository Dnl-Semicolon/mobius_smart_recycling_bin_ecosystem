<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\CollectorAgency;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplicationService
{
    public function __construct(private NotificationService $notifications) {}

    public function registerBrand(array $data): Brand
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['contact_person'],
                'email' => $data['email'],
                'password' => $data['password'],
                'roles' => ['public_user'],
            ]);

            $brand = Brand::create([
                'name' => $data['name'],
                'contact_person' => $data['contact_person'],
                'contact_email' => $data['email'],
                'contact_phone' => $data['phone'] ?? null,
                'description' => $data['description'] ?? null,
                'website_url' => $data['website_url'] ?? null,
                'logo_path' => $data['logo_path'] ?? null,
                'status' => ApplicationStatus::Pending,
                'active' => false,
                'points_multiplier' => 1.00,
                'rewards_budget' => 0,
                'user_id' => $user->id,
            ]);

            $this->notifications->notifyWelcome($user);

            Log::info('Brand registration submitted — confirmation email would be sent', [
                'brand_id' => $brand->id,
                'email' => $data['email'],
            ]);

            return $brand;
        });
    }

    public function approveBrand(Brand $brand, User $admin, array $config): Brand
    {
        return DB::transaction(function () use ($brand, $admin, $config) {
            $brand->update([
                'status' => ApplicationStatus::Approved,
                'active' => true,
                'points_multiplier' => $config['points_multiplier'],
                'rewards_budget' => $config['rewards_budget'],
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            if ($brand->adminUser) {
                $brand->adminUser->addRole(UserRole::StoreOwner);

                $this->notifications->notifySystem(
                    $brand->adminUser,
                    'Brand Application Approved',
                    "Your brand \"{$brand->name}\" has been approved! You can now manage your outlets and rewards."
                );
            }

            Log::info('Brand application approved — notification email would be sent', [
                'brand_id' => $brand->id,
                'approved_by' => $admin->id,
            ]);

            return $brand;
        });
    }

    public function rejectBrand(Brand $brand, User $admin, string $reason): Brand
    {
        $brand->update([
            'status' => ApplicationStatus::Rejected,
            'rejection_reason' => $reason,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        if ($brand->adminUser) {
            $this->notifications->notifySystem(
                $brand->adminUser,
                'Brand Application Update',
                "Your brand application for \"{$brand->name}\" was not approved. Reason: {$reason}"
            );
        }

        Log::info('Brand application rejected — notification email would be sent', [
            'brand_id' => $brand->id,
            'rejected_by' => $admin->id,
        ]);

        return $brand;
    }

    public function registerAgency(array $data): CollectorAgency
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['contact_person'],
                'email' => $data['email'],
                'password' => $data['password'],
                'roles' => ['public_user'],
            ]);

            $agency = CollectorAgency::create([
                'name' => $data['name'],
                'contact_person' => $data['contact_person'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'description' => $data['description'] ?? null,
                'fleet_size' => $data['fleet_size'] ?? 0,
                'coverage_area' => $data['coverage_area'] ?? null,
                'logo_path' => $data['logo_path'] ?? null,
                'status' => ApplicationStatus::Pending,
                'user_id' => $user->id,
            ]);

            $this->notifications->notifyWelcome($user);

            Log::info('Agency registration submitted — confirmation email would be sent', [
                'agency_id' => $agency->id,
                'email' => $data['email'],
            ]);

            return $agency;
        });
    }

    public function approveAgency(CollectorAgency $agency, User $admin): CollectorAgency
    {
        return DB::transaction(function () use ($agency, $admin) {
            $agency->update([
                'status' => ApplicationStatus::Approved,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            if ($agency->adminUser) {
                $agency->adminUser->addRole(UserRole::AgencyAdmin);

                $this->notifications->notifySystem(
                    $agency->adminUser,
                    'Agency Application Approved',
                    "Your agency \"{$agency->name}\" has been approved! You can now manage your collectors and view routes."
                );
            }

            Log::info('Agency application approved — notification email would be sent', [
                'agency_id' => $agency->id,
                'approved_by' => $admin->id,
            ]);

            return $agency;
        });
    }

    public function rejectAgency(CollectorAgency $agency, User $admin, string $reason): CollectorAgency
    {
        $agency->update([
            'status' => ApplicationStatus::Rejected,
            'rejection_reason' => $reason,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        if ($agency->adminUser) {
            $this->notifications->notifySystem(
                $agency->adminUser,
                'Agency Application Update',
                "Your agency application for \"{$agency->name}\" was not approved. Reason: {$reason}"
            );
        }

        Log::info('Agency application rejected — notification email would be sent', [
            'agency_id' => $agency->id,
            'rejected_by' => $admin->id,
        ]);

        return $agency;
    }
}
