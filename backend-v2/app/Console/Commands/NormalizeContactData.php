<?php

namespace App\Console\Commands;

use App\Models\RegistrationRequest;
use App\Models\User;
use App\Support\EmailNormalizer;
use App\Support\PhoneNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NormalizeContactData extends Command
{
    protected $signature = 'contacts:normalize {--dry-run : Preview changes without saving them}';

    protected $description = 'Normalize stored user and registration request contact data.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $userChanges = User::query()
            ->select(['id', 'email', 'phone'])
            ->orderBy('id')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'original_email' => $user->email,
                'normalized_email' => EmailNormalizer::normalize($user->email),
                'original_phone' => $user->phone,
                'normalized_phone' => PhoneNormalizer::normalize($user->phone),
                'invalid_phone' => $user->phone !== null
                    && trim($user->phone) !== ''
                    && PhoneNormalizer::normalize($user->phone) === null,
            ]);

        $leadChanges = RegistrationRequest::query()
            ->select(['id', 'contact_email', 'contact_phone'])
            ->orderBy('id')
            ->get()
            ->map(fn (RegistrationRequest $lead) => [
                'id' => $lead->id,
                'original_email' => $lead->contact_email,
                'normalized_email' => EmailNormalizer::normalize($lead->contact_email),
                'original_phone' => $lead->contact_phone,
                'normalized_phone' => PhoneNormalizer::normalize($lead->contact_phone),
                'invalid_phone' => trim($lead->contact_phone) !== ''
                    && PhoneNormalizer::normalize($lead->contact_phone) === null,
            ]);

        $userEmailCollisions = $this->findCollisions($userChanges, 'normalized_email');
        $userPhoneCollisions = $this->findCollisions($userChanges, 'normalized_phone');
        $invalidUserPhones = $userChanges->where('invalid_phone', true)->values();
        $invalidLeadPhones = $leadChanges->where('invalid_phone', true)->values();

        $this->outputSummary($userChanges, $leadChanges, $userEmailCollisions, $userPhoneCollisions, $invalidUserPhones, $invalidLeadPhones);

        if ($dryRun) {
            $this->comment('Dry run complete. No data was changed.');

            return self::SUCCESS;
        }

        if (
            $userEmailCollisions->isNotEmpty()
            || $userPhoneCollisions->isNotEmpty()
            || $invalidUserPhones->isNotEmpty()
            || $invalidLeadPhones->isNotEmpty()
        ) {
            $this->error('Normalization aborted. Resolve collisions or invalid phone values before applying changes.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($userChanges, $leadChanges): void {
            foreach ($userChanges as $change) {
                $user = User::find($change['id']);

                if (! $user) {
                    continue;
                }

                if (
                    $user->email === $change['normalized_email']
                    && $user->phone === $change['normalized_phone']
                ) {
                    continue;
                }

                $user->timestamps = false;
                $user->email = $change['normalized_email'];
                $user->phone = $change['normalized_phone'];
                $user->saveQuietly();
            }

            foreach ($leadChanges as $change) {
                $lead = RegistrationRequest::find($change['id']);

                if (! $lead) {
                    continue;
                }

                if (
                    $lead->contact_email === $change['normalized_email']
                    && $lead->contact_phone === $change['normalized_phone']
                ) {
                    continue;
                }

                $lead->timestamps = false;
                $lead->contact_email = $change['normalized_email'];
                $lead->contact_phone = $change['normalized_phone'];
                $lead->saveQuietly();
            }
        });

        $this->info('Normalization applied successfully.');

        return self::SUCCESS;
    }

    private function findCollisions(Collection $rows, string $column): Collection
    {
        return $rows
            ->filter(fn (array $row) => filled($row[$column]))
            ->groupBy($column)
            ->filter(fn (Collection $group) => $group->count() > 1)
            ->values();
    }

    private function outputSummary(
        Collection $userChanges,
        Collection $leadChanges,
        Collection $userEmailCollisions,
        Collection $userPhoneCollisions,
        Collection $invalidUserPhones,
        Collection $invalidLeadPhones,
    ): void {
        $changedUsers = $userChanges->filter(
            fn (array $row) => $row['original_email'] !== $row['normalized_email']
                || $row['original_phone'] !== $row['normalized_phone']
        );

        $changedLeads = $leadChanges->filter(
            fn (array $row) => $row['original_email'] !== $row['normalized_email']
                || $row['original_phone'] !== $row['normalized_phone']
        );

        $this->info(sprintf(
            'Users to update: %d | Leads to update: %d | User email collisions: %d | User phone collisions: %d | Invalid user phones: %d | Invalid lead phones: %d',
            $changedUsers->count(),
            $changedLeads->count(),
            $userEmailCollisions->count(),
            $userPhoneCollisions->count(),
            $invalidUserPhones->count(),
            $invalidLeadPhones->count(),
        ));

        $changedUsers->each(function (array $row): void {
            $this->line(sprintf(
                'User #%d email: %s -> %s | phone: %s -> %s',
                $row['id'],
                $row['original_email'],
                $row['normalized_email'] ?? 'null',
                $row['original_phone'] ?? 'null',
                $row['normalized_phone'] ?? 'null',
            ));
        });

        $changedLeads->each(function (array $row): void {
            $this->line(sprintf(
                'Lead #%d email: %s -> %s | phone: %s -> %s',
                $row['id'],
                $row['original_email'],
                $row['normalized_email'] ?? 'null',
                $row['original_phone'] ?? 'null',
                $row['normalized_phone'] ?? 'null',
            ));
        });

        $userEmailCollisions->each(function (Collection $group): void {
            $this->warn(sprintf(
                'User email collision on %s for IDs: %s',
                $group->first()['normalized_email'],
                $group->pluck('id')->implode(', '),
            ));
        });

        $userPhoneCollisions->each(function (Collection $group): void {
            $this->warn(sprintf(
                'User phone collision on %s for IDs: %s',
                $group->first()['normalized_phone'],
                $group->pluck('id')->implode(', '),
            ));
        });

        $invalidUserPhones->each(function (array $row): void {
            $this->warn(sprintf(
                'Invalid user phone on user #%d: %s',
                $row['id'],
                $row['original_phone'],
            ));
        });

        $invalidLeadPhones->each(function (array $row): void {
            $this->warn(sprintf(
                'Invalid lead phone on registration request #%d: %s',
                $row['id'],
                $row['original_phone'],
            ));
        });
    }
}
