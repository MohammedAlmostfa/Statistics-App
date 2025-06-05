<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index()
    {
        $backupPath = $this->backupService->downloadBackup();

        if ($backupPath) {
            return response()->download($backupPath)->deleteFileAfterSend(true);
        }

        return response()->json(['error' => 'فشل إنشاء النسخة الاحتياطية'], 500);
    }
}
