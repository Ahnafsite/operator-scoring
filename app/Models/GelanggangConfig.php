<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GelanggangConfig extends Model
{
    protected $fillable = [
        'name',
        'target_path',
        'serve_host',
        'serve_port',
        'reverb_port',
    ];

    protected $casts = [
        'serve_port' => 'integer',
        'reverb_port' => 'integer',
    ];

    /**
     * Get the PM2 process name prefix for this gelanggang.
     */
    public function pm2Prefix(): string
    {
        return 'arena-' . $this->id;
    }

    /**
     * Get the PM2 process names for all services.
     */
    public function pm2ProcessNames(): array
    {
        $prefix = $this->pm2Prefix();
        return [
            'serve'  => "{$prefix}-serve",
            'reverb' => "{$prefix}-reverb",
            'queue'  => "{$prefix}-queue",
        ];
    }
}
