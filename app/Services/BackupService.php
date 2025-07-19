<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ZipArchive;

class BackupService
{
    public function downloadBackup()
    {
        Log::info("Backup execution started");
        set_time_limit(300);

        try {
            $fileName = 'backup_' . now()->format('Y_m_d_H_i_s') . '.sql';
            $filePath = '/tmp/' . $fileName;

            $mysqldump = config('database.connections.mysql.mysqldump');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $database = config('database.connections.mysql.database');

            $command = "{$mysqldump} --default-character-set=utf8mb4 -u{$username} -p{$password} {$database} > \"{$filePath}\"";

            $output = shell_exec($command);

            Log::info("Backup command: $command");
            Log::info("Execution output: $output");

            if (!file_exists($filePath)) {
                Log::error("Failed to find backup file: $filePath");
                return response()->json(['message' => 'Failed to create the backup.'], 500);
            }

            return $filePath;
        } catch (Exception $e) {
            Log::error("Error during backup: " . $e->getMessage());
            return response()->json(['message' => 'Error during backup.'], 500);
        }
    }
}