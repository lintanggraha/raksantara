<?php

namespace App\Http\Controllers;

use App\Services\ImportTemplateSpreadsheetService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportTemplateController extends Controller
{
    public function download(Request $request, string $template, ImportTemplateSpreadsheetService $service): StreamedResponse
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');
        abort_if($user->isSuperadmin(), 403, 'Superadmin is read-only for imports.');
        abort_unless($user->isComplianceDivision(), 403, 'Import template hanya tersedia untuk divisi Compliance.');

        $payload = $service->build($template);
        $filename = $payload['filename'];
        $spreadsheet = $payload['spreadsheet'];

        $response = new StreamedResponse(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', $service->contentType());
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

        return $response;
    }
}
