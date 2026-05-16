<?php
require 'vendor/autoload.php';
use Symfony\Component\Process\Process;

$env = $_SERVER;
$env['TEST_VAR'] = 'hello';
$env['DB_CONNECTION'] = false;

$process = new Process(['php', '-r', 'var_dump(getenv("DB_CONNECTION"), getenv("TEST_VAR"));'], null, $env);
$process->run();
echo $process->getOutput();
