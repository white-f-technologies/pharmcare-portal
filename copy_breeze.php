<?php
$src = __DIR__ . '/vendor/laravel/breeze/stubs/default';
$dst = __DIR__;

$items = [
    // Routes
    'routes/auth.php' => 'routes/auth.php',
    // Controllers
    'app/Http/Controllers/ProfileController.php' => 'app/Http/Controllers/ProfileController.php',
    // Requests
    'app/Http/Requests/ProfileUpdateRequest.php' => 'app/Http/Requests/ProfileUpdateRequest.php',
    // View Components
    'app/View/Components/AppLayout.php' => 'app/View/Components/AppLayout.php',
    'app/View/Components/GuestLayout.php' => 'app/View/Components/GuestLayout.php',
];

foreach ($items as $from => $to) {
    $fromPath = "$src/$from";
    $toPath = "$dst/$to";
    $dir = dirname($toPath);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    copy($fromPath, $toPath);
    echo "Copied: $from\n";
}

// Auth controllers
$authControllers = scandir("$src/app/Http/Controllers/Auth");
foreach ($authControllers as $f) {
    if ($f === '.' || $f === '..') continue;
    $toDir = "$dst/app/Http/Controllers/Auth";
    if (!is_dir($toDir)) mkdir($toDir, 0755, true);
    copy("$src/app/Http/Controllers/Auth/$f", "$toDir/$f");
    echo "Copied: app/Http/Controllers/Auth/$f\n";
}

// Auth requests
$authRequests = scandir("$src/app/Http/Requests/Auth");
foreach ($authRequests as $f) {
    if ($f === '.' || $f === '..') continue;
    $toDir = "$dst/app/Http/Requests/Auth";
    if (!is_dir($toDir)) mkdir($toDir, 0755, true);
    copy("$src/app/Http/Requests/Auth/$f", "$toDir/$f");
    echo "Copied: app/Http/Requests/Auth/$f\n";
}

// Views - recursive copy
$views = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator("$src/resources/views", RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);
foreach ($views as $file) {
    $relative = substr($file->getPathname(), strlen("$src/resources/views/"));
    $target = "$dst/resources/views/$relative";
    if ($file->isDir()) {
        if (!is_dir($target)) mkdir($target, 0755, true);
    } else {
        copy($file->getPathname(), $target);
        echo "Copied: resources/views/$relative\n";
    }
}

// CSS
if (!is_dir("$dst/resources/css")) mkdir("$dst/resources/css", 0755, true);
copy("$src/resources/css/app.css", "$dst/resources/css/app.css");
echo "Copied: resources/css/app.css\n";

// JS
if (!is_dir("$dst/resources/js")) mkdir("$dst/resources/js", 0755, true);
copy("$src/resources/js/app.js", "$dst/resources/js/app.js");
echo "Copied: resources/js/app.js\n";

// Config files
copy("$src/tailwind.config.js", "$dst/tailwind.config.js");
echo "Copied: tailwind.config.js\n";
copy("$src/postcss.config.js", "$dst/postcss.config.js");
echo "Copied: postcss.config.js\n";
copy("$src/vite.config.js", "$dst/vite.config.js");
echo "Copied: vite.config.js\n";

echo "\nAll Breeze files copied successfully!\n";
