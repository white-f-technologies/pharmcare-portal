<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class BackupController extends Controller
{
    private string $backupDir = 'backups';

    public function index()
    {
        $files = Storage::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            if (Str::endsWith($file, '.sql') || Str::endsWith($file, '.zip')) {
                $backups[] = [
                    'name'       => basename($file),
                    'path'       => $file,
                    'type'       => Str::endsWith($file, '.zip') ? 'ZIP (DB + Media)' : 'SQL Dump',
                    'size'       => $this->formatBytes(Storage::size($file)),
                    'bytes'      => Storage::size($file),
                    'created_at' => date('Y-m-d H:i:s', Storage::lastModified($file)),
                    'has_checksum' => Storage::exists($file . '.sha256'),
                ];
            }
        }

        usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return view('backups.index', compact('backups'));
    }

    public function store(Request $request)
    {
        try {
            $includeMedia = $request->boolean('include_media', true);
            $customPath = $request->input('custom_export_path');

            $timestamp = date('Y-m-d_H-i-s');
            $sqlContent = $this->generateDatabaseDump();

            if ($includeMedia && class_exists('ZipArchive')) {
                $filename = 'pharmcare_backup_' . $timestamp . '.zip';
                $tempZip = tempnam(sys_get_temp_dir(), 'pharmcare_zip');

                $zip = new ZipArchive();
                if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    $zip->addFromString('database.sql', $sqlContent);

                    // Add storage/app/public directory
                    $publicStorage = storage_path('app/public');
                    if (file_exists($publicStorage)) {
                        $files = new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator($publicStorage),
                            \RecursiveIteratorIterator::LEAVES_ONLY
                        );

                        foreach ($files as $file) {
                            if (!$file->isDir()) {
                                $filePath = $file->getRealPath();
                                $relativePath = 'media/' . substr($filePath, strlen($publicStorage) + 1);
                                $zip->addFile($filePath, $relativePath);
                            }
                        }
                    }
                    $zip->close();
                }

                $zipData = file_get_contents($tempZip);
                @unlink($tempZip);

                Storage::put($this->backupDir . '/' . $filename, $zipData);

                // Generate SHA256 checksum
                $checksum = hash('sha256', $zipData);
                Storage::put($this->backupDir . '/' . $filename . '.sha256', $checksum . '  ' . $filename);

                // If custom path provided (e.g. USB drive or external location)
                if ($customPath && is_dir($customPath) && is_writable($customPath)) {
                    $exportPath = rtrim($customPath, '/\\') . DIRECTORY_SEPARATOR . $filename;
                    file_put_contents($exportPath, $zipData);
                    // Also write checksum to external path
                    file_put_contents($exportPath . '.sha256', $checksum . '  ' . $filename);
                }
            } else {
                $filename = 'pharmcare_backup_' . $timestamp . '.sql';
                Storage::put($this->backupDir . '/' . $filename, $sqlContent);

                // Generate SHA256 checksum
                $checksum = hash('sha256', $sqlContent);
                Storage::put($this->backupDir . '/' . $filename . '.sha256', $checksum . '  ' . $filename);

                if ($customPath && is_dir($customPath) && is_writable($customPath)) {
                    $exportPath = rtrim($customPath, '/\\') . DIRECTORY_SEPARATOR . $filename;
                    file_put_contents($exportPath, $sqlContent);
                    file_put_contents($exportPath . '.sha256', $checksum . '  ' . $filename);
                }
            }

            $this->logActivity('database_backup_created', "Backup {$filename} created successfully." . ($customPath ? " Also exported to: {$customPath}" : ''));

            $message = "Backup '{$filename}' created successfully.";
            if ($customPath && is_dir($customPath)) {
                $message .= " A copy has been saved to: {$customPath}";
            }

            return redirect()->route('backups.index')
                ->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()->route('backups.index')
                ->with('error', 'Failed to create backup: ' . $e->getMessage());
        }
    }

    public function download(string $filename)
    {
        $path = $this->backupDir . '/' . basename($filename);

        if (!Storage::exists($path)) {
            abort(404, 'Backup file not found.');
        }

        return Storage::download($path);
    }

    public function restore(string $filename)
    {
        $path = $this->backupDir . '/' . basename($filename);

        if (!Storage::exists($path)) {
            return redirect()->route('backups.index')->with('error', 'Backup file not found.');
        }

        try {
            // ── SAFETY: create a pre-restore backup first ──────────────
            $safetyFilename = $this->createSafetyBackup();

            // ── VERIFY: check SHA256 if checksum file exists ───────────
            $checksumPath = $path . '.sha256';
            if (Storage::exists($checksumPath)) {
                $storedLine = trim(Storage::get($checksumPath));
                $storedChecksum = explode('  ', $storedLine)[0] ?? '';
                $actualChecksum = hash('sha256', Storage::get($path));

                if ($storedChecksum !== $actualChecksum) {
                    return redirect()->route('backups.index')
                        ->with('error', "Backup integrity check FAILED for '{$filename}'. The file may be corrupted. A safety backup was saved as '{$safetyFilename}'.");
                }
            }

            // ── RESTORE ────────────────────────────────────────────────
            $fullPath = storage_path('app/' . $path);
            $this->performRestoreFromFile($fullPath);

            $this->logActivity('database_backup_restored', "System restored from {$filename}. Safety backup: {$safetyFilename}.");

            return redirect()->route('backups.index')
                ->with('success', "System successfully restored from '{$filename}'. A safety backup of the previous state was saved as '{$safetyFilename}'.");
        } catch (\Throwable $e) {
            return redirect()->route('backups.index')
                ->with('error', 'Failed to restore backup: ' . $e->getMessage());
        }
    }

    public function uploadRestore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:102400',
        ]);

        $file = $request->file('backup_file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, ['sql', 'zip'])) {
            return redirect()->route('backups.index')->with('error', 'Only .sql or .zip backup files are allowed.');
        }

        try {
            // Create safety backup before restoring
            $safetyFilename = $this->createSafetyBackup();

            $this->performRestoreFromFile($file->getRealPath());

            $filename = 'pharmcare_uploaded_' . date('Y-m-d_H-i-s') . '.' . $extension;
            Storage::putFileAs($this->backupDir, $file, $filename);

            $this->logActivity('database_backup_uploaded_restored', "System restored from uploaded backup. Safety backup: {$safetyFilename}.");

            return redirect()->route('backups.index')
                ->with('success', "System successfully restored from uploaded backup archive. Safety backup: '{$safetyFilename}'.");
        } catch (\Throwable $e) {
            return redirect()->route('backups.index')
                ->with('error', 'Failed to restore system from uploaded file: ' . $e->getMessage());
        }
    }

    public function destroy(string $filename)
    {
        $path = $this->backupDir . '/' . basename($filename);

        if (Storage::exists($path)) {
            Storage::delete($path);
            // Also delete checksum if exists
            if (Storage::exists($path . '.sha256')) {
                Storage::delete($path . '.sha256');
            }
            $this->logActivity('database_backup_deleted', "Backup {$filename} deleted.");
            return redirect()->route('backups.index')->with('success', "Backup file '{$filename}' deleted.");
        }

        return redirect()->route('backups.index')->with('error', 'Backup file not found.');
    }

    // ─── SAFETY BACKUP ────────────────────────────────────────────────

    /**
     * Create a safety backup of the current database before a destructive operation.
     * Returns the filename of the safety backup.
     */
    private function createSafetyBackup(): string
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = 'pharmcare_safety_prerestore_' . $timestamp . '.sql';
        $sqlContent = $this->generateDatabaseDump();

        Storage::put($this->backupDir . '/' . $filename, $sqlContent);

        $checksum = hash('sha256', $sqlContent);
        Storage::put($this->backupDir . '/' . $filename . '.sha256', $checksum . '  ' . $filename);

        return $filename;
    }

    // ─── RESTORE LOGIC ────────────────────────────────────────────────

    private function performRestoreFromFile(string $filePath): void
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === true) {
                $sqlContent = $zip->getFromName('database.sql');
                if (!$sqlContent) {
                    throw new \Exception("Zip archive does not contain 'database.sql'.");
                }
                $this->executeRestoreSql($sqlContent);

                // Restore media files securely (preventing Zip Slip)
                $publicStorage = storage_path('app/public');
                $realPublic = realpath($publicStorage);
                if (!$realPublic && !file_exists($publicStorage)) {
                    mkdir($publicStorage, 0755, true);
                    $realPublic = realpath($publicStorage);
                }

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entryName = $zip->getNameIndex($i);
                    if (str_starts_with($entryName, 'media/')) {
                        $relativeMediaName = ltrim(substr($entryName, 6), '/\\');
                        // Prevent directory traversal sequences
                        if (empty($relativeMediaName) || str_contains($relativeMediaName, '..') || str_contains($relativeMediaName, "\0")) {
                            continue;
                        }

                        $targetPath = $publicStorage . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativeMediaName);
                        $targetDir = dirname($targetPath);
                        if (!file_exists($targetDir)) {
                            mkdir($targetDir, 0755, true);
                        }

                        $realTargetDir = realpath($targetDir);
                        if ($realPublic && $realTargetDir && (str_starts_with($realTargetDir, $realPublic . DIRECTORY_SEPARATOR) || $realTargetDir === $realPublic)) {
                            file_put_contents($targetPath, $zip->getFromIndex($i));
                        }
                    }
                }
                $zip->close();
            } else {
                throw new \Exception("Could not open zip backup file.");
            }
        } else {
            $sqlContent = file_get_contents($filePath);
            $this->executeRestoreSql($sqlContent);
        }
    }

    // ─── DATABASE DUMP ────────────────────────────────────────────────

    private function generateDatabaseDump(): string
    {
        $driver = config('database.default');

        if ($driver === 'sqlite') {
            return $this->dumpSqlite();
        }

        return $this->dumpMysql();
    }

    private function dumpMysql(): string
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $keyName = 'Tables_in_' . $dbName;

        $output = "-- PharmCare Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Database: {$dbName}\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableObj) {
            $table = $tableObj->$keyName ?? current((array)$tableObj);

            $createTableStmt = DB::select("SHOW CREATE TABLE `{$table}`");
            $createTableSql = $createTableStmt[0]->{'Create Table'} ?? null;

            if ($createTableSql) {
                $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $output .= $createTableSql . ";\n\n";

                $rows = DB::table($table)->get();
                if ($rows->count() > 0) {
                    foreach ($rows as $row) {
                        $values = array_map(function ($value) {
                            if (is_null($value)) return 'NULL';
                            return DB::connection()->getPdo()->quote($value);
                        }, (array)$row);

                        $output .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $output .= "\n";
                }
            }
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $output;
    }

    private function dumpSqlite(): string
    {
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

        $output = "-- PharmCare SQLite Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- App Version: " . config('license.version', '2.2.0') . "\n\n";
        $output .= "PRAGMA foreign_keys = OFF;\n\n";

        foreach ($tables as $tableObj) {
            $table = $tableObj->name;
            $createStmt = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$table]);
            $createSql = $createStmt[0]->sql ?? null;

            if ($createSql) {
                $output .= "DROP TABLE IF EXISTS \"{$table}\";\n";
                $output .= $createSql . ";\n\n";

                $rows = DB::table($table)->get();
                if ($rows->count() > 0) {
                    foreach ($rows as $row) {
                        $values = array_map(function ($value) {
                            if (is_null($value)) return 'NULL';
                            return DB::connection()->getPdo()->quote($value);
                        }, (array)$row);

                        $output .= "INSERT INTO \"{$table}\" VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $output .= "\n";
                }
            }
        }

        $output .= "PRAGMA foreign_keys = ON;\n";

        return $output;
    }

    private function executeRestoreSql(string $sql): void
    {
        $driver = config('database.default');

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        DB::unprepared($sql);

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    // ─── HELPERS ──────────────────────────────────────────────────────

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    protected function logActivity(string $action, ?string $modelType = null, ?int $modelId = null, string $description = '', ?array $properties = null): void
    {
        try {
            ActivityLog::log($action, $description);
        } catch (\Throwable $e) {
            // Don't fail backup operations due to logging errors
        }
    }
}
