<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Run the migrations
$status = $kernel->call('migrate', [
    '--force' => true,
]);

echo ($status === 0) ? "Migrations completed successfully!\n" : "Migration failed.\n";

$kernel->terminate(null, $status);
exit($status);
