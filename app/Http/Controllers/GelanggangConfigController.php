<?php

namespace App\Http\Controllers;

use App\Models\GelanggangConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GelanggangConfigController extends Controller
{
    /**
     * Store a new gelanggang configuration.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'target_path' => 'required|string|max:500',
            'serve_host'  => 'required|ip',
            'serve_port'  => 'required|integer|min:1024|max:65535',
            'reverb_port' => 'required|integer|min:1024|max:65535',
        ]);

        // Ensure target_path exists
        if (!is_dir($validated['target_path'])) {
            return response()->json([
                'message' => 'Target path directory does not exist.',
                'errors' => ['target_path' => ['The specified directory does not exist.']],
            ], 422);
        }

        $config = GelanggangConfig::create($validated);

        return response()->json([
            'message' => 'Gelanggang created successfully.',
            'gelanggang' => $config,
        ], 201);
    }

    /**
     * Update an existing gelanggang configuration.
     */
    public function update(Request $request, GelanggangConfig $gelanggangConfig): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:100',
            'target_path' => 'sometimes|required|string|max:500',
            'serve_host'  => 'sometimes|required|ip',
            'serve_port'  => 'sometimes|required|integer|min:1024|max:65535',
            'reverb_port' => 'sometimes|required|integer|min:1024|max:65535',
        ]);

        if (isset($validated['target_path']) && !is_dir($validated['target_path'])) {
            return response()->json([
                'message' => 'Target path directory does not exist.',
                'errors' => ['target_path' => ['The specified directory does not exist.']],
            ], 422);
        }

        $gelanggangConfig->update($validated);

        return response()->json([
            'message' => 'Gelanggang updated successfully.',
            'gelanggang' => $gelanggangConfig->fresh(),
        ]);
    }

    /**
     * Delete a gelanggang configuration.
     */
    public function destroy(GelanggangConfig $gelanggangConfig): JsonResponse
    {
        $gelanggangConfig->delete();

        return response()->json([
            'message' => 'Gelanggang deleted successfully.',
        ]);
    }
}
