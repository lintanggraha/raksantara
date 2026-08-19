<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppEntryController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\DashboardPreferenceController;
use App\Http\Controllers\ComplianceProgramController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\RiskAlertController;
use App\Http\Controllers\RiskStatusApprovalController;
use App\Http\Controllers\EvidenceAttachmentController;
use App\Http\Controllers\RiskCommentController;
use App\Http\Controllers\RiskImportController;
use App\Http\Controllers\ScheduledReportController;
use App\Http\Controllers\TrendAnalysisController;
use App\Http\Controllers\AuditDashboardController;
use App\Http\Controllers\OjkMitigationController;
use App\Http\Controllers\ImportTemplateController;
use App\Http\Controllers\IncidentManagementController;
use App\Http\Controllers\ThreatIntelligenceController;
use App\Http\Controllers\OwnerWorkloadController;
use App\Http\Controllers\RiskTreatmentTemplateController;
use App\Http\Controllers\RiskRcsaWorkspaceController;
use App\Http\Controllers\ParameterManagementController;
use App\Http\Controllers\FrameworkController;
use App\Http\Controllers\RiskGovernanceController;
use App\Http\Controllers\AccessControlController;
use App\Http\Controllers\IncidentLearningController;
use App\Http\Controllers\EnterpriseRiskProfileController;
use App\Http\Controllers\ThirdPartyRiskController;
use App\Http\Controllers\ControlFrameworkMappingController;
use App\Http\Controllers\StrategicRiskReviewController;
use App\Http\Controllers\TwoFactorAuthenticationController;
use App\Http\Controllers\OidcSingleSignOnController;
use App\Http\Controllers\OperationalResilienceController;
use App\Http\Controllers\DataRetentionController;
use App\Http\Controllers\OperationalResilienceEvidenceExportController;

Auth::routes([
    'register' => config('auth.allow_public_registration', false),
]);

Route::get('/health', [HealthCheckController::class, 'live'])->name('health.live');
Route::get('/health/ready', [HealthCheckController::class, 'ready'])->name('health.ready');

Route::get('/', [AppEntryController::class, 'redirectToMonitoring'])->middleware(['auth', 'mfa.completed']);

Route::get('/home', [AppEntryController::class, 'redirectToMonitoring'])->middleware(['auth', 'mfa.completed']);

Route::get('/dashboard', [AppEntryController::class, 'redirectToMonitoring'])->middleware(['auth', 'mfa.completed']);

Route::get('/sso/oidc/redirect', [OidcSingleSignOnController::class, 'redirect'])->name('sso.oidc.redirect');
Route::get('/sso/oidc/callback', [OidcSingleSignOnController::class, 'callback'])->name('sso.oidc.callback');

Route::get('/two-factor-challenge', [TwoFactorAuthenticationController::class, 'challenge'])->name('two-factor.challenge');
Route::post('/two-factor-challenge', [TwoFactorAuthenticationController::class, 'verifyChallenge'])
    ->middleware('throttle:10,1')
    ->name('two-factor.challenge.verify');

Route::middleware(['auth', 'password.changed', 'mfa.completed'])->group(function () {
    Route::get('/account/pending-approval', [AppEntryController::class, 'pendingApproval'])->name('account.pending');

    Route::get('/two-factor', [TwoFactorAuthenticationController::class, 'show'])->name('two-factor.settings');
    Route::post('/two-factor/setup', [TwoFactorAuthenticationController::class, 'beginSetup'])->name('two-factor.setup.begin');
    Route::post('/two-factor/confirm', [TwoFactorAuthenticationController::class, 'confirmSetup'])
        ->middleware('throttle:10,1')
        ->name('two-factor.setup.confirm');
    Route::post('/two-factor/recovery-codes', [TwoFactorAuthenticationController::class, 'regenerateRecoveryCodes'])
        ->middleware('throttle:10,1')
        ->name('two-factor.recovery-codes.regenerate');
    Route::delete('/two-factor', [TwoFactorAuthenticationController::class, 'disable'])
        ->middleware('throttle:10,1')
        ->name('two-factor.disable');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/change-password', [ProfileController::class, 'showChangePassword'])->name('change.password');
    Route::post('/change-password', [ProfileController::class, 'changePassword'])
        ->middleware('throttle:10,1')
        ->name('profile.changePassword');

    Route::middleware('superadmin')->group(function () {
        Route::get('/parameters', [ParameterManagementController::class, 'index'])->name('parameters.index');
        Route::post('/parameters/risk-categories', [ParameterManagementController::class, 'storeRiskCategory'])->name('parameters.risk-categories.store');
        Route::put('/parameters/risk-categories/{riskCategory}', [ParameterManagementController::class, 'updateRiskCategory'])
            ->whereNumber('riskCategory')
            ->name('parameters.risk-categories.update');
        Route::post('/parameters/risk-owners', [ParameterManagementController::class, 'storeRiskOwner'])->name('parameters.risk-owners.store');
        Route::put('/parameters/risk-owners/{riskOwner}', [ParameterManagementController::class, 'updateRiskOwner'])
            ->whereNumber('riskOwner')
            ->name('parameters.risk-owners.update');
        Route::post('/parameters/cause-categories', [ParameterManagementController::class, 'storeRiskCauseCategory'])->name('parameters.cause-categories.store');
        Route::put('/parameters/cause-categories/{riskCauseCategory}', [ParameterManagementController::class, 'updateRiskCauseCategory'])
            ->whereNumber('riskCauseCategory')
            ->name('parameters.cause-categories.update');
        Route::post('/parameters/probability-scales', [ParameterManagementController::class, 'storeProbabilityScale'])->name('parameters.probability-scales.store');
        Route::put('/parameters/probability-scales/{probabilityScale}', [ParameterManagementController::class, 'updateProbabilityScale'])
            ->whereNumber('probabilityScale')
            ->name('parameters.probability-scales.update');
        Route::post('/parameters/impact-categories', [ParameterManagementController::class, 'storeImpactCategory'])->name('parameters.impact-categories.store');
        Route::put('/parameters/impact-categories/{impactCategory}', [ParameterManagementController::class, 'updateImpactCategory'])
            ->whereNumber('impactCategory')
            ->name('parameters.impact-categories.update');
        Route::post('/parameters/impact-scales', [ParameterManagementController::class, 'storeImpactScale'])->name('parameters.impact-scales.store');
        Route::put('/parameters/impact-scales/{impactScale}', [ParameterManagementController::class, 'updateImpactScale'])
            ->whereNumber('impactScale')
            ->name('parameters.impact-scales.update');
        Route::post('/parameters/radar-groups', [ParameterManagementController::class, 'storeRadarGroup'])->name('parameters.radar-groups.store');
        Route::put('/parameters/radar-groups/{radarGroup}', [ParameterManagementController::class, 'updateRadarGroup'])
            ->whereNumber('radarGroup')
            ->name('parameters.radar-groups.update');

        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/access-control', [AccessControlController::class, 'index'])->name('access-control.index');
        Route::post('/access-control', [AccessControlController::class, 'store'])->name('access-control.store');
        Route::delete('/access-control/{assignment}', [AccessControlController::class, 'destroy'])
            ->whereNumber('assignment')
            ->name('access-control.destroy');
        Route::put('/users/{user}/division', [UserManagementController::class, 'updateDivision'])
            ->whereNumber('user')
            ->name('users.updateDivision');
        Route::put('/users/{user}/superadmin', [UserManagementController::class, 'updateSuperadmin'])
            ->whereNumber('user')
            ->name('users.updateSuperadmin');
        Route::put('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])
            ->whereNumber('user')
            ->name('users.resetPassword');

        Route::get('/risk-status-approvals', [RiskStatusApprovalController::class, 'index'])
            ->name('risk-status-approvals.index');
        Route::put('/risk-status-approvals/{statusRequest}/approve', [RiskStatusApprovalController::class, 'approve'])
            ->whereNumber('statusRequest')
            ->name('risk-status-approvals.approve');
        Route::put('/risk-status-approvals/{statusRequest}/reject', [RiskStatusApprovalController::class, 'reject'])
            ->whereNumber('statusRequest')
            ->name('risk-status-approvals.reject');

        Route::get('/risk-alerts', [RiskAlertController::class, 'index'])->name('risk-alerts.index');
        Route::put('/risk-alerts/{alert}/resolve', [RiskAlertController::class, 'resolve'])
            ->whereNumber('alert')
            ->name('risk-alerts.resolve');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});

Route::middleware(['auth', 'password.changed', 'approved.user', 'mfa.completed'])->group(function () {
    Route::get('/dashboard-preferences', [DashboardPreferenceController::class, 'show'])
        ->name('dashboard-preferences.show');
    Route::put('/dashboard-preferences', [DashboardPreferenceController::class, 'update'])
        ->name('dashboard-preferences.update');

    Route::get('/impact-scales/{id}', [AppEntryController::class, 'impactScales'])->whereNumber('id');

    Route::get('/risk-monitoring', [RiskController::class, 'monitoring'])->name('risks.monitoring');
    Route::get('/enterprise-risk-profile', [EnterpriseRiskProfileController::class, 'index'])->name('enterprise-risk-profile.index');
    Route::get('/operational-resilience', [OperationalResilienceController::class, 'index'])->name('operational-resilience.index');
    Route::get('/operational-resilience/evidence-pack.xlsx', [OperationalResilienceEvidenceExportController::class, 'download'])
        ->middleware('compliance.division')
        ->name('operational-resilience.evidence-pack.download');
    Route::get('/data-retention', [DataRetentionController::class, 'index'])->name('data-retention.index');
    Route::post('/data-retention/schedules', [DataRetentionController::class, 'storeSchedule'])
        ->middleware('compliance.division')
        ->name('data-retention.schedules.store');
    Route::post('/data-retention/purge-requests', [DataRetentionController::class, 'storePurgeRequest'])
        ->middleware('compliance.division')
        ->name('data-retention.purge-requests.store');
    Route::put('/data-retention/purge-requests/{purgeRequest}/review', [DataRetentionController::class, 'reviewPurgeRequest'])
        ->middleware('superadmin')
        ->whereNumber('purgeRequest')
        ->name('data-retention.purge-requests.review');
    Route::put('/data-retention/purge-requests/{purgeRequest}/execute', [DataRetentionController::class, 'executePurgeRequest'])
        ->middleware('superadmin')
        ->whereNumber('purgeRequest')
        ->name('data-retention.purge-requests.execute');
    Route::post('/operational-resilience/backups', [OperationalResilienceController::class, 'storeBackup'])
        ->middleware('compliance.division')
        ->name('operational-resilience.backups.store');
    Route::post('/operational-resilience/backups/{backup}/restore-tests', [OperationalResilienceController::class, 'storeRestoreTest'])
        ->middleware('compliance.division')
        ->whereNumber('backup')
        ->name('operational-resilience.restore-tests.store');
    Route::put('/operational-resilience/restore-tests/{restoreTest}/verify', [OperationalResilienceController::class, 'verifyRestoreTest'])
        ->middleware('compliance.division')
        ->whereNumber('restoreTest')
        ->name('operational-resilience.restore-tests.verify');
    Route::post('/enterprise-risk-profile/decisions', [EnterpriseRiskProfileController::class, 'storeDecision'])
        ->middleware('compliance.division')
        ->name('enterprise-risk-profile.decisions.store');
    Route::put('/enterprise-risk-profile/decisions/{decision}', [EnterpriseRiskProfileController::class, 'updateDecision'])
        ->middleware('compliance.division')
        ->whereNumber('decision')
        ->name('enterprise-risk-profile.decisions.update');
    Route::get('/strategic-risk-reviews', [StrategicRiskReviewController::class, 'index'])->name('strategic-risk-reviews.index');
    Route::post('/strategic-risk-reviews/scenarios', [StrategicRiskReviewController::class, 'storeScenario'])
        ->middleware('compliance.division')
        ->name('strategic-risk-reviews.scenarios.store');
    Route::post('/strategic-risk-reviews/scenarios/{scenario}/impacts', [StrategicRiskReviewController::class, 'storeScenarioImpact'])
        ->middleware('compliance.division')
        ->whereNumber('scenario')
        ->name('strategic-risk-reviews.scenarios.impacts.store');
    Route::post('/strategic-risk-reviews/maturity', [StrategicRiskReviewController::class, 'storeMaturityAssessment'])
        ->middleware('compliance.division')
        ->name('strategic-risk-reviews.maturity.store');
    Route::get('/risk-heatmap', [RiskController::class, 'heatmap'])->name('risks.heatmap');
    Route::get('/risk-recap', [RiskController::class, 'recap'])->name('risks.recap');
    Route::get('/risk-dashboard-summary', [RiskController::class, 'dashboardSummary'])->name('risks.dashboard-summary');
    
    // Trend Analysis
    Route::get('/trend-analysis', [TrendAnalysisController::class, 'index'])->name('trends.analysis');
    Route::get('/api/trend-analysis/data', [TrendAnalysisController::class, 'data'])->name('trends.data');
    Route::get('/threat-intelligence', [ThreatIntelligenceController::class, 'index'])->name('threat-intelligence.index');
    
    // Framework Explorer
    Route::get('/frameworks/iso27001', [FrameworkController::class, 'iso27001'])->name('frameworks.iso27001');
    Route::get('/frameworks/nist-csf-2', [FrameworkController::class, 'nist'])->name('frameworks.nist');
    Route::get('/frameworks/seojk', [FrameworkController::class, 'seojk'])->name('frameworks.seojk');
    Route::redirect('/frameworks/cis', '/frameworks/panduan-resiliensi-ojk');
    Route::get('/frameworks/panduan-resiliensi-ojk', [FrameworkController::class, 'cis'])->name('frameworks.cis');
    Route::redirect('/frameworks/pci-dss', '/frameworks/pbi-02-2024')->name('frameworks.pci-dss');
    Route::get('/frameworks/pbi-02-2024', [FrameworkController::class, 'pbi'])->name('frameworks.pbi');
    Route::get('/incident-management', [IncidentManagementController::class, 'index'])->name('incidents.index');
    Route::post('/incident-management', [IncidentManagementController::class, 'store'])->name('incidents.store');
    Route::put('/incident-management/{incident}', [IncidentManagementController::class, 'update'])
        ->whereNumber('incident')
        ->name('incidents.update');
    Route::delete('/incident-management/{incident}', [IncidentManagementController::class, 'destroy'])
        ->whereNumber('incident')
        ->name('incidents.destroy');
    Route::get('/incident-management/{incident}/learning', [IncidentLearningController::class, 'show'])
        ->whereNumber('incident')
        ->name('incidents.learning.show');
    Route::post('/incident-management/{incident}/learning/actions', [IncidentLearningController::class, 'storeAction'])
        ->whereNumber('incident')
        ->name('incidents.learning.actions.store');
    Route::put('/incident-management/{incident}/learning/actions/{action}', [IncidentLearningController::class, 'updateAction'])
        ->whereNumber('incident')
        ->whereNumber('action')
        ->name('incidents.learning.actions.update');
    Route::put('/incident-management/{incident}/learning/review', [IncidentLearningController::class, 'completeReview'])
        ->whereNumber('incident')
        ->name('incidents.learning.review');
    Route::get('/audit', [AuditDashboardController::class, 'index'])
        ->middleware('compliance.division')
        ->name('audit.placeholder');
    Route::post('/audit/import', [AuditDashboardController::class, 'importSpreadsheet'])
        ->middleware('compliance.division')
        ->name('audit.import.store');
    Route::get('/import-templates/{template}', [ImportTemplateController::class, 'download'])
        ->where('template', 'risk-register|audit-dashboard|ojk-mitigation')
        ->middleware('compliance.division')
        ->name('import-templates.download');
    Route::get('/audit/export/{format}', [AuditDashboardController::class, 'export'])
        ->where('format', 'pdf|xlsx|xls|pptx')
        ->middleware('compliance.division')
        ->name('audit.export');
    
    Route::get('/reports/scheduled', [ScheduledReportController::class, 'index'])->name('reports.scheduled.index');
    Route::post('/reports/scheduled', [ScheduledReportController::class, 'store'])->name('reports.scheduled.store');
    Route::patch('/reports/scheduled/{scheduledReport}', [ScheduledReportController::class, 'update'])
        ->whereNumber('scheduledReport')
        ->name('reports.scheduled.update');
    Route::patch('/reports/scheduled/{scheduledReport}/toggle', [ScheduledReportController::class, 'toggle'])
        ->whereNumber('scheduledReport')
        ->name('reports.scheduled.toggle');
    Route::post('/reports/scheduled/{scheduledReport}/run-now', [ScheduledReportController::class, 'runNow'])
        ->whereNumber('scheduledReport')
        ->name('reports.scheduled.runNow');
    Route::delete('/reports/scheduled/{scheduledReport}', [ScheduledReportController::class, 'destroy'])
        ->whereNumber('scheduledReport')
        ->name('reports.scheduled.destroy');
    Route::view('/user-guide', 'how-to')->name('howto');
    Route::redirect('/how-to', '/user-guide');

    Route::middleware('compliance.division')->group(function () {
        Route::get('/ojk-mitigation', [OjkMitigationController::class, 'index'])->name('ojk-mitigation.index');
        Route::post('/ojk-mitigation/import', [OjkMitigationController::class, 'importSpreadsheet'])->name('ojk-mitigation.import');
        Route::get('/ojk-mitigation/{item}/edit', [OjkMitigationController::class, 'edit'])
            ->whereNumber('item')
            ->name('ojk-mitigation.edit');
        Route::put('/ojk-mitigation/{item}', [OjkMitigationController::class, 'update'])
            ->whereNumber('item')
            ->name('ojk-mitigation.update');
        Route::delete('/ojk-mitigation/{item}', [OjkMitigationController::class, 'destroy'])
            ->whereNumber('item')
            ->name('ojk-mitigation.destroy');
    });

    Route::middleware('compliance.division')->group(function () {
        Route::get('/compliance/internal-audit', [ComplianceProgramController::class, 'indexInternalAudit'])->name('compliance.internal-audit');
        Route::get('/compliance/internal-audit/create', [ComplianceProgramController::class, 'createInternalAudit'])->name('compliance.internal-audit.create');
        Route::post('/compliance/internal-audit', [ComplianceProgramController::class, 'storeInternalAudit'])->name('compliance.internal-audit.store');
        Route::get('/compliance/internal-audit/{program}/edit', [ComplianceProgramController::class, 'editInternalAudit'])->whereNumber('program')->name('compliance.internal-audit.edit');
        Route::put('/compliance/internal-audit/{program}', [ComplianceProgramController::class, 'updateInternalAudit'])->whereNumber('program')->name('compliance.internal-audit.update');
        Route::delete('/compliance/internal-audit/{program}', [ComplianceProgramController::class, 'destroyInternalAudit'])->whereNumber('program')->name('compliance.internal-audit.destroy');

        Route::get('/compliance/risk-management', [ComplianceProgramController::class, 'indexRiskManagement'])->name('compliance.risk-management');
        Route::get('/compliance/risk-management/create', [ComplianceProgramController::class, 'createRiskManagement'])->name('compliance.risk-management.create');
        Route::post('/compliance/risk-management', [ComplianceProgramController::class, 'storeRiskManagement'])->name('compliance.risk-management.store');
        Route::get('/compliance/risk-management/{program}/edit', [ComplianceProgramController::class, 'editRiskManagement'])->whereNumber('program')->name('compliance.risk-management.edit');
        Route::put('/compliance/risk-management/{program}', [ComplianceProgramController::class, 'updateRiskManagement'])->whereNumber('program')->name('compliance.risk-management.update');
        Route::delete('/compliance/risk-management/{program}', [ComplianceProgramController::class, 'destroyRiskManagement'])->whereNumber('program')->name('compliance.risk-management.destroy');

        Route::get('/compliance', [ComplianceProgramController::class, 'indexCompliance'])->name('compliance.compliance');
        Route::get('/compliance/create', [ComplianceProgramController::class, 'createCompliance'])->name('compliance.compliance.create');
        Route::post('/compliance', [ComplianceProgramController::class, 'storeCompliance'])->name('compliance.compliance.store');
        Route::get('/compliance/{program}/edit', [ComplianceProgramController::class, 'editCompliance'])->whereNumber('program')->name('compliance.compliance.edit');
        Route::put('/compliance/{program}', [ComplianceProgramController::class, 'updateCompliance'])->whereNumber('program')->name('compliance.compliance.update');
        Route::delete('/compliance/{program}', [ComplianceProgramController::class, 'destroyCompliance'])->whereNumber('program')->name('compliance.compliance.destroy');
    });

    Route::get('/reports/{scope}/{format}', [ReportController::class, 'export'])
        ->where('scope', 'monitoring|board|risks|heatmap|recap|overall')
        ->where('format', 'xls|xlsx|pdf|pptx')
        ->name('reports.export');

    Route::post('/risks/{risk}/attachments', [EvidenceAttachmentController::class, 'storeRiskAttachment'])
        ->whereNumber('risk')
        ->name('risks.attachments.store');
    Route::post('/risks/{risk}/treatments/{treatment}/attachments', [EvidenceAttachmentController::class, 'storeTreatmentAttachment'])
        ->whereNumber('risk')
        ->whereNumber('treatment')
        ->name('risks.treatments.attachments.store');
    Route::post('/risks/{risk}/comments/{comment}/attachments', [RiskCommentController::class, 'storeAttachment'])
        ->whereNumber('risk')
        ->whereNumber('comment')
        ->name('risks.comments.attachments.store');
    Route::get('/attachments/{attachment}/download', [EvidenceAttachmentController::class, 'download'])
        ->whereNumber('attachment')
        ->name('attachments.download');
    Route::delete('/attachments/{attachment}', [EvidenceAttachmentController::class, 'destroy'])
        ->whereNumber('attachment')
        ->name('attachments.destroy');

    Route::post('/risks/{risk}/comments', [RiskCommentController::class, 'store'])
        ->whereNumber('risk')
        ->name('risks.comments.store');
    Route::put('/risks/{risk}/comments/{comment}', [RiskCommentController::class, 'update'])
        ->whereNumber('risk')
        ->whereNumber('comment')
        ->name('risks.comments.update');
    Route::delete('/risks/{risk}/comments/{comment}', [RiskCommentController::class, 'destroy'])
        ->whereNumber('risk')
        ->whereNumber('comment')
        ->name('risks.comments.destroy');
    Route::post('/risks/import', [RiskImportController::class, 'store'])
        ->middleware('compliance.division')
        ->name('risks.import.store');

    // KRI Routes
    Route::get('/kris', [\App\Http\Controllers\KriController::class, 'index'])->name('kris.index');
    Route::post('/risks/{risk}/kris', [\App\Http\Controllers\KriController::class, 'store'])->whereNumber('risk')->name('kris.store');
    Route::put('/kris/{kri}', [\App\Http\Controllers\KriController::class, 'update'])->whereNumber('kri')->name('kris.update');
    Route::delete('/kris/{kri}', [\App\Http\Controllers\KriController::class, 'destroy'])->whereNumber('kri')->name('kris.destroy');
    Route::post('/kris/{kri}/log', [\App\Http\Controllers\KriController::class, 'logValue'])->whereNumber('kri')->name('kris.logValue');

    // Action Plan Kanban Routes
    Route::get('/mitigation-kanban', [\App\Http\Controllers\RiskTreatmentKanbanController::class, 'index'])->name('treatments.kanban');
    Route::patch('/mitigation-kanban/{treatment}/status', [\App\Http\Controllers\RiskTreatmentKanbanController::class, 'updateStatus'])->name('treatments.update-status');
    Route::get('/owner-workload', [OwnerWorkloadController::class, 'index'])->name('owners.workload');
    Route::get('/mitigation-playbooks', [RiskTreatmentTemplateController::class, 'index'])->name('treatment-templates.index');
    Route::post('/mitigation-playbooks', [RiskTreatmentTemplateController::class, 'store'])->name('treatment-templates.store');
    Route::put('/mitigation-playbooks/{template}', [RiskTreatmentTemplateController::class, 'update'])
        ->whereNumber('template')
        ->name('treatment-templates.update');
    Route::delete('/mitigation-playbooks/{template}', [RiskTreatmentTemplateController::class, 'destroy'])
        ->whereNumber('template')
        ->name('treatment-templates.destroy');
    Route::get('/risks/{risk}/rcsa', [RiskRcsaWorkspaceController::class, 'show'])
        ->whereNumber('risk')
        ->name('risks.rcsa.show');
    Route::put('/risks/{risk}/rcsa', [RiskRcsaWorkspaceController::class, 'update'])
        ->whereNumber('risk')
        ->name('risks.rcsa.update');

    Route::get('/risks/{risk}/governance', [RiskGovernanceController::class, 'show'])
        ->whereNumber('risk')
        ->name('risks.governance.show');
    Route::post('/risks/{risk}/governance/controls', [RiskGovernanceController::class, 'storeControl'])
        ->whereNumber('risk')
        ->name('risks.governance.controls.store');
    Route::post('/risks/{risk}/governance/controls/{control}/tests', [RiskGovernanceController::class, 'storeControlTest'])
        ->whereNumber('risk')
        ->whereNumber('control')
        ->name('risks.governance.controls.tests.store');
    Route::post('/risks/{risk}/governance/acceptances', [RiskGovernanceController::class, 'requestAcceptance'])
        ->whereNumber('risk')
        ->name('risks.governance.acceptances.store');
    Route::put('/risks/{risk}/governance/acceptances/{acceptance}/decision', [RiskGovernanceController::class, 'decideAcceptance'])
        ->whereNumber('risk')
        ->whereNumber('acceptance')
        ->name('risks.governance.acceptances.decision');

    // BCM Routes
    Route::get('/bcm', [\App\Http\Controllers\BcmController::class, 'index'])->name('bcm.index');
    Route::post('/risks/{risk}/bia', [\App\Http\Controllers\BiaController::class, 'update'])
        ->whereNumber('risk')
        ->name('bia.store');
    Route::post('/bias/{bia}/bcp', [\App\Http\Controllers\BcpController::class, 'update'])
        ->whereNumber('bia')
        ->name('bcp.store');

    Route::get('/control-framework-mappings', [ControlFrameworkMappingController::class, 'index'])->name('control-framework-mappings.index');
    Route::post('/control-framework-mappings', [ControlFrameworkMappingController::class, 'store'])
        ->middleware('compliance.division')
        ->name('control-framework-mappings.store');
    Route::get('/vendors/{vendor}/third-party-risk', [ThirdPartyRiskController::class, 'show'])
        ->whereNumber('vendor')
        ->name('vendors.third-party-risk.show');
    Route::post('/vendors/{vendor}/third-party-risk/assessments', [ThirdPartyRiskController::class, 'storeAssessment'])
        ->middleware('compliance.division')
        ->whereNumber('vendor')
        ->name('vendors.third-party-risk.assessments.store');
    Route::put('/vendors/{vendor}/third-party-risk/assessments/{assessment}', [ThirdPartyRiskController::class, 'updateAssessment'])
        ->middleware('compliance.division')
        ->whereNumber('vendor')
        ->whereNumber('assessment')
        ->name('vendors.third-party-risk.assessments.update');

    Route::resource('vendors', \App\Http\Controllers\VendorController::class);
    Route::resource('risk-appetites', \App\Http\Controllers\RiskAppetiteController::class);
    Route::resource('risks', RiskController::class);
});
