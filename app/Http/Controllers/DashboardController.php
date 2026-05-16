<?php

namespace App\Http\Controllers;

use App\Models\GelanggangConfig;
use App\Services\Pm2Service;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected Pm2Service $pm2Service
    ) {}

    /**
     * Show the main dashboard with all gelanggang cards.
     */
    public function index()
    {
        $configs = GelanggangConfig::all();
        $statuses = $this->pm2Service->getAllStatuses($configs);

        return Inertia::render('Dashboard', [
            'gelanggangs' => $configs,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Polling endpoint: return ultra-lightweight JSON payload
     * of PM2 process statuses and resources.
     */
    public function status(): JsonResponse
    {
        $configs = GelanggangConfig::all();
        $statuses = $this->pm2Service->getAllStatuses($configs);

        return response()->json([
            'statuses' => $statuses,
        ]);
    }
}
