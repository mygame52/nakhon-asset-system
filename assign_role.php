<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$roleAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
$roleProc = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'procurement']);
$roleUser = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user']);

$users = \App\Models\User::all();
foreach ($users as $user) {
    if (!$user->hasAnyRole(['admin', 'procurement', 'user'])) {
        $user->assignRole('user');
    }
}

$first = \App\Models\User::first();
if ($first && !$first->hasRole('admin')) {
    $first->assignRole('admin');
}

echo "Roles synced successfully!\n";
