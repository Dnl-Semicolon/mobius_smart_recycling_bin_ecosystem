<?php

use App\Enums\PickupStatus;
use App\Enums\WasteType;
use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

/*
|--------------------------------------------------------------------------
| Bin Show Page (Monitoring Station)
|--------------------------------------------------------------------------
*/

it('shows bin monitoring station with hero and quick stats', function () {
    $outlet = Outlet::factory()->create();
    $bin = Bin::factory()->active()->create(['fill_level' => 72]);
    BinAssignment::factory()->current()->create(['bin_id' => $bin->id, 'outlet_id' => $outlet->id]);

    // Create events without waste_type to avoid observer changing fill_level
    DetectionEvent::factory()->count(5)->create([
        'bin_id' => $bin->id,
        'waste_type' => null,
        'detected_at' => now(),
    ]);

    $response = $this->get(route('admin.bins.show', $bin));

    $response->assertSuccessful()
        ->assertSeeText($bin->serial_number)
        ->assertSeeText($outlet->name)
        ->assertSeeText('72%')
        ->assertSeeText('Detections')
        ->assertSeeText('Recent Activity');
});

it('shows quick stats with correct counts', function () {
    $bin = Bin::factory()->active()->create();

    DetectionEvent::factory()->count(3)->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 90,
        'detected_at' => now(),
    ]);

    $response = $this->get(route('admin.bins.show', $bin));

    $response->assertSuccessful()
        ->assertSeeText('3'); // today's detections
});

it('shows bin with no detections gracefully', function () {
    $bin = Bin::factory()->active()->create();

    $response = $this->get(route('admin.bins.show', $bin));

    $response->assertSuccessful()
        ->assertSeeText('No detections yet')
        ->assertSeeText('No activity yet');
});

it('shows active pickup request banner in hero', function () {
    $bin = Bin::factory()->active()->create(['fill_level' => 85]);
    PickupRequest::factory()->create([
        'bin_id' => $bin->id,
        'status' => PickupStatus::Pending,
    ]);

    $response = $this->get(route('admin.bins.show', $bin));

    $response->assertSuccessful()
        ->assertSeeText('Pickup Pending');
});

/*
|--------------------------------------------------------------------------
| Bin Detections Sub-Page
|--------------------------------------------------------------------------
*/

it('shows paginated detections for a specific bin', function () {
    $bin = Bin::factory()->active()->create();
    DetectionEvent::factory()->count(20)->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PlasticCup,
    ]);

    $response = $this->get(route('admin.bins.detections', $bin));

    $response->assertSuccessful()
        ->assertSeeText($bin->serial_number)
        ->assertSeeText('Plastic Cup');
});

it('filters detections by waste type', function () {
    $bin = Bin::factory()->active()->create();
    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup,
    ]);
    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::Straw,
    ]);

    $response = $this->get(route('admin.bins.detections', [$bin, 'waste_type' => 'straw']));

    $response->assertSuccessful()
        ->assertSeeText('Straw');
});

it('filters detections by minimum confidence', function () {
    $bin = Bin::factory()->active()->create();
    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'confidence' => 95,
        'waste_type' => WasteType::Lid,
    ]);
    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'confidence' => 40,
        'waste_type' => WasteType::LiquidWaste,
    ]);

    $response = $this->get(route('admin.bins.detections', [$bin, 'min_confidence' => 80]));

    $response->assertSuccessful()
        ->assertSeeText('95%')
        ->assertDontSeeText('40%');
});

it('shows empty state when bin has no detections', function () {
    $bin = Bin::factory()->active()->create();

    $response = $this->get(route('admin.bins.detections', $bin));

    $response->assertSuccessful()
        ->assertSeeText('No detections found');
});

it('does not show detections from other bins', function () {
    $bin = Bin::factory()->active()->create();
    $otherBin = Bin::factory()->active()->create();

    DetectionEvent::factory()->create([
        'bin_id' => $otherBin->id,
        'waste_type' => WasteType::Straw,
    ]);

    $response = $this->get(route('admin.bins.detections', $bin));

    $response->assertSuccessful()
        ->assertSeeText('No detections found');
});

/*
|--------------------------------------------------------------------------
| Bin Pickups Sub-Page
|--------------------------------------------------------------------------
*/

it('shows pickup history for a specific bin', function () {
    $bin = Bin::factory()->active()->create();
    PickupRequest::factory()->count(3)->create([
        'bin_id' => $bin->id,
        'status' => PickupStatus::Completed,
        'completed_at' => now(),
    ]);

    $response = $this->get(route('admin.bins.pickups', $bin));

    $response->assertSuccessful()
        ->assertSeeText($bin->serial_number)
        ->assertSeeText('Completed')
        ->assertSeeText('3'); // total pickups stat
});

it('shows pickup stats correctly', function () {
    $bin = Bin::factory()->active()->create();
    PickupRequest::factory()->create([
        'bin_id' => $bin->id,
        'status' => PickupStatus::Completed,
        'completed_at' => now(),
    ]);
    PickupRequest::factory()->create([
        'bin_id' => $bin->id,
        'status' => PickupStatus::Pending,
    ]);

    $response = $this->get(route('admin.bins.pickups', $bin));

    $response->assertSuccessful()
        ->assertSeeText('Total Pickups')
        ->assertSeeText('2');
});

it('shows empty state when bin has no pickups', function () {
    $bin = Bin::factory()->active()->create();

    $response = $this->get(route('admin.bins.pickups', $bin));

    $response->assertSuccessful()
        ->assertSeeText('No pickup requests');
});

/*
|--------------------------------------------------------------------------
| Bin Analytics Sub-Page
|--------------------------------------------------------------------------
*/

it('shows analytics page with charts', function () {
    $bin = Bin::factory()->active()->create();
    DetectionEvent::factory()->count(10)->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 88,
    ]);

    $response = $this->get(route('admin.bins.analytics', $bin));

    $response->assertSuccessful()
        ->assertSeeText($bin->serial_number)
        ->assertSeeText('Waste Type Distribution')
        ->assertSeeText('Detections This Week')
        ->assertSeeText('Confidence Distribution')
        ->assertSeeText('Fill Level Trend')
        ->assertSeeText('10'); // total detections stat
});

it('shows analytics with no data gracefully', function () {
    $bin = Bin::factory()->active()->create();

    $response = $this->get(route('admin.bins.analytics', $bin));

    $response->assertSuccessful()
        ->assertSeeText('No detection data yet');
});

it('shows correct summary stats on analytics page', function () {
    $bin = Bin::factory()->active()->create();

    DetectionEvent::factory()->create(['bin_id' => $bin->id, 'waste_type' => WasteType::PaperCup]);
    DetectionEvent::factory()->create(['bin_id' => $bin->id, 'waste_type' => WasteType::Straw]);
    DetectionEvent::factory()->create(['bin_id' => $bin->id, 'waste_type' => WasteType::PaperCup]);

    PickupRequest::factory()->create([
        'bin_id' => $bin->id,
        'status' => PickupStatus::Completed,
        'completed_at' => now(),
    ]);

    $response = $this->get(route('admin.bins.analytics', $bin));

    $response->assertSuccessful()
        ->assertSeeText('3') // total detections
        ->assertSeeText('2'); // unique waste types
});

/*
|--------------------------------------------------------------------------
| Bin Index (Enhanced)
|--------------------------------------------------------------------------
*/

it('shows fleet summary stats on index page', function () {
    Bin::factory()->active()->count(3)->create(['fill_level' => 50]);
    Bin::factory()->active()->create(['fill_level' => 85]);
    Bin::factory()->inactive()->create();

    $response = $this->get(route('admin.bins.index'));

    $response->assertSuccessful()
        ->assertSeeText('Total Bins')
        ->assertSeeText('5')
        ->assertSeeText('Active')
        ->assertSeeText('4')
        ->assertSeeText('Need Pickup')
        ->assertSeeText('1');
});

it('sorts bins by fill level descending by default', function () {
    $lowBin = Bin::factory()->active()->create(['fill_level' => 10, 'serial_number' => 'LOW-001']);
    $highBin = Bin::factory()->active()->create(['fill_level' => 90, 'serial_number' => 'HIGH-001']);

    $response = $this->get(route('admin.bins.index'));

    $response->assertSuccessful()
        ->assertSeeInOrder(['HIGH-001', 'LOW-001']);
});

it('sorts bins by serial number when requested', function () {
    Bin::factory()->active()->create(['serial_number' => 'ZZZ-001', 'fill_level' => 90]);
    Bin::factory()->active()->create(['serial_number' => 'AAA-001', 'fill_level' => 10]);

    $response = $this->get(route('admin.bins.index', ['sort' => 'serial']));

    $response->assertSuccessful()
        ->assertSeeInOrder(['AAA-001', 'ZZZ-001']);
});

it('shows detection count on each bin card', function () {
    $bin = Bin::factory()->active()->create();
    DetectionEvent::factory()->count(7)->create(['bin_id' => $bin->id]);

    $response = $this->get(route('admin.bins.index'));

    $response->assertSuccessful()
        ->assertSeeText('7');
});

/*
|--------------------------------------------------------------------------
| Telemetry JSON Endpoint
|--------------------------------------------------------------------------
*/

it('returns telemetry JSON for a bin', function () {
    $bin = Bin::factory()->active()->create([
        'fill_level' => 45,
        'compartments' => [
            'solids' => ['fill_percent' => 30, 'volume_ml' => 1500, 'weight_g' => 200, 'max_volume_ml' => 5000, 'max_weight_g' => 2000],
            'liquid' => ['fill_percent' => 10, 'volume_ml' => 200, 'weight_g' => 200, 'max_volume_ml' => 2000, 'max_weight_g' => 2000],
        ],
        'last_seen_at' => now(),
        'ip_address' => '192.168.1.50:9001',
    ]);

    $response = $this->getJson(route('admin.bins.telemetry', $bin));

    $response->assertSuccessful()
        ->assertJson([
            'fill_level' => 45,
            'solids_fill' => 30,
            'solids_weight_g' => 200,
            'liquid_fill' => 10,
            'total_weight_g' => 400,
            'is_online' => true,
            'ip_address' => '192.168.1.50:9001',
        ]);
});

it('returns telemetry with null compartments gracefully', function () {
    $bin = Bin::factory()->active()->create(['compartments' => null]);

    $response = $this->getJson(route('admin.bins.telemetry', $bin));

    $response->assertSuccessful()
        ->assertJson([
            'solids_fill' => 0,
            'liquid_fill' => 0,
            'total_weight_g' => 0,
            'is_online' => false,
        ]);
});

it('returns fleet status JSON with online counts', function () {
    Bin::factory()->active()->create([
        'last_seen_at' => now(),
        'ip_address' => '192.168.1.50:9001',
    ]);
    Bin::factory()->active()->create([
        'last_seen_at' => now()->subMinutes(10),
    ]);
    Bin::factory()->active()->create();

    $response = $this->getJson(route('admin.bins.fleet-status'));

    $response->assertSuccessful()
        ->assertJsonPath('summary.total', 3)
        ->assertJsonPath('summary.online', 1);
});

it('requires admin role for telemetry endpoint', function () {
    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->active()->create();

    $this->actingAs($collector)
        ->getJson(route('admin.bins.telemetry', $bin))
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Authorization
|--------------------------------------------------------------------------
*/

it('requires admin role for bin detections page', function () {
    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->active()->create();

    $this->actingAs($collector)
        ->get(route('admin.bins.detections', $bin))
        ->assertForbidden();
});

it('requires admin role for bin analytics page', function () {
    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->active()->create();

    $this->actingAs($collector)
        ->get(route('admin.bins.analytics', $bin))
        ->assertForbidden();
});

it('requires admin role for bin pickups page', function () {
    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->active()->create();

    $this->actingAs($collector)
        ->get(route('admin.bins.pickups', $bin))
        ->assertForbidden();
});
