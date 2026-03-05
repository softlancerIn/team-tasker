<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$task = \App\Models\Task::find(11);
echo "PRIORITY_VALUE:[" . $task->priority . "]\n";
echo "TYPE:" . gettype($task->priority) . "\n";
