<?php

namespace App\Http\Controllers;

use App\Domains\AuditLog\Models\AuditLog;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        $logs = AuditLog::with('user')
            ->latest()
            ->paginate(20);

        return view('pages.audit-logs.index', compact('logs'));
    }
}
