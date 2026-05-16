<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileSystemController extends Controller
{
    /**
     * List directories at a given path.
     * Returns the current path, parent path, and child directories.
     */
    public function listDirectories(Request $request): JsonResponse
    {
        $path = $request->query('path', '/');

        // Resolve to absolute path
        $realPath = realpath($path);

        if (!$realPath || !is_dir($realPath)) {
            return response()->json([
                'error' => 'Invalid directory path.',
            ], 400);
        }

        // Ensure readable
        if (!is_readable($realPath)) {
            return response()->json([
                'error' => 'Directory is not readable.',
            ], 403);
        }

        $directories = [];

        try {
            $entries = scandir($realPath);

            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') continue;
                if (str_starts_with($entry, '.')) continue; // Skip hidden dirs

                $fullPath = $realPath . DIRECTORY_SEPARATOR . $entry;

                if (is_dir($fullPath)) {
                    $directories[] = [
                        'name' => $entry,
                        'path' => $fullPath,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silently handle permission errors on individual entries
        }

        // Sort alphabetically
        usort($directories, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        // Build breadcrumb parts
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $realPath));
        $breadcrumbs = [];
        $accumulated = '';
        foreach ($parts as $part) {
            $accumulated .= DIRECTORY_SEPARATOR . $part;
            $breadcrumbs[] = [
                'name' => $part,
                'path' => $accumulated,
            ];
        }

        return response()->json([
            'current' => $realPath,
            'parent'  => dirname($realPath),
            'breadcrumbs' => $breadcrumbs,
            'directories' => $directories,
        ]);
    }
}
