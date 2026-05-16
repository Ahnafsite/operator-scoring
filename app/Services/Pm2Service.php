<?php

namespace App\Services;

use App\Models\GelanggangConfig;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class Pm2Service
{
    /**
     * Get the full path to the pm2 executable.
     */
    protected function pm2Binary(): string
    {
        return config('pm2.binary', 'pm2');
    }

    /**
     * Run a PM2 command and return the output.
     */
    protected function runPm2(array $args, ?string $cwd = null, int $timeout = 30, array $customEnv = []): ?string
    {
        $command = array_merge([$this->pm2Binary()], $args);

        $env = array_merge($_SERVER, $_ENV);
        
        // Strip Laravel and web server specific environment variables
        // so they do not leak into the spawned PM2 processes.
        $blockedPrefixes = [
            'APP_', 'DB_', 'CACHE_', 'REDIS_', 'MAIL_', 'QUEUE_', 'SESSION_', 
            'BROADCAST_', 'FILESYSTEM_', 'MEMCACHED_', 'AWS_', 'VITE_', 'LOG_', 
            'BCRYPT_', 'PUSHER_', 'REVERB_', 'HTTP_', 'REQUEST_', 'SERVER_', 
            'REMOTE_', 'DOCUMENT_ROOT', 'SCRIPT_', 'PATH_INFO', 'PHP_SELF', 'CONTENT_'
        ];
        
        foreach ($env as $key => $value) {
            foreach ($blockedPrefixes as $prefix) {
                if (str_starts_with($key, $prefix)) {
                    $env[$key] = false; // Set to false so Symfony Process removes it from inherited env
                    break;
                }
            }
        }

        foreach ($customEnv as $key => $value) {
            $env[$key] = (string) $value;
        }

        if (empty($env['HOME'])) {
            $env['HOME'] = env('HOME', getenv('HOME') ?: '/Users/' . get_current_user());
        }
        if (empty($env['PATH'])) {
            $env['PATH'] = env('PATH', getenv('PATH') ?: '/usr/local/bin:/usr/bin:/bin');
        }

        $process = new Process($command, $cwd, $env);
        $process->setTimeout($timeout);

        try {
            $process->run();

            if (!$process->isSuccessful()) {
                Log::warning('PM2 command failed', [
                    'command' => implode(' ', $command),
                    'error' => $process->getErrorOutput(),
                    'exit_code' => $process->getExitCode(),
                ]);
                return null;
            }

            return $process->getOutput();
        } catch (\Exception $e) {
            Log::error('PM2 command exception', [
                'command' => implode(' ', $command),
                'exception' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get the status of all PM2 processes as an associative array.
     * Returns parsed JSON from `pm2 jlist`.
     */
    public function getAllProcesses(): array
    {
        $output = $this->runPm2(['jlist']);

        if ($output === null) {
            return [];
        }

        $decoded = json_decode($output, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get status info for a specific gelanggang's processes.
     * Returns lightweight data: status, cpu, memory for each service.
     */
    public function getGelanggangStatus(GelanggangConfig $config): array
    {
        $allProcesses = $this->getAllProcesses();
        $processNames = $config->pm2ProcessNames();

        $statuses = [];
        foreach ($processNames as $service => $pm2Name) {
            $found = collect($allProcesses)->firstWhere('name', $pm2Name);

            if ($found) {
                $statuses[$service] = [
                    'status'  => $found['pm2_env']['status'] ?? 'unknown',
                    'cpu'     => $found['monit']['cpu'] ?? 0,
                    'memory'  => $found['monit']['memory'] ?? 0,
                    'pm_id'   => $found['pm_id'] ?? null,
                    'uptime'  => $found['pm2_env']['pm_uptime'] ?? null,
                ];
            } else {
                $statuses[$service] = [
                    'status'  => 'stopped',
                    'cpu'     => 0,
                    'memory'  => 0,
                    'pm_id'   => null,
                    'uptime'  => null,
                ];
            }
        }

        return $statuses;
    }

    /**
     * Get lightweight status data for all gelanggangs at once.
     * Optimized: calls `pm2 jlist` only once.
     */
    public function getAllStatuses(iterable $configs): array
    {
        $allProcesses = $this->getAllProcesses();
        $result = [];

        foreach ($configs as $config) {
            $processNames = $config->pm2ProcessNames();
            $statuses = [];

            foreach ($processNames as $service => $pm2Name) {
                $found = collect($allProcesses)->firstWhere('name', $pm2Name);

                if ($found) {
                    $statuses[$service] = [
                        'status'  => $found['pm2_env']['status'] ?? 'unknown',
                        'cpu'     => $found['monit']['cpu'] ?? 0,
                        'memory'  => $found['monit']['memory'] ?? 0,
                        'pm_id'   => $found['pm_id'] ?? null,
                    ];
                } else {
                    $statuses[$service] = [
                        'status'  => 'stopped',
                        'cpu'     => 0,
                        'memory'  => 0,
                        'pm_id'   => null,
                    ];
                }
            }

            $result[$config->id] = $statuses;
        }

        return $result;
    }

    /**
     * Get the start commands for all services of a gelanggang.
     */
    public function getServiceCommands(GelanggangConfig $config): array
    {
        $processNames = $config->pm2ProcessNames();
        $path = $config->target_path;
        $host = $config->serve_host;
        $servePort = $config->serve_port;
        $reverbPort = $config->reverb_port;

        return [
            'serve' => [
                'name' => $processNames['serve'],
                'script' => 'php',
                'args' => ['artisan', 'serve', "--host={$host}", "--port={$servePort}"],
                'path' => $path,
                'env' => [
                    'APP_PORT' => $servePort,
                    'SERVER_PORT' => $servePort,
                ],
            ],
            'reverb' => [
                'name' => $processNames['reverb'],
                'script' => 'php',
                'args' => ['artisan', 'reverb:start', "--host={$host}", "--port={$reverbPort}"],
                'path' => $path,
                'env' => [
                    'REVERB_SERVER_PORT' => $reverbPort,
                    'REVERB_PORT' => $reverbPort,
                    'VITE_REVERB_PORT' => $reverbPort,
                ],
            ],
            'queue' => [
                'name' => $processNames['queue'],
                'script' => 'php',
                'args' => ['artisan', 'queue:work', '--sleep=3', '--tries=3'],
                'path' => $path,
                'env' => [],
            ],
        ];
    }

    /**
     * Sync the gelanggang configuration to its .env file.
     */
    public function syncEnvFile(GelanggangConfig $config): void
    {
        $envPath = rtrim($config->target_path, '/') . '/.env';
        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);
        
        $updates = [
            'APP_PORT' => $config->serve_port,
            'SERVER_PORT' => $config->serve_port,
            'REVERB_SERVER_PORT' => $config->reverb_port,
            'REVERB_PORT' => $config->reverb_port,
            'VITE_REVERB_PORT' => $config->reverb_port,
            'VITE_REVERB_HOST' => $config->serve_host === '0.0.0.0' ? '127.0.0.1' : $config->serve_host,
        ];

        foreach ($updates as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        $envContent = rtrim($envContent) . "\n";
        file_put_contents($envPath, $envContent);
    }

    /**
     * Start all 3 services for a gelanggang using PM2.
     */
    public function startAll(GelanggangConfig $config): bool
    {
        $this->syncEnvFile($config);
        
        $commands = $this->getServiceCommands($config);
        $allSuccess = true;

        foreach ($commands as $service => $cmd) {
            if (!$this->startSingleService($config, $service)) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    /**
     * Start a single service for a gelanggang.
     * If the service is not in PM2, it registers and starts it.
     */
    public function startSingleService(GelanggangConfig $config, string $serviceType): bool
    {
        $this->syncEnvFile($config);
        
        $commands = $this->getServiceCommands($config);
        if (!isset($commands[$serviceType])) {
            return false;
        }

        $cmd = $commands[$serviceType];
        $allProcesses = $this->getAllProcesses();
        $exists = collect($allProcesses)->contains('name', $cmd['name']);

        if ($exists) {
            return $this->restartProcess($cmd['name']);
        }

        $pm2Args = [
            'start', $cmd['script'],
            '--name', $cmd['name'],
            '--',
        ];
        $pm2Args = array_merge($pm2Args, $cmd['args']);

        $result = $this->runPm2($pm2Args, $cmd['path'], 30, $cmd['env'] ?? []);

        if ($result === null) {
            Log::error("Failed to start {$serviceType} for gelanggang {$config->id}");
            return false;
        }

        return true;
    }

    /**
     * Stop all 3 services for a gelanggang.
     */
    public function stopAll(GelanggangConfig $config): bool
    {
        $processNames = $config->pm2ProcessNames();
        $allSuccess = true;

        foreach ($processNames as $service => $pm2Name) {
            $result = $this->stopProcess($pm2Name);
            if (!$result) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    /**
     * Start a single process by name.
     */
    public function startProcess(string $processName): bool
    {
        $result = $this->runPm2(['start', $processName]);
        return $result !== null;
    }

    /**
     * Stop a single PM2 process by name.
     */
    public function stopProcess(string $processName): bool
    {
        $result = $this->runPm2(['stop', $processName]);
        return $result !== null;
    }

    /**
     * Delete a single PM2 process by name (removes from PM2 list).
     */
    public function deleteProcess(string $processName): bool
    {
        $result = $this->runPm2(['delete', $processName]);
        return $result !== null;
    }

    /**
     * Delete all processes for a gelanggang from PM2.
     */
    public function deleteAll(GelanggangConfig $config): bool
    {
        $processNames = $config->pm2ProcessNames();
        $allSuccess = true;

        foreach ($processNames as $pm2Name) {
            if (!$this->deleteProcess($pm2Name)) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    /**
     * Restart a single PM2 process.
     */
    public function restartProcess(string $processName): bool
    {
        $result = $this->runPm2(['restart', $processName]);
        return $result !== null;
    }
}
