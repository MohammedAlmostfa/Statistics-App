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
        set_time_limit(300);

        try {
            $fileName = 'backup_' . now()->format('Y_m_d_H_i_s') . '.sql';
            $filePath = storage_path('app/' . $fileName);

            $mysqldump = env('MYSQLDUMP_PATH', '/bin/mysqldump');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $database = env('DB_DATABASE');


            // تجهيز الأمر
            $command = "{$mysqldump} --default-character-set=utf8mb4 -u{$username} " . (!empty($password) ? "-p{$password} " : "") . "{$database} > \"{$filePath}\"";

            shell_exec($command);

            if (!file_exists($filePath)) {
                Log::error("لم يتم العثور على ملف النسخة الاحتياطية: $filePath");
                return response()->json(['message' => 'فشل إنشاء النسخة الاحتياطية.'], 500);
            }

            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (Exception $e) {
            Log::error("خطأ أثناء إنشاء النسخة الاحتياطية: " . $e->getMessage());
            return response()->json(['message' => 'خطأ أثناء إنشاء النسخة الاحتياطية.'], 500);
        }
    }
}
