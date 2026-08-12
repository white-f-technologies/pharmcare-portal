<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Manually run seeder
$seeder = new Database\Seeders\DatabaseSeeder;
$seeder->run();

echo "Database seeded successfully!\n";
