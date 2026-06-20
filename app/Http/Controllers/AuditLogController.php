<?php

namespace App\Http\Controllers;

use App\Domain\Audit\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = AuditLog::with(['hotel', 'actor'])->orderByDesc('created_at');

        foreach (['hotel_id', 'actor_id', 'action', 'entity_type', 'entity_id', 'request_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        $auditLogs = $query->paginate(25)->withQueryString();

        return $this->viewOrRedirect($request, 'domain.audit-logs.index', compact('auditLogs'));
    }

    public function show(Request $request, AuditLog $auditLog)
    {
        $this->authorize('view', $auditLog);

        return $this->viewOrRedirect($request, 'domain.audit-logs.show', compact('auditLog'));
    }
}
