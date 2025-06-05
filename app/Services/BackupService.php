<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class BackupService
{
    public function downloadBackup()
    {
        try {
            $fileName = 'backup_' . now()->format('Y_m_d_H_i_s') . '.sql';
            $filePath = storage_path('app/' . $fileName);

            $db = config('database.connections.mysql');
            $command = sprintf(
                'mysqldump -u%s --password=%s %s > %s',
                escapeshellarg($db['username']),
                escapeshellarg($db['password']),
                escapeshellarg($db['database']),
                escapeshellarg($filePath)
            );

            $process = Process::fromShellCommandline($command);
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error(' فشل عملية النسخ الاحتياطي: ' . $process->getErrorOutput());
                return false;
            }

            if (file_exists($filePath)) {
                Log::info(' تم إنشاء نسخة احتياطية بنجاح: ' . $filePath);
                return $filePath;
            }

            Log::error(' لم يتم العثور على ملف النسخة الاحتياطية بعد تنفيذ العملية.');
            return false;
        } catch (Exception $e) {
            Log::error(' خطأ أثناء إنشاء النسخة الاحتياطية: ' . $e->getMessage());
            return false;
        }
    }
}
