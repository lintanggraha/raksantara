<?php

namespace App\Http\Controllers;

use App\ComplianceWorkProgram;
use App\Support\InputValidation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplianceProgramController extends Controller
{
    public function indexInternalAudit()
    {
        return $this->renderIndex(ComplianceWorkProgram::SECTION_INTERNAL_AUDIT);
    }

    public function createInternalAudit()
    {
        return $this->renderForm(ComplianceWorkProgram::SECTION_INTERNAL_AUDIT);
    }

    public function storeInternalAudit(Request $request)
    {
        return $this->storeProgram($request, ComplianceWorkProgram::SECTION_INTERNAL_AUDIT);
    }

    public function editInternalAudit(ComplianceWorkProgram $program)
    {
        return $this->renderForm(ComplianceWorkProgram::SECTION_INTERNAL_AUDIT, $program);
    }

    public function updateInternalAudit(Request $request, ComplianceWorkProgram $program)
    {
        return $this->updateProgram($request, ComplianceWorkProgram::SECTION_INTERNAL_AUDIT, $program);
    }

    public function destroyInternalAudit(ComplianceWorkProgram $program)
    {
        return $this->destroyProgram(ComplianceWorkProgram::SECTION_INTERNAL_AUDIT, $program);
    }

    public function indexRiskManagement()
    {
        return $this->renderIndex(ComplianceWorkProgram::SECTION_RISK_MANAGEMENT);
    }

    public function createRiskManagement()
    {
        return $this->renderForm(ComplianceWorkProgram::SECTION_RISK_MANAGEMENT);
    }

    public function storeRiskManagement(Request $request)
    {
        return $this->storeProgram($request, ComplianceWorkProgram::SECTION_RISK_MANAGEMENT);
    }

    public function editRiskManagement(ComplianceWorkProgram $program)
    {
        return $this->renderForm(ComplianceWorkProgram::SECTION_RISK_MANAGEMENT, $program);
    }

    public function updateRiskManagement(Request $request, ComplianceWorkProgram $program)
    {
        return $this->updateProgram($request, ComplianceWorkProgram::SECTION_RISK_MANAGEMENT, $program);
    }

    public function destroyRiskManagement(ComplianceWorkProgram $program)
    {
        return $this->destroyProgram(ComplianceWorkProgram::SECTION_RISK_MANAGEMENT, $program);
    }

    public function indexCompliance()
    {
        return $this->renderIndex(ComplianceWorkProgram::SECTION_GOVERNANCE_COMPLIANCE);
    }

    public function createCompliance()
    {
        return $this->renderForm(ComplianceWorkProgram::SECTION_GOVERNANCE_COMPLIANCE);
    }

    public function storeCompliance(Request $request)
    {
        return $this->storeProgram($request, ComplianceWorkProgram::SECTION_GOVERNANCE_COMPLIANCE);
    }

    public function editCompliance(ComplianceWorkProgram $program)
    {
        return $this->renderForm(ComplianceWorkProgram::SECTION_GOVERNANCE_COMPLIANCE, $program);
    }

    public function updateCompliance(Request $request, ComplianceWorkProgram $program)
    {
        return $this->updateProgram($request, ComplianceWorkProgram::SECTION_GOVERNANCE_COMPLIANCE, $program);
    }

    public function destroyCompliance(ComplianceWorkProgram $program)
    {
        return $this->destroyProgram(ComplianceWorkProgram::SECTION_GOVERNANCE_COMPLIANCE, $program);
    }

    private function renderIndex(string $section)
    {
        $sectionConfig = ComplianceWorkProgram::configForSection($section);

        $programs = ComplianceWorkProgram::query()
            ->where('section', $section)
            ->orderByDesc('created_at')
            ->get();

        return view('compliance.programs.index', compact('sectionConfig', 'programs'));
    }

    private function renderForm(string $section, ?ComplianceWorkProgram $program = null)
    {
        $sectionConfig = ComplianceWorkProgram::configForSection($section);
        $this->ensureProgramSectionMatches($section, $program);

        $nextWorkId = $program ? $program->work_id : ComplianceWorkProgram::nextWorkIdPreview($section);

        return view('compliance.programs.form', compact('sectionConfig', 'program', 'nextWorkId'));
    }

    private function storeProgram(Request $request, string $section)
    {
        $sectionConfig = ComplianceWorkProgram::configForSection($section);
        $validated = $request->validate($this->validationRules(), $this->validationMessages());
        $payload = $this->normalizedPayload($request, $validated);

        $program = null;
        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $program = ComplianceWorkProgram::create(array_merge($payload, [
                    'section' => $section,
                    'work_id' => ComplianceWorkProgram::nextWorkIdPreview($section),
                    'created_by' => Auth::id(),
                ]));
                break;
            } catch (QueryException $exception) {
                if (!$this->isDuplicateWorkIdException($exception) || $attempt === $maxAttempts) {
                    throw $exception;
                }
            }
        }

        return redirect()
            ->route($sectionConfig['route_base'])
            ->with('success', 'Data program kerja berhasil ditambahkan dengan ID ' . $program->work_id . '.');
    }

    private function updateProgram(Request $request, string $section, ComplianceWorkProgram $program)
    {
        $sectionConfig = ComplianceWorkProgram::configForSection($section);
        $this->ensureProgramSectionMatches($section, $program);

        $validated = $request->validate($this->validationRules(), $this->validationMessages());
        $payload = $this->normalizedPayload($request, $validated);

        $program->fill($payload);
        $program->save();

        return redirect()
            ->route($sectionConfig['route_base'])
            ->with('success', 'Data program kerja ' . $program->work_id . ' berhasil diperbarui.');
    }

    private function destroyProgram(string $section, ComplianceWorkProgram $program)
    {
        $sectionConfig = ComplianceWorkProgram::configForSection($section);
        $this->ensureProgramSectionMatches($section, $program);

        $workId = $program->work_id;
        $program->delete();

        return redirect()
            ->route($sectionConfig['route_base'])
            ->with('success', 'Data program kerja ' . $workId . ' berhasil dihapus.');
    }

    private function ensureProgramSectionMatches(string $expectedSection, ?ComplianceWorkProgram $program): void
    {
        if (is_null($program)) {
            return;
        }

        abort_unless($program->section === $expectedSection, 404);
    }

    private function normalizedPayload(Request $request, array $validated): array
    {
        return [
            // Keep existing DB columns, but map to the new business labels.
            'program_kerja' => trim((string) $validated['program_key_initiative']),
            'deskripsi' => trim((string) $validated['description_rationales']),
            'target_sasaran_kerja' => trim((string) $validated['deliverables']),
            'kategori' => 'Program Kerja',
            'q1' => false,
            'q2' => false,
            'q3' => false,
            'q4' => false,
            'alignment_champion_program' => trim((string) $validated['how_to_measure']),
            'alignment_risk_profile_2025' => trim((string) $validated['budget']),
            'support_needed' => $this->nullableTrim($validated['support_needed'] ?? null),
        ];
    }

    private function nullableTrim($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function isDuplicateWorkIdException(QueryException $exception): bool
    {
        $message = strtolower((string) $exception->getMessage());

        return strpos($message, 'duplicate') !== false && strpos($message, 'work_id') !== false;
    }

    private function validationRules(): array
    {
        $budgetRegex = '/^[0-9\.,!\s]+$/';

        return [
            'program_key_initiative' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'description_rationales' => ['required', 'string', 'max:3000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'deliverables' => ['required', 'string', 'max:3000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'how_to_measure' => ['required', 'string', 'max:3000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'budget' => ['required', 'string', 'max:255', 'regex:' . $budgetRegex],
            'support_needed' => ['nullable', 'string', 'max:3000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'program_key_initiative.regex' => InputValidation::safeTextMessage(),
            'description_rationales.regex' => InputValidation::safeTextMessage(),
            'deliverables.regex' => InputValidation::safeTextMessage(),
            'how_to_measure.regex' => InputValidation::safeTextMessage(),
            'budget.regex' => 'Kolom budget hanya boleh angka dan tanda baca (.,!).',
            'support_needed.regex' => InputValidation::safeTextMessage(),
        ];
    }
}
