<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_users' => User::count(),
                'total_organizations' => Organization::count(),
                'pending_leads' => RegistrationRequest::where('status', 'pending')->count(),
                'total_leads' => RegistrationRequest::count(),
                'total_bins' => DB::table('bins')->count(),
                'total_outlets' => DB::table('outlets')->count(),
            ],
        ]);
    }
}
