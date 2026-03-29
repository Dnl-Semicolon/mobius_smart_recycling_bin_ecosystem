<?php

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('shows reports index page', function () {
    Report::factory()->count(3)->create();

    $this->get(route('admin.reports.index'))
        ->assertSuccessful()
        ->assertSeeText('Reports');
});

it('shows empty state when no reports', function () {
    $this->get(route('admin.reports.index'))
        ->assertSuccessful()
        ->assertSeeText('No reports yet');
});

it('filters reports by status', function () {
    Report::factory()->create(['status' => ReportStatus::Open]);
    Report::factory()->resolved()->create();

    $response = $this->get(route('admin.reports.index', ['status' => 'open']));

    $response->assertSuccessful()
        ->assertSeeText('Open');
});

it('filters reports by type', function () {
    Report::factory()->create(['type' => ReportType::DamagedBin]);
    Report::factory()->create(['type' => ReportType::Overflow]);

    $response = $this->get(route('admin.reports.index', ['type' => 'damaged_bin']));

    $response->assertSuccessful();
});

it('shows report detail page', function () {
    $report = Report::factory()->create(['description' => 'The lid is broken']);

    $this->get(route('admin.reports.show', $report))
        ->assertSuccessful()
        ->assertSeeText('The lid is broken')
        ->assertSeeText('Report #'.$report->id);
});

it('resolves a report', function () {
    $report = Report::factory()->create();

    $this->post(route('admin.reports.resolve', $report), [
        'resolution' => 'Bin has been repaired and is back in service.',
    ])->assertRedirect(route('admin.reports.show', $report));

    $report->refresh();
    expect($report->status)->toBe(ReportStatus::Resolved)
        ->and($report->resolved_by)->toBe($this->admin->id)
        ->and($report->resolution)->toBe('Bin has been repaired and is back in service.');
});

it('prevents resolving already resolved report', function () {
    $report = Report::factory()->resolved()->create();

    $this->post(route('admin.reports.resolve', $report), [
        'resolution' => 'Trying again.',
    ])->assertForbidden();
});

it('requires resolution text when resolving', function () {
    $report = Report::factory()->create();

    $this->post(route('admin.reports.resolve', $report), [
        'resolution' => '',
    ])->assertSessionHasErrors('resolution');
});

it('denies non-admin access to reports', function () {
    $user = User::factory()->create(); // public_user

    $this->actingAs($user)
        ->get(route('admin.reports.index'))
        ->assertForbidden();
});
