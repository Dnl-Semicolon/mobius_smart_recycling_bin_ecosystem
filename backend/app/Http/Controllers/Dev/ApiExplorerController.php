<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ApiExplorerController extends Controller
{
    public function index(): View
    {
        $endpoints = [
            'Auth' => [
                ['method' => 'POST', 'path' => '/api/v1/auth/login', 'description' => 'Login and get Bearer token', 'auth' => false, 'body' => '{"email":"admin@mobius.test","password":"password","device_name":"API Explorer"}'],
                ['method' => 'POST', 'path' => '/api/v1/auth/logout', 'description' => 'Revoke current token', 'auth' => true, 'body' => ''],
                ['method' => 'GET', 'path' => '/api/v1/auth/user', 'description' => 'Get current user info', 'auth' => true, 'body' => ''],
            ],
            'Admin — Dashboard' => [
                ['method' => 'GET', 'path' => '/api/v1/dashboard', 'description' => 'Dashboard summary stats', 'auth' => true, 'body' => ''],
            ],
            'Admin — Outlets' => [
                ['method' => 'GET', 'path' => '/api/v1/outlets', 'description' => 'List all outlets', 'auth' => true, 'body' => ''],
                ['method' => 'GET', 'path' => '/api/v1/outlets/1', 'description' => 'Show outlet #1', 'auth' => true, 'body' => ''],
            ],
            'Admin — Bins' => [
                ['method' => 'GET', 'path' => '/api/v1/bins', 'description' => 'List all bins', 'auth' => true, 'body' => ''],
                ['method' => 'GET', 'path' => '/api/v1/bins/1', 'description' => 'Show bin #1', 'auth' => true, 'body' => ''],
            ],
            'Admin — Detections' => [
                ['method' => 'GET', 'path' => '/api/v1/detection-events', 'description' => 'List detection events', 'auth' => true, 'body' => ''],
                ['method' => 'GET', 'path' => '/api/v1/detection-events/1', 'description' => 'Show detection #1', 'auth' => true, 'body' => ''],
            ],
            'Admin — Pickup Management' => [
                ['method' => 'GET', 'path' => '/api/v1/admin/pickup-requests', 'description' => 'List all pickup requests', 'auth' => true, 'body' => ''],
                ['method' => 'GET', 'path' => '/api/v1/admin/pickup-requests/1', 'description' => 'Show pickup #1', 'auth' => true, 'body' => ''],
                ['method' => 'POST', 'path' => '/api/v1/admin/pickup-requests/1/cancel', 'description' => 'Cancel pickup #1', 'auth' => true, 'body' => ''],
                ['method' => 'POST', 'path' => '/api/v1/admin/pickup-requests/1/unclaim', 'description' => 'Unclaim pickup #1', 'auth' => true, 'body' => ''],
            ],
            'Admin — Users' => [
                ['method' => 'GET', 'path' => '/api/v1/admin/users', 'description' => 'List all users', 'auth' => true, 'body' => ''],
                ['method' => 'GET', 'path' => '/api/v1/admin/users/1', 'description' => 'Show user #1', 'auth' => true, 'body' => ''],
            ],
            'Collector' => [
                ['method' => 'GET', 'path' => '/api/v1/collector/pickups', 'description' => 'Pickup board (available/active/history)', 'auth' => true, 'body' => ''],
                ['method' => 'GET', 'path' => '/api/v1/collector/stats', 'description' => 'Personal performance stats', 'auth' => true, 'body' => ''],
                ['method' => 'POST', 'path' => '/api/v1/collector/pickups/1/claim', 'description' => 'Claim pickup #1', 'auth' => true, 'body' => ''],
                ['method' => 'POST', 'path' => '/api/v1/collector/pickups/1/complete', 'description' => 'Complete pickup #1', 'auth' => true, 'body' => ''],
            ],
            'Public' => [
                ['method' => 'GET', 'path' => '/api/v1/public/stats', 'description' => 'Community impact stats (no auth)', 'auth' => false, 'body' => ''],
            ],
            'Detection (Device)' => [
                ['method' => 'POST', 'path' => '/api/v1/detect', 'description' => 'Submit a detection event', 'auth' => false, 'body' => '{"bin_id":1,"waste_type":"paper_cup","confidence":95}'],
            ],
        ];

        return view('dev.api-explorer', compact('endpoints'));
    }
}
