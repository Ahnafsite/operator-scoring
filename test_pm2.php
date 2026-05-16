<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Symfony\Component\Process\Process;
$process = new Process(['pm2', 'jlist']);
$process->run();
echo $process->getErrorOutput();
echo $process->getOutput();
