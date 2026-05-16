<?php

namespace App\Http\Controllers;

use App\Models\GelanggangConfig;
use App\Services\Pm2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Pm2Controller extends Controller
{
    public function __construct(
        protected Pm2Service $pm2Service
    ) {}

    /**
     * Start all services for a gelanggang.
     */
    public function startAll(GelanggangConfig $gelanggangConfig): JsonResponse
    {
        $success = $this->pm2Service->startAll($gelanggangConfig);

        return response()->json([
            'success' => $success,
            'message' => $success
                ? "All services started for {$gelanggangConfig->name}."
                : "Some services failed to start for {$gelanggangConfig->name}.",
        ]);
    }

    /**
     * Stop all services for a gelanggang.
     */
    public function stopAll(GelanggangConfig $gelanggangConfig): JsonResponse
    {
        $success = $this->pm2Service->stopAll($gelanggangConfig);

        return response()->json([
            'success' => $success,
            'message' => $success
                ? "All services stopped for {$gelanggangConfig->name}."
                : "Some services failed to stop for {$gelanggangConfig->name}.",
        ]);
    }

    /**
     * Toggle (start/stop) a single service for a gelanggang.
     */
    public function toggleProcess(Request $request, GelanggangConfig $gelanggangConfig): JsonResponse
    {
        $validated = $request->validate([
            'service' => 'required|in:serve,reverb,queue',
            'action'  => 'required|in:start,stop,restart',
        ]);

        $processNames = $gelanggangConfig->pm2ProcessNames();
        $pm2Name = $processNames[$validated['service']];

        $success = match ($validated['action']) {
            'start'   => $this->pm2Service->startSingleService($gelanggangConfig, $validated['service']),
            'stop'    => $this->pm2Service->stopProcess($pm2Name),
            'restart' => $this->pm2Service->restartProcess($pm2Name),
        };

        return response()->json([
            'success' => $success,
            'message' => $success
                ? ucfirst($validated['action']) . " {$validated['service']} successful."
                : "Failed to {$validated['action']} {$validated['service']}.",
        ]);
    }

    /**
     * Delete all PM2 processes for a gelanggang (cleanup).
     */
    public function deleteAll(GelanggangConfig $gelanggangConfig): JsonResponse
    {
        $success = $this->pm2Service->deleteAll($gelanggangConfig);

        return response()->json([
            'success' => $success,
            'message' => $success
                ? "All processes deleted for {$gelanggangConfig->name}."
                : "Some processes failed to delete for {$gelanggangConfig->name}.",
        ]);
    }
}
