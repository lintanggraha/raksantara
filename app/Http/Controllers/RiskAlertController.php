<?php

namespace App\Http\Controllers;

use App\RiskAlert;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class RiskAlertController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:open,resolved,all',
            'type' => 'nullable|string|max:64',
        ]);

        $status = (string) $request->input('status', 'open');
        $type = trim((string) $request->input('type', ''));

        $query = RiskAlert::with(['risk', 'treatment'])
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($type !== '') {
            $query->where('alert_type', $type);
        }

        $alerts = $query->paginate(30)->appends([
            'status' => $status,
            'type' => $type,
        ]);

        $types = RiskAlert::query()->select('alert_type')->distinct()->orderBy('alert_type')->pluck('alert_type');

        return view('alerts.index', compact('alerts', 'types', 'status', 'type'));
    }

    public function resolve(RiskAlert $alert)
    {
        $alert->update([
            'status' => RiskAlert::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);

        AuditLogger::logModelEvent(
            'risk.alert.resolved',
            $alert,
            null,
            $alert->toArray()
        );

        return redirect()->back()->with('success', 'Alert marked as resolved.');
    }
}

