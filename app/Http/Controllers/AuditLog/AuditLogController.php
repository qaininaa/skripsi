<?php

namespace App\Http\Controllers\AuditLog;

use App\Http\Controllers\Controller;
use Domain\AuditLog\Services\AuditLogService;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(private AuditLogService $auditLogService) {}

    public function index(): View
    {
        $logs = $this->auditLogService->paginate(20);

        return view('pages.audit-logs.index', compact('logs'));
    }
}
