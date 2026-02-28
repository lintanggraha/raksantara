<?php

namespace App\Http\Controllers;

use App\Services\AuditDashboardDataService;
use Illuminate\Http\Request;

class AuditDashboardController extends Controller
{
    private AuditDashboardDataService $dashboardDataService;

    public function __construct(AuditDashboardDataService $dashboardDataService)
    {
        $this->dashboardDataService = $dashboardDataService;
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'bank' => 'nullable|string|max:64',
        ]);

        $importPreviewRows = $request->session()->get('audit_dashboard.import_preview_rows');
        $useImportPreview = $request->query('source') === 'import-preview' && is_array($importPreviewRows);

        $auditDashboard = $useImportPreview
            ? $this->dashboardDataService->buildFromImportedRows($importPreviewRows, $validated)
            : $this->dashboardDataService->build($validated);

        return view('audit.placeholder', compact('auditDashboard'));
    }
}
