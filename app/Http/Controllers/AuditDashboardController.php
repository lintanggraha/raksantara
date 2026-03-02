<?php

namespace App\Http\Controllers;

use App\Services\AuditDashboardDataService;
use App\Services\AuditDashboardSpreadsheetImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuditDashboardController extends Controller
{
    private AuditDashboardDataService $dashboardDataService;
    private AuditDashboardSpreadsheetImportService $spreadsheetImportService;

    public function __construct(
        AuditDashboardDataService $dashboardDataService,
        AuditDashboardSpreadsheetImportService $spreadsheetImportService
    )
    {
        $this->dashboardDataService = $dashboardDataService;
        $this->spreadsheetImportService = $spreadsheetImportService;
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'bank' => 'nullable|string|max:64',
        ]);

        $hasImported = (bool) $request->session()->get('audit_dashboard.has_imported', false);
        $importRows = $hasImported ? $request->session()->get('audit_dashboard.import_rows') : null;
        $useImportedRows = $hasImported && is_array($importRows) && !empty($importRows);

        $auditDashboard = $useImportedRows
            ? $this->dashboardDataService->buildFromImportedRows($importRows, $validated)
            : $this->dashboardDataService->buildFromImportedRows([], $validated);

        return view('audit.placeholder', compact('auditDashboard'));
    }

    public function importSpreadsheet(Request $request)
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');
        abort_if($user->isSuperadmin(), 403, 'Superadmin is read-only for risk dashboard.');
        abort_unless($user->isComplianceDivision(), 403, 'Import audit hanya tersedia untuk divisi Compliance.');

        $request->validate([
            'file' => 'required|file|max:20480|mimes:xls,xlsx',
        ]);

        $uploadedFile = $request->file('file');
        $storedPath = $uploadedFile->store('imports/audit-dashboard', 'local');
        $absolutePath = storage_path('app/' . $storedPath);

        try {
            $result = $this->spreadsheetImportService->parse($absolutePath);
            $importRows = (array) ($result['rows'] ?? []);
            $importMeta = is_array($result['meta'] ?? null) ? $result['meta'] : [];

            if (empty($importRows)) {
                return redirect()
                    ->route('audit.placeholder')
                    ->with('error', 'Tidak ada data valid yang bisa diimport dari file tersebut.');
            }

            $request->session()->put('audit_dashboard.import_rows', $importRows);
            $request->session()->put('audit_dashboard.has_imported', true);
            $request->session()->forget('audit_dashboard.import_preview_rows');
            $request->session()->forget('audit_dashboard.import_meta');
            $request->session()->forget('audit_dashboard.import_tab_analysis');

            return redirect()
                ->route('audit.placeholder')
                ->with('success', sprintf(
                    'Import audit berhasil. %d baris dimuat dari sheet %s dan langsung diterapkan ke dashboard.',
                    (int) ($importMeta['row_count'] ?? count($importRows)),
                    (string) ($importMeta['sheet_name'] ?? '-')
                ));
        } catch (\Throwable $e) {
            return redirect()
                ->route('audit.placeholder')
                ->with('error', 'Import audit gagal: ' . $e->getMessage());
        } finally {
            if (!empty($storedPath) && Storage::disk('local')->exists($storedPath)) {
                Storage::disk('local')->delete($storedPath);
            }
        }
    }
}
