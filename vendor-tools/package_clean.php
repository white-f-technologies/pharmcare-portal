<?php

$zipFile = __DIR__ . '/../dist/PharmCare_Standalone_v2.1.0_Client.zip';
$sourceDir = realpath(__DIR__ . '/..');

if (!file_exists(dirname($zipFile))) {
    mkdir(dirname($zipFile), 0755, true);
}

if (file_exists($zipFile)) {
    @unlink($zipFile);
}

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("Failed to create ZIP file.\n");
}

$excludePaths = [
    '.git',
    '.github',
    'node_modules',
    'dist',
    '.env',
    'database/database.sqlite',
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/keys/private.key',
    'vendor-tools/keys/private.key',
];

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$addedCount = 0;
foreach ($files as $name => $file) {
    if (!$file->isFile()) {
        continue;
    }

    $filePath = $file->getRealPath();
    $relativePath = substr($filePath, strlen($sourceDir) + 1);
    $relativePath = str_replace('\\', '/', $relativePath);

    $shouldExclude = false;
    foreach ($excludePaths as $ex) {
        if ($relativePath === $ex || str_starts_with($relativePath, $ex . '/')) {
            $shouldExclude = true;
            break;
        }
    }

    if ($shouldExclude) {
        continue;
    }

    $zip->addFile($filePath, $relativePath);
    $addedCount++;
}

$zip->close();

echo "Successfully created clean standalone distribution package!\n";
echo "Location: " . realpath($zipFile) . "\n";
echo "Total files archived: {$addedCount}\n";
