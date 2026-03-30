<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\BrandApplication;
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

    public function registerBrandClaim(array $data): BrandApplication
    {
        return DB::transaction(function () use ($data) {
            $brand = Brand::findOrFail($data['brand_id']);

            $user = User::create([
                'name' => $data['contact_person'],
                'email' => $data['email'],
                'password' => $data['password'],
                'roles' => ['public_user'],
            ]);

            $application = BrandApplication::create([
                'brand_id' => $brand->id,
                'user_id' => $user->id,
                'status' => ApplicationStatus::Pending,
                'brand_name' => $brand->name,
                'contact_person' => $data['contact_person'],
                'contact_email' => $data['email'],
                'contact_phone' => $data['phone'] ?? null,
            ]);

            $this->notifications->notifyWelcome($user);

            Log::info('Brand claim submitted', [
                'application_id' => $application->id,
                'brand_id' => $brand->id,
                'email' => $data['email'],
            ]);

            return $application;
        });
    }

    public function registerNewBrand(array $data): BrandApplication
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['contact_person'],
                'email' => $data['email'],
                'password' => $data['password'],
                'roles' => ['public_user'],
            ]);

            $application = BrandApplication::create([
                'brand_id' => null,
                'user_id' => $user->id,
                'status' => ApplicationStatus::Pending,
                'brand_name' => $data['brand_name'],
                'description' => $data['description'] ?? null,
                'website_url' => $data['website_url'] ?? null,
                'logo_path' => $data['logo_path'] ?? null,
                'contact_person' => $data['contact_person'],
                'contact_email' => $data['email'],
                'contact_phone' => $data['phone'] ?? null,
            ]);

            $this->notifications->notifyWelcome($user);

            Log::info('New brand request submitted', [
                'application_id' => $application->id,
                'brand_name' => $data['brand_name'],
                'email' => $data['email'],
            ]);

            return $application;
        });
    }

    public function approveBrandApplication(BrandApplication $application, User $admin, array $config): BrandApplication
    {
        return DB::transaction(function () use ($application, $admin, $config) {
            if ($application->isClaimingExisting()) {
                $application->brand->update([
                    'user_id' => $application->user_id,
                    'points_multiplier' => $config['points_multiplier'],
                    'rewards_budget' => $config['rewards_budget'],
                ]);
            } else {
                $brand = Brand::create([
                    'name' => $application->brand_name,
                    'description' => $application->description,
                    'website_url' => $application->website_url,
                    'logo_path' => $application->logo_path,
                    'status' => ApplicationStatus::Approved,
                    'active' => true,
                    'points_multiplier' => $config['points_multiplier'],
                    'rewards_budget' => $config['rewards_budget'],
                    'user_id' => $application->user_id,
                ]);

                $application->brand_id = $brand->id;
            }

            $application->update([
                'status' => ApplicationStatus::Approved,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $application->user->addRole(UserRole::StoreOwner);

            $this->notifications->notifySystem(
                $application->user,
                'Brand Application Approved',
                "Your application for \"{$application->brand_name}\" has been approved! You can now manage your brand."
            );

            Log::info('Brand application approved', [
                'application_id' => $application->id,
                'approved_by' => $admin->id,
            ]);

            return $application;
        });
    }

    public function rejectBrandApplication(BrandApplication $application, User $admin, string $reason): BrandApplication
    {
        $application->update([
            'status' => ApplicationStatus::Rejected,
            'rejection_reason' => $reason,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $this->notifications->notifySystem(
            $application->user,
            'Brand Application Update',
            "Your application for \"{$application->brand_name}\" was not approved. Reason: {$reason}"
        );

        Log::info('Brand application rejected', [
            'application_id' => $application->id,
            'rejected_by' => $admin->id,
        ]);

        return $application;
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
