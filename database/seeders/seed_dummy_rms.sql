-- Risk Management System - Dummy Data Seed
-- Version: 1.0
-- Target: MySQL 8.0+
-- Scope: local/development only
--
-- This script is intentionally tagged with DUMMY-* and is safe to remove by
-- running the cleanup section below. It does not create or modify roles/users.
-- Run after all Laravel migrations and base seeders have completed.
--
-- Important: data is shared across roles. Roles see different menus/data based
-- on application authorization; this script does not duplicate the same risk
-- per role.

SET SQL_SAFE_UPDATES = 0;
SET @dummy_year = 2026;
SET @dummy_now = NOW();

START TRANSACTION;

-- ---------------------------------------------------------------------------
-- CLEANUP: remove a previous dummy run only.
-- ---------------------------------------------------------------------------
DELETE FROM risk_incident_actions
WHERE risk_incident_id IN (
    SELECT id FROM risk_incidents WHERE incident_code LIKE 'DUMMY-INC-%'
);
DELETE FROM risk_incidents WHERE incident_code LIKE 'DUMMY-INC-%';

DELETE FROM kri_logs
WHERE kri_id IN (SELECT id FROM kris WHERE metric_name LIKE 'DUMMY-%');
DELETE FROM kris WHERE metric_name LIKE 'DUMMY-%';

DELETE FROM risk_comment_mentions
WHERE risk_comment_id IN (
    SELECT id FROM risk_comments WHERE content LIKE '[DUMMY] %'
);
DELETE FROM risk_comments WHERE content LIKE '[DUMMY] %';

DELETE FROM risk_control_framework_mappings
WHERE risk_control_id IN (
    SELECT id FROM risk_controls WHERE control_code LIKE 'DUMMY-CTRL-%'
);
DELETE FROM risk_control_tests
WHERE risk_control_id IN (
    SELECT id FROM risk_controls WHERE control_code LIKE 'DUMMY-CTRL-%'
);
DELETE FROM risk_acceptances
WHERE risk_id IN (SELECT id FROM risks WHERE risk_id LIKE 'DUMMY-RISK-%');
DELETE FROM risk_controls WHERE control_code LIKE 'DUMMY-CTRL-%';

DELETE FROM risk_scenario_impacts
WHERE risk_scenario_id IN (
    SELECT id FROM risk_scenarios WHERE scenario_code LIKE 'DUMMY-SCN-%'
);
DELETE FROM risk_scenarios WHERE scenario_code LIKE 'DUMMY-SCN-%';
DELETE FROM risk_maturity_assessments WHERE assessment_period = 'DUMMY-2026';
DELETE FROM risk_committee_decisions WHERE meeting_reference LIKE 'DUMMY-CMT-%';

DELETE FROM bcps
WHERE bia_id IN (SELECT id FROM bias WHERE risk_id IN (
    SELECT id FROM risks WHERE risk_id LIKE 'DUMMY-RISK-%'
));
DELETE FROM bias WHERE risk_id IN (
    SELECT id FROM risks WHERE risk_id LIKE 'DUMMY-RISK-%'
);
DELETE FROM risk_rcsa_workspaces WHERE risk_id IN (
    SELECT id FROM risks WHERE risk_id LIKE 'DUMMY-RISK-%'
);

DELETE FROM vendor_risk_assessments
WHERE vendor_id IN (SELECT id FROM vendors WHERE name LIKE 'DUMMY Vendor %');
DELETE FROM vendors WHERE name LIKE 'DUMMY Vendor %';

DELETE FROM resilience_restore_tests
WHERE resilience_backup_record_id IN (
    SELECT id FROM resilience_backup_records WHERE backup_reference LIKE 'DUMMY-BKP-%'
);
DELETE FROM resilience_backup_records WHERE backup_reference LIKE 'DUMMY-BKP-%';

DELETE FROM data_purge_requests
WHERE scope_reference LIKE 'DUMMY-%';
DELETE FROM data_retention_schedules WHERE record_type LIKE 'DUMMY-%';

DELETE FROM scheduled_report_runs
WHERE scheduled_report_id IN (
    SELECT id FROM scheduled_reports WHERE name LIKE 'DUMMY Report %'
);
DELETE FROM scheduled_reports WHERE name LIKE 'DUMMY Report %';

DELETE FROM compliance_work_programs WHERE work_id LIKE 'DUMMY-CWP-%';
DELETE FROM risk_treatment_templates WHERE name LIKE 'DUMMY Playbook %';
DELETE FROM risk_appetites WHERE name LIKE 'DUMMY Appetite %';

DELETE FROM risk_treatments
WHERE risk_id IN (SELECT id FROM risks WHERE risk_id LIKE 'DUMMY-RISK-%');
DELETE FROM risk_residual_analyses
WHERE risk_id IN (SELECT id FROM risks WHERE risk_id LIKE 'DUMMY-RISK-%');
DELETE FROM risk_inherent_analyses
WHERE risk_id IN (SELECT id FROM risks WHERE risk_id LIKE 'DUMMY-RISK-%');
DELETE FROM risks WHERE risk_id LIKE 'DUMMY-RISK-%';

-- ---------------------------------------------------------------------------
-- SEED LOOKUP / SUPPORT DATA
-- ---------------------------------------------------------------------------
INSERT INTO risk_appetites
    (name, risk_category, threshold_metric, appetite_statement,
     appetite_limit_value, tolerance_limit_value, unit, is_active, created_by,
     created_at, updated_at)
VALUES
    ('DUMMY Appetite Operational', 'Operasional', 'residual_risk_score',
     'Dummy appetite for operational risk testing.', 9, 12, 'score', 1,
     (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY Appetite Technology', 'Teknis', 'residual_risk_score',
     'Dummy appetite for technology risk testing.', 9, 12, 'score', 1,
     (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY Appetite Data', 'Data', 'residual_potential_loss',
     'Dummy appetite for data risk testing.', 500000000, 1000000000, 'IDR', 1,
     (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY Appetite Compliance', 'Kepatuhan', 'qualitative_only',
     'Dummy appetite for compliance risk testing.', NULL, NULL, 'qualitative', 1,
     (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY Appetite Third Party', 'Pihak Ketiga', 'residual_risk_score',
     'Dummy appetite for third-party risk testing.', 6, 9, 'score', 1,
     (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now);

INSERT INTO risk_treatment_templates
    (name, risk_owner, option, description, default_pic, default_effectiveness,
     target_days, next_action_offset_days, checklist, is_active, created_by,
     created_at, updated_at)
VALUES
    ('DUMMY Playbook Preventive Control', 'BT - BANKING APPLICATIONS', 'Mitigate',
     'Dummy preventive control playbook.', 'Risk Owner', 'Medium', 30, 7,
     'Review control design; assign PIC; attach evidence.', 1,
     (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY Playbook Detective Monitoring', 'DI - DIGITAL SOLUTION', 'Mitigate',
     'Dummy detective monitoring playbook.', 'Risk Manager', 'Medium', 45, 14,
     'Define KRI; configure threshold; review monthly.', 1,
     (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY Playbook Transfer', 'CO - COMPLIANCE', 'Transfer',
     'Dummy risk transfer playbook.', 'Compliance Officer', 'High', 60, 14,
     'Review contract; verify liability; obtain approval.', 1,
     (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY Playbook Acceptance', 'CO - COMPLIANCE', 'Accept',
     'Dummy risk acceptance playbook.', 'Risk Manager', 'Low', 14, 3,
     'Document rationale; define compensating control; set expiry.', 1,
     (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY Playbook Avoidance', 'BS - BUSINESS STRATEGY', 'Avoid',
     'Dummy risk avoidance playbook.', 'Risk Owner', 'High', 30, 7,
     'Validate alternative process; obtain committee decision.', 1,
     (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now);

INSERT INTO vendors
    (name, description, contact_name, contact_email, status, criticality_level,
     onboarding_status, risk_rating, service_owner, data_classification,
     contract_end_date, next_review_at, last_assessed_at, created_at, updated_at)
VALUES
    ('DUMMY Vendor Cloud Platform', 'Dummy cloud infrastructure provider.', 'Vendor Contact 1', 'vendor1@dummy.local', 'Active', 'High', 'approved', 'High', 'IT Operation Management', 'Confidential', '2027-12-31', '2026-12-15', @dummy_now, @dummy_now, @dummy_now),
    ('DUMMY Vendor Payment Gateway', 'Dummy payment gateway provider.', 'Vendor Contact 2', 'vendor2@dummy.local', 'Active', 'High', 'under_review', 'High', 'Digital Banking', 'Restricted', '2027-09-30', '2026-11-30', @dummy_now, @dummy_now),
    ('DUMMY Vendor Security Testing', 'Dummy security testing provider.', 'Vendor Contact 3', 'vendor3@dummy.local', 'Active', 'Medium', 'approved', 'Medium', 'Cyber Security', 'Confidential', '2027-06-30', '2027-01-15', @dummy_now, @dummy_now),
    ('DUMMY Vendor Office Services', 'Dummy non-critical office service provider.', 'Vendor Contact 4', 'vendor4@dummy.local', 'Active', 'Low', 'approved', 'Low', 'Business Support', 'Internal', '2027-03-31', '2027-02-15', @dummy_now, @dummy_now),
    ('DUMMY Vendor Data Analytics', 'Dummy data analytics provider.', 'Vendor Contact 5', 'vendor5@dummy.local', 'Active', 'Medium', 'draft', 'Medium', 'Research & Innovation', 'Confidential', '2027-10-31', '2026-12-30', NULL, @dummy_now, @dummy_now);

-- ---------------------------------------------------------------------------
-- 50 RISK REGISTER RECORDS
-- ---------------------------------------------------------------------------
CREATE TEMPORARY TABLE _dummy_seq (n INT PRIMARY KEY);
INSERT INTO _dummy_seq (n) VALUES
(1),(2),(3),(4),(5),(6),(7),(8),(9),(10),
(11),(12),(13),(14),(15),(16),(17),(18),(19),(20),
(21),(22),(23),(24),(25),(26),(27),(28),(29),(30),
(31),(32),(33),(34),(35),(36),(37),(38),(39),(40),
(41),(42),(43),(44),(45),(46),(47),(48),(49),(50);

INSERT INTO risks
    (risk_id, year, risk_title, sasaran_target, risk_description,
     risk_category_id, risk_appetite_id, risk_cause_category_id, asset_id,
     risk_owner, status, probability_notes, probability_score, impact_notes,
     impact_score, risk_score, impact_scale_id, created_by, vendor_id,
     created_at, updated_at)
SELECT
    CONCAT('DUMMY-RISK-', LPAD(s.n, 3, '0')),
    @dummy_year,
    CONCAT('Dummy Risk Register ', LPAD(s.n, 3, '0'), ' - ',
        CASE MOD(s.n, 8)
            WHEN 0 THEN 'Gangguan layanan aplikasi'
            WHEN 1 THEN 'Kegagalan kontrol akses'
            WHEN 2 THEN 'Keterlambatan pemulihan'
            WHEN 3 THEN 'Kualitas data tidak memadai'
            WHEN 4 THEN 'Ketergantungan pihak ketiga'
            WHEN 5 THEN 'Pelanggaran kepatuhan'
            WHEN 6 THEN 'Kegagalan perubahan sistem'
            ELSE 'Kapasitas operasional tidak mencukupi'
        END),
    CONCAT('Dummy target pengendalian risiko untuk proses nomor ', s.n),
    CONCAT('Data dummy untuk pengujian risk register dan dashboard. Risiko nomor ', s.n,
           ' digunakan hanya pada lingkungan development.'),
    CASE MOD(s.n, 6)
        WHEN 0 THEN (SELECT id FROM risk_categories WHERE name = 'Operasional' LIMIT 1)
        WHEN 1 THEN (SELECT id FROM risk_categories WHERE name = 'Teknis' LIMIT 1)
        WHEN 2 THEN (SELECT id FROM risk_categories WHERE name = 'Data' LIMIT 1)
        WHEN 3 THEN (SELECT id FROM risk_categories WHERE name = 'Kepatuhan' LIMIT 1)
        WHEN 4 THEN (SELECT id FROM risk_categories WHERE name = 'Pihak Ketiga' LIMIT 1)
        ELSE (SELECT id FROM risk_categories WHERE name = 'Keuangan' LIMIT 1)
    END,
    CASE MOD(s.n - 1, 5)
        WHEN 0 THEN (SELECT id FROM risk_appetites WHERE name = 'DUMMY Appetite Operational' LIMIT 1)
        WHEN 1 THEN (SELECT id FROM risk_appetites WHERE name = 'DUMMY Appetite Technology' LIMIT 1)
        WHEN 2 THEN (SELECT id FROM risk_appetites WHERE name = 'DUMMY Appetite Data' LIMIT 1)
        WHEN 3 THEN (SELECT id FROM risk_appetites WHERE name = 'DUMMY Appetite Compliance' LIMIT 1)
        ELSE (SELECT id FROM risk_appetites WHERE name = 'DUMMY Appetite Third Party' LIMIT 1)
    END,
    CASE MOD(s.n, 4)
        WHEN 0 THEN (SELECT id FROM risk_cause_categories WHERE name = 'People' LIMIT 1)
        WHEN 1 THEN (SELECT id FROM risk_cause_categories WHERE name = 'Process' LIMIT 1)
        WHEN 2 THEN (SELECT id FROM risk_cause_categories WHERE name = 'Product' LIMIT 1)
        ELSE (SELECT id FROM risk_cause_categories WHERE name = 'Nature' LIMIT 1)
    END,
    NULL,
    CASE MOD(s.n, 6)
        WHEN 0 THEN 'CO - COMPLIANCE'
        WHEN 1 THEN 'BT - BANKING APPLICATIONS'
        WHEN 2 THEN 'DI - DIGITAL SOLUTION'
        WHEN 3 THEN 'OM - DATA CENTER OPERATION'
        WHEN 4 THEN 'CS - CYBER SECURITY'
        ELSE 'BS - BUSINESS SUPPORT'
    END,
    CASE MOD(s.n, 3)
        WHEN 0 THEN 'Open'
        WHEN 1 THEN 'Monitoring'
        ELSE 'Closed'
    END,
    CONCAT('Probability rationale dummy ', s.n),
    1 + MOD(s.n, 5),
    CONCAT('Impact rationale dummy ', s.n),
    1 + MOD(s.n * 2, 5),
    (1 + MOD(s.n, 5)) * (1 + MOD(s.n * 2, 5)),
    (SELECT id FROM impact_scales WHERE score = 1 + MOD(s.n * 2, 5) ORDER BY id LIMIT 1),
    (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1),
    CASE MOD(s.n, 5)
        WHEN 0 THEN (SELECT id FROM vendors WHERE name = 'DUMMY Vendor Cloud Platform' LIMIT 1)
        WHEN 1 THEN (SELECT id FROM vendors WHERE name = 'DUMMY Vendor Payment Gateway' LIMIT 1)
        ELSE NULL
    END,
    @dummy_now, @dummy_now
FROM _dummy_seq s;

-- Link vendor risks after insert, using a deterministic distribution.
UPDATE risks r
JOIN vendors v ON v.name = CASE MOD(CAST(SUBSTRING(r.risk_id, 12) AS UNSIGNED), 5)
    WHEN 0 THEN 'DUMMY Vendor Cloud Platform'
    WHEN 1 THEN 'DUMMY Vendor Payment Gateway'
    ELSE 'DUMMY Vendor Security Testing'
END
SET r.vendor_id = v.id
WHERE r.risk_id LIKE 'DUMMY-RISK-%';

-- ---------------------------------------------------------------------------
-- INHERENT / RESIDUAL ANALYSIS, TREATMENT, CONTROLS, KRI
-- ---------------------------------------------------------------------------
INSERT INTO risk_inherent_analyses
    (risk_id, probability_score, probability_description, impact_score,
     impact_description, impact_scale_id, inherent_risk_score, risk_level,
     impact_amount, probability_percent, potential_loss, created_at, updated_at)
SELECT
    r.id, r.probability_score, CONCAT('Dummy inherent probability for ', r.risk_id),
    r.impact_score, CONCAT('Dummy inherent impact for ', r.risk_id), r.impact_scale_id,
    r.risk_score,
    CASE WHEN r.risk_score <= 4 THEN 'Low' WHEN r.risk_score <= 9 THEN 'Medium'
         WHEN r.risk_score <= 14 THEN 'High' ELSE 'Critical' END,
    100000000 * r.impact_score,
    r.probability_score * 20,
    100000000 * r.impact_score * r.probability_score / 5,
    @dummy_now, @dummy_now
FROM risks r WHERE r.risk_id LIKE 'DUMMY-RISK-%';

INSERT INTO risk_residual_analyses
    (risk_id, probability_score, probability_description, impact_score,
     impact_description, impact_scale_id, residual_risk_score, risk_adjustment,
     risk_level, adjustment, risk_level_adjustment, radar_description,
     radar_code, radar_group_id, impact_amount, probability_percent,
     potential_loss, created_at, updated_at)
SELECT
    r.id, GREATEST(1, r.probability_score - 1), CONCAT('Dummy residual probability for ', r.risk_id),
    GREATEST(1, r.impact_score - CASE WHEN MOD(r.id, 3) = 0 THEN 1 ELSE 0 END),
    CONCAT('Dummy residual impact for ', r.risk_id), r.impact_scale_id,
    GREATEST(1, r.probability_score - 1) * GREATEST(1, r.impact_score - CASE WHEN MOD(r.id, 3) = 0 THEN 1 ELSE 0 END),
    -1,
    CASE WHEN GREATEST(1, r.probability_score - 1) * GREATEST(1, r.impact_score - CASE WHEN MOD(r.id, 3) = 0 THEN 1 ELSE 0 END) <= 4 THEN 'Low'
         WHEN GREATEST(1, r.probability_score - 1) * GREATEST(1, r.impact_score - CASE WHEN MOD(r.id, 3) = 0 THEN 1 ELSE 0 END) <= 9 THEN 'Medium'
         WHEN GREATEST(1, r.probability_score - 1) * GREATEST(1, r.impact_score - CASE WHEN MOD(r.id, 3) = 0 THEN 1 ELSE 0 END) <= 14 THEN 'High'
         ELSE 'Critical' END,
    'Reduced by dummy control', 'Reduced by dummy control',
    'Dummy radar classification',
    CASE MOD(r.id, 5) WHEN 0 THEN 'O2' WHEN 1 THEN 'S1' WHEN 2 THEN 'C2' WHEN 3 THEN 'F1' ELSE 'T1' END,
    (SELECT id FROM radar_groups WHERE code = CASE MOD(r.id, 5) WHEN 0 THEN 'O2' WHEN 1 THEN 'S1' WHEN 2 THEN 'C2' WHEN 3 THEN 'F1' ELSE 'T1' END LIMIT 1),
    100000000 * GREATEST(1, r.impact_score - 1),
    GREATEST(1, r.probability_score - 1) * 20,
    100000000 * GREATEST(1, r.impact_score - 1) * GREATEST(1, r.probability_score - 1) / 5,
    @dummy_now, @dummy_now
FROM risks r WHERE r.risk_id LIKE 'DUMMY-RISK-%';

INSERT INTO risk_treatments
    (risk_id, template_id, option, description, pic, status, status_updated_at,
     deadline, next_action_at, blocked_reason, playbook_checklist,
     effectiveness, effectiveness_score, created_at, updated_at)
SELECT
    r.id,
    CASE MOD(r.id - 1, 5)
        WHEN 0 THEN (SELECT id FROM risk_treatment_templates WHERE name = 'DUMMY Playbook Preventive Control' LIMIT 1)
        WHEN 1 THEN (SELECT id FROM risk_treatment_templates WHERE name = 'DUMMY Playbook Detective Monitoring' LIMIT 1)
        WHEN 2 THEN (SELECT id FROM risk_treatment_templates WHERE name = 'DUMMY Playbook Transfer' LIMIT 1)
        WHEN 3 THEN (SELECT id FROM risk_treatment_templates WHERE name = 'DUMMY Playbook Acceptance' LIMIT 1)
        ELSE (SELECT id FROM risk_treatment_templates WHERE name = 'DUMMY Playbook Avoidance' LIMIT 1)
    END,
    CASE MOD(r.id, 4) WHEN 0 THEN 'Mitigate' WHEN 1 THEN 'Transfer' WHEN 2 THEN 'Accept' ELSE 'Avoid' END,
    CONCAT('Dummy mitigation plan for ', r.risk_id), r.risk_owner,
    CASE MOD(r.id, 4) WHEN 0 THEN 'In Progress' WHEN 1 THEN 'Not Started' WHEN 2 THEN 'Completed' ELSE 'Blocked' END,
    @dummy_now, DATE_ADD(CURDATE(), INTERVAL (15 + MOD(r.id, 60)) DAY),
    DATE_ADD(CURDATE(), INTERVAL (7 + MOD(r.id, 21)) DAY), NULL,
    'Dummy checklist: design reviewed; PIC assigned; evidence pending.',
    CASE MOD(r.id, 3) WHEN 0 THEN 'High' WHEN 1 THEN 'Medium' ELSE 'Low' END,
    CASE MOD(r.id, 3) WHEN 0 THEN 0.80 WHEN 1 THEN 0.60 ELSE 0.40 END,
    @dummy_now, @dummy_now
FROM risks r WHERE r.risk_id LIKE 'DUMMY-RISK-%';

INSERT INTO risk_controls
    (risk_id, control_code, name, description, owner_name, review_frequency,
     status, design_effectiveness, last_tested_at, next_test_at, created_by,
     created_at, updated_at)
SELECT
    r.id, CONCAT('DUMMY-CTRL-', LPAD(CAST(SUBSTRING(r.risk_id, 12) AS UNSIGNED), 3, '0')),
    CONCAT('Dummy control for ', r.risk_id),
    'Dummy control description for assurance testing.', r.risk_owner,
    CASE MOD(r.id, 3) WHEN 0 THEN 'Monthly' WHEN 1 THEN 'Quarterly' ELSE 'Semi-Annual' END,
    'active', CASE MOD(r.id, 3) WHEN 0 THEN 'effective' WHEN 1 THEN 'partially_effective' ELSE 'not_assessed' END,
    DATE_SUB(CURDATE(), INTERVAL MOD(r.id, 90) DAY), DATE_ADD(CURDATE(), INTERVAL (30 + MOD(r.id, 90)) DAY),
    (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now
FROM risks r WHERE r.risk_id LIKE 'DUMMY-RISK-%';

INSERT INTO risk_control_tests
    (risk_control_id, tested_by, tested_at, result, findings, remediation_plan,
     remediation_due_at, remediation_status, created_at, updated_at)
SELECT
    c.id, (SELECT id FROM users WHERE username = 'auditor' LIMIT 1),
    DATE_SUB(CURDATE(), INTERVAL MOD(c.id, 60) DAY),
    CASE MOD(c.id, 3) WHEN 0 THEN 'effective' WHEN 1 THEN 'partially_effective' ELSE 'ineffective' END,
    CASE WHEN MOD(c.id, 3) = 0 THEN 'Dummy test passed.' ELSE 'Dummy finding requires follow-up.' END,
    CASE WHEN MOD(c.id, 3) = 0 THEN NULL ELSE 'Dummy remediation plan and evidence collection.' END,
    CASE WHEN MOD(c.id, 3) = 0 THEN NULL ELSE DATE_ADD(CURDATE(), INTERVAL 30 DAY) END,
    CASE WHEN MOD(c.id, 3) = 0 THEN 'not_required' ELSE 'open' END,
    @dummy_now, @dummy_now
FROM risk_controls c WHERE c.control_code LIKE 'DUMMY-CTRL-%';

INSERT INTO risk_control_framework_mappings
    (risk_control_id, framework_code, control_reference, requirement_title,
     mapping_status, evidence_notes, last_reviewed_at, reviewed_by, created_at, updated_at)
SELECT
    c.id,
    CASE MOD(c.id, 4) WHEN 0 THEN 'ISO27001' WHEN 1 THEN 'NIST-CSF-2.0'
         WHEN 2 THEN 'PBI-02-2024' ELSE 'SEOJK-29-2022' END,
    CASE MOD(c.id, 4) WHEN 0 THEN 'A.5.1' WHEN 1 THEN 'PR.AA'
         WHEN 2 THEN 'Tata Kelola TI' ELSE 'Protective Security' END,
    'Dummy framework control mapping', 'mapped', 'Dummy evidence reference for local testing.',
    CURDATE(), (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now
FROM risk_controls c WHERE c.control_code LIKE 'DUMMY-CTRL-%';

INSERT INTO risk_acceptances
    (risk_id, requested_by, reviewed_by, status, rationale, compensating_controls,
     accepted_until, decision_notes, decided_at, created_at, updated_at)
SELECT
    r.id, (SELECT id FROM users WHERE username = 'risk-owner' LIMIT 1),
    CASE WHEN MOD(r.id, 2) = 0 THEN (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1) ELSE NULL END,
    CASE WHEN MOD(r.id, 2) = 0 THEN 'approved' ELSE 'requested' END,
    CONCAT('Dummy acceptance rationale for ', r.risk_id),
    'Dummy compensating control and additional monitoring.', DATE_ADD(CURDATE(), INTERVAL 180 DAY),
    CASE WHEN MOD(r.id, 2) = 0 THEN 'Dummy committee decision approved.' ELSE NULL END,
    CASE WHEN MOD(r.id, 2) = 0 THEN @dummy_now ELSE NULL END, @dummy_now, @dummy_now
FROM risks r
WHERE r.risk_id LIKE 'DUMMY-RISK-%' AND MOD(CAST(SUBSTRING(r.risk_id, 12) AS UNSIGNED), 5) = 0;

INSERT INTO kris
    (risk_id, metric_name, description, data_source, source_identifier,
     source_reference, expected_refresh_minutes, source_updated_at,
     data_quality_status, data_quality_notes, last_quality_checked_at,
     last_sync_error, threshold_type, threshold_warn, threshold_critical,
     current_value, status, last_updated_by, created_at, updated_at)
SELECT
    r.id, CONCAT('DUMMY-KRI-', LPAD(CAST(SUBSTRING(r.risk_id, 12) AS UNSIGNED), 3, '0')),
    CONCAT('Dummy KRI for ', r.risk_id), 'manual', CONCAT('dummy-source-', r.id),
    'https://dummy.local/kri', 1440, @dummy_now, 'valid', 'Dummy data quality verified.',
    @dummy_now, NULL, 'lower_is_better', 70, 90, 50 + MOD(r.id, 50),
    CASE WHEN 50 + MOD(r.id, 50) >= 90 THEN 'Red' WHEN 50 + MOD(r.id, 50) >= 70 THEN 'Amber' ELSE 'Green' END,
    (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now
FROM risks r WHERE r.risk_id LIKE 'DUMMY-RISK-%';

INSERT INTO kri_logs
    (kri_id, recorded_value, source_recorded_at, source_reference,
     status_calculated, data_quality_status, notes, recorded_by, created_at, updated_at)
SELECT
    k.id, 45 + MOD(k.id, 55), @dummy_now, 'DUMMY-KRI-LOG',
    CASE WHEN 45 + MOD(k.id, 55) >= 90 THEN 'Red' WHEN 45 + MOD(k.id, 55) >= 70 THEN 'Amber' ELSE 'Green' END,
    'valid', 'Dummy KRI log entry.',
    (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now
FROM kris k WHERE k.metric_name LIKE 'DUMMY-KRI-%';

-- ---------------------------------------------------------------------------
-- INCIDENTS, COMMENTS, RCSA, BCM
-- ---------------------------------------------------------------------------
INSERT INTO risk_incidents
    (incident_code, risk_id, reported_by, last_updated_by, title,
     incident_description, occurred_at, detected_at, severity, status,
     mitigation_verdict, business_impact, immediate_action, root_cause,
     corrective_action, resolution_notes, closed_at, lessons_learned,
     risk_review_status, control_review_status, kri_review_status,
     post_incident_reviewed_by, post_incident_reviewed_at, created_at, updated_at)
SELECT
    CONCAT('DUMMY-INC-', LPAD(CAST(SUBSTRING(r.risk_id, 12) AS UNSIGNED), 3, '0')),
    r.id, (SELECT id FROM users WHERE username = 'risk-owner' LIMIT 1),
    (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1),
    CONCAT('Dummy incident for ', r.risk_id),
    'Dummy incident description for incident learning testing.',
    DATE_SUB(@dummy_now, INTERVAL MOD(r.id, 45) DAY), DATE_SUB(@dummy_now, INTERVAL MOD(r.id, 40) DAY),
    CASE MOD(r.id, 3) WHEN 0 THEN 'High' WHEN 1 THEN 'Medium' ELSE 'Low' END,
    CASE MOD(r.id, 3) WHEN 0 THEN 'Open' WHEN 1 THEN 'Investigating' ELSE 'Closed' END,
    CASE MOD(r.id, 3) WHEN 0 THEN 'Not Tested' WHEN 1 THEN 'Partially Effective' ELSE 'Effective' END,
    'Dummy business impact.', 'Dummy immediate containment action.', 'Dummy root cause.',
    'Dummy corrective action.', CASE WHEN MOD(r.id, 3) = 2 THEN 'Dummy incident closed.' ELSE NULL END,
    CASE WHEN MOD(r.id, 3) = 2 THEN DATE_ADD(@dummy_now, INTERVAL 0 DAY) ELSE NULL END,
    'Dummy lesson learned.', CASE WHEN MOD(r.id, 3) = 2 THEN 'reviewed' ELSE 'not_required' END,
    CASE WHEN MOD(r.id, 3) = 2 THEN 'reviewed' ELSE 'not_required' END,
    CASE WHEN MOD(r.id, 3) = 2 THEN 'reviewed' ELSE 'not_required' END,
    CASE WHEN MOD(r.id, 3) = 2 THEN (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1) ELSE NULL END,
    CASE WHEN MOD(r.id, 3) = 2 THEN @dummy_now ELSE NULL END,
    @dummy_now, @dummy_now
FROM risks r
WHERE r.risk_id LIKE 'DUMMY-RISK-%'
  AND MOD(CAST(SUBSTRING(r.risk_id, 12) AS UNSIGNED), 5) = 0;

INSERT INTO risk_incident_actions
    (risk_incident_id, action_type, title, description, owner_name, due_at,
     status, completion_notes, completed_by, completed_at, verified_by,
     verified_at, created_at, updated_at)
SELECT
    i.id, 'Corrective', CONCAT('Dummy CAPA for ', i.incident_code),
    'Dummy corrective/preventive action.', 'Risk Manager', DATE_ADD(CURDATE(), INTERVAL 30 DAY),
    CASE WHEN MOD(i.id, 2) = 0 THEN 'completed' ELSE 'open' END,
    CASE WHEN MOD(i.id, 2) = 0 THEN 'Dummy action completed.' ELSE NULL END,
    CASE WHEN MOD(i.id, 2) = 0 THEN (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1) ELSE NULL END,
    CASE WHEN MOD(i.id, 2) = 0 THEN @dummy_now ELSE NULL END,
    CASE WHEN MOD(i.id, 2) = 0 THEN (SELECT id FROM users WHERE username = 'auditor' LIMIT 1) ELSE NULL END,
    CASE WHEN MOD(i.id, 2) = 0 THEN @dummy_now ELSE NULL END,
    @dummy_now, @dummy_now
FROM risk_incidents i WHERE i.incident_code LIKE 'DUMMY-INC-%';

INSERT INTO risk_comments
    (risk_id, user_id, content, edited_at, created_at, updated_at)
SELECT
    r.id, (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1),
    CONCAT('[DUMMY] Review note for ', r.risk_id), NULL, @dummy_now, @dummy_now
FROM risks r WHERE r.risk_id LIKE 'DUMMY-RISK-%';

INSERT INTO risk_rcsa_workspaces
    (risk_id, process_owner, objective, scope, key_controls, control_gaps,
     likelihood_rationale, impact_rationale, review_frequency, next_review_at,
     sign_off_name, sign_off_role, signed_off_at, notes, updated_by, created_at, updated_at)
SELECT
    r.id, r.risk_owner, CONCAT('Dummy RCSA objective for ', r.risk_id),
    'Dummy process scope', 'Dummy key controls', 'Dummy control gap under observation',
    'Dummy likelihood rationale', 'Dummy impact rationale', 'Quarterly', DATE_ADD(CURDATE(), INTERVAL 90 DAY),
    CASE WHEN MOD(r.id, 2) = 0 THEN 'RMS Local Compliance Officer' ELSE NULL END,
    CASE WHEN MOD(r.id, 2) = 0 THEN 'Compliance Officer' ELSE NULL END,
    CASE WHEN MOD(r.id, 2) = 0 THEN @dummy_now ELSE NULL END,
    'Dummy RCSA notes.', (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now
FROM risks r WHERE r.risk_id LIKE 'DUMMY-RISK-%';

INSERT INTO bias
    (risk_id, rto, rpo, mao, impact_financial, impact_operational,
     impact_regulatory, impact_reputational, criticality_tier, justification_notes,
     created_at, updated_at)
SELECT
    r.id, CASE MOD(r.id, 3) WHEN 0 THEN '60 minutes' WHEN 1 THEN '4 hours' ELSE '24 hours' END,
    CASE MOD(r.id, 3) WHEN 0 THEN '15 minutes' WHEN 1 THEN '1 hour' ELSE '4 hours' END,
    CASE MOD(r.id, 3) WHEN 0 THEN '2 hours' WHEN 1 THEN '8 hours' ELSE '48 hours' END,
    CASE MOD(r.id, 4) WHEN 0 THEN 'Critical' WHEN 1 THEN 'High' ELSE 'Medium' END,
    CASE MOD(r.id, 4) WHEN 0 THEN 'High' WHEN 1 THEN 'Medium' ELSE 'Low' END,
    CASE MOD(r.id, 4) WHEN 0 THEN 'High' WHEN 1 THEN 'Medium' ELSE 'Low' END,
    CASE MOD(r.id, 4) WHEN 0 THEN 'High' WHEN 1 THEN 'Medium' ELSE 'Low' END,
    CASE MOD(r.id, 3) WHEN 0 THEN 'Tier 1' WHEN 1 THEN 'Tier 2' ELSE 'Tier 3' END,
    CONCAT('Dummy BIA justification for ', r.risk_id), @dummy_now, @dummy_now
FROM risks r WHERE r.risk_id LIKE 'DUMMY-RISK-%';

INSERT INTO bcps
    (bia_id, strategy_summary, recovery_procedures, primary_workaround,
     testing_frequency, last_tested_at, last_test_result, created_at, updated_at)
SELECT
    b.id, 'Dummy continuity strategy.', 'Dummy recovery procedure.', 'Dummy manual workaround.',
    'Annual', DATE_SUB(CURDATE(), INTERVAL 30 DAY), CASE MOD(b.id, 3) WHEN 0 THEN 'Pass' WHEN 1 THEN 'Partial' ELSE 'Fail' END,
    @dummy_now, @dummy_now
FROM bias b JOIN risks r ON r.id = b.risk_id WHERE r.risk_id LIKE 'DUMMY-RISK-%';

-- ---------------------------------------------------------------------------
-- VENDOR ASSESSMENT, COMPLIANCE, COMMITTEE, SCENARIO, MATURITY
-- ---------------------------------------------------------------------------
INSERT INTO vendor_risk_assessments
    (vendor_id, assessment_type, assessed_at, assessed_by, inherent_rating,
     residual_rating, decision, valid_until, findings, risk_treatment, status,
     created_at, updated_at)
SELECT
    v.id, 'Periodic Review', CURDATE(), (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1),
    CASE v.criticality_level WHEN 'High' THEN 5 WHEN 'Medium' THEN 3 ELSE 1 END,
    CASE v.criticality_level WHEN 'High' THEN 3 WHEN 'Medium' THEN 2 ELSE 1 END,
    CASE WHEN v.risk_rating = 'High' THEN 'Conditional' ELSE 'Approved' END,
    DATE_ADD(CURDATE(), INTERVAL 365 DAY), 'Dummy vendor assessment finding.',
    'Dummy vendor risk treatment.', 'open', @dummy_now, @dummy_now
FROM vendors v WHERE v.name LIKE 'DUMMY Vendor %';

INSERT INTO compliance_work_programs
    (section, work_id, program_kerja, deskripsi, target_sasaran_kerja, kategori,
     q1, q2, q3, q4, alignment_champion_program, alignment_risk_profile_2025,
     support_needed, created_by, created_at, updated_at)
VALUES
    ('Risk Management', 'DUMMY-CWP-001', 'Dummy review regulatory mapping', 'Dummy compliance work program.', 'Complete mapping and evidence review.', 'Monitoring', 1, 1, 0, 0, 'Dummy champion alignment', 'Dummy risk profile alignment', 'Dummy support needed', (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now),
    ('Internal Audit', 'DUMMY-CWP-002', 'Dummy control testing cycle', 'Dummy internal audit work program.', 'Test priority controls.', 'Assurance', 0, 1, 1, 0, 'Dummy champion alignment', 'Dummy risk profile alignment', 'Dummy support needed', (SELECT id FROM users WHERE username = 'auditor' LIMIT 1), @dummy_now, @dummy_now),
    ('Governance', 'DUMMY-CWP-003', 'Dummy committee preparation', 'Dummy governance work program.', 'Prepare risk committee pack.', 'Governance', 0, 0, 1, 1, 'Dummy champion alignment', 'Dummy risk profile alignment', 'Dummy support needed', (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now),
    ('Compliance', 'DUMMY-CWP-004', 'Dummy regulatory self-assessment', 'Dummy self-assessment work program.', 'Complete quarterly self-assessment.', 'Compliance', 1, 0, 1, 0, 'Dummy champion alignment', 'Dummy risk profile alignment', 'Dummy support needed', (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now);

INSERT INTO risk_committee_decisions
    (meeting_date, meeting_reference, risk_id, title, decision, owner_name,
     due_at, status, closure_notes, created_by, updated_by, created_at, updated_at)
SELECT
    DATE_SUB(CURDATE(), INTERVAL MOD(r.id, 30) DAY), CONCAT('DUMMY-CMT-', LPAD(CAST(SUBSTRING(r.risk_id, 12) AS UNSIGNED), 3, '0')),
    r.id, CONCAT('Dummy committee decision for ', r.risk_id),
    'Dummy decision: continue treatment and monitor residual risk.', r.risk_owner,
    DATE_ADD(CURDATE(), INTERVAL 45 DAY), CASE WHEN MOD(r.id, 2) = 0 THEN 'open' ELSE 'closed' END,
    CASE WHEN MOD(r.id, 2) = 0 THEN NULL ELSE 'Dummy decision closed.' END,
    (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1),
    (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now
FROM risks r WHERE r.risk_id LIKE 'DUMMY-RISK-%' AND MOD(CAST(SUBSTRING(r.risk_id, 12) AS UNSIGNED), 5) = 1;

INSERT INTO risk_scenarios
    (scenario_code, title, scenario_type, status, time_horizon, likelihood,
     impact, trigger_event, narrative, response_strategy, owner_name, created_by,
     updated_by, created_at, updated_at)
VALUES
    ('DUMMY-SCN-001', 'Dummy cyber disruption scenario', 'Cyber', 'active', '12 months', 3, 5, 'Dummy cyber event trigger', 'Dummy stress scenario narrative for cyber disruption.', 'Dummy response strategy.', 'CS - CYBER SECURITY', (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY-SCN-002', 'Dummy third-party outage scenario', 'Third Party', 'active', '6 months', 4, 4, 'Dummy vendor outage trigger', 'Dummy stress scenario narrative for third-party outage.', 'Dummy response strategy.', 'CO - COMPLIANCE', (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY-SCN-003', 'Dummy data quality scenario', 'Data', 'draft', '24 months', 3, 3, 'Dummy data quality trigger', 'Dummy scenario narrative for material data quality degradation.', 'Dummy response strategy.', 'DI - DIGITAL SOLUTION', (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now);

INSERT INTO risk_scenario_impacts
    (risk_scenario_id, risk_id, scenario_probability, scenario_impact,
     scenario_score, assumptions, mitigation_response, created_at, updated_at)
SELECT
    s.id, r.id, 3 + MOD(r.id, 3), 3 + MOD(r.id, 3),
    (3 + MOD(r.id, 3)) * (3 + MOD(r.id, 3)),
    'Dummy scenario assumption.', 'Dummy scenario mitigation response.', @dummy_now, @dummy_now
FROM risk_scenarios s
JOIN risks r ON r.risk_id LIKE 'DUMMY-RISK-%'
WHERE s.scenario_code LIKE 'DUMMY-SCN-%'
  AND MOD(CAST(SUBSTRING(r.risk_id, 12) AS UNSIGNED), 3) = CAST(SUBSTRING(s.scenario_code, 11) AS UNSIGNED) - 1;

INSERT INTO risk_maturity_assessments
    (assessment_period, domain, current_score, target_score, evidence,
     improvement_plan, owner_name, target_date, status, assessed_by, updated_by,
     created_at, updated_at)
VALUES
    ('DUMMY-2026', 'Governance', 3, 4, 'Dummy governance evidence.', 'Dummy governance improvement plan.', 'Risk Manager', '2026-12-31', 'open', (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY-2026', 'Risk Management', 2, 4, 'Dummy risk management evidence.', 'Dummy risk management improvement plan.', 'Risk Manager', '2026-12-31', 'open', (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY-2026', 'Information Security', 3, 5, 'Dummy information security evidence.', 'Dummy information security improvement plan.', 'Cyber Security', '2026-12-31', 'open', (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY-2026', 'Compliance', 3, 4, 'Dummy compliance evidence.', 'Dummy compliance improvement plan.', 'Compliance Officer', '2026-12-31', 'open', (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY-2026', 'Operational Resilience', 2, 4, 'Dummy resilience evidence.', 'Dummy resilience improvement plan.', 'Risk Manager', '2026-12-31', 'open', (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY-2026', 'Third Party Risk', 2, 4, 'Dummy third-party evidence.', 'Dummy third-party improvement plan.', 'Compliance Officer', '2026-12-31', 'open', (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now);

-- ---------------------------------------------------------------------------
-- OPERATIONAL RESILIENCE, RETENTION, SCHEDULED REPORTS
-- ---------------------------------------------------------------------------
INSERT INTO resilience_backup_records
    (backup_reference, asset_scope, environment, storage_location, encryption_status,
     status, started_at, completed_at, checksum, retention_until, verified_at,
     evidence_reference, notes, recorded_by, created_at, updated_at)
VALUES
    ('DUMMY-BKP-001', 'database', 'local', 'dummy://backup/database-001', 'encrypted', 'successful', DATE_SUB(@dummy_now, INTERVAL 5 DAY), DATE_SUB(@dummy_now, INTERVAL 5 DAY), 'dummy-checksum-001', DATE_ADD(@dummy_now, INTERVAL 365 DAY), @dummy_now, 'DUMMY-EVIDENCE-BKP-001', 'Dummy backup record.', (SELECT id FROM users WHERE username = 'system-admin' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY-BKP-002', 'files', 'local', 'dummy://backup/object-002', 'encrypted', 'successful', DATE_SUB(@dummy_now, INTERVAL 10 DAY), DATE_SUB(@dummy_now, INTERVAL 10 DAY), 'dummy-checksum-002', DATE_ADD(@dummy_now, INTERVAL 365 DAY), @dummy_now, 'DUMMY-EVIDENCE-BKP-002', 'Dummy backup record.', (SELECT id FROM users WHERE username = 'system-admin' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY-BKP-003', 'configuration', 'local', 'dummy://backup/config-003', 'encrypted', 'successful', DATE_SUB(@dummy_now, INTERVAL 15 DAY), DATE_SUB(@dummy_now, INTERVAL 15 DAY), 'dummy-checksum-003', DATE_ADD(@dummy_now, INTERVAL 365 DAY), @dummy_now, 'DUMMY-EVIDENCE-BKP-003', 'Dummy backup record.', (SELECT id FROM users WHERE username = 'system-admin' LIMIT 1), @dummy_now, @dummy_now);

INSERT INTO resilience_restore_tests
    (resilience_backup_record_id, test_type, performed_at, recovery_time_minutes,
     recovery_point_minutes, result, findings, remediation_plan, evidence_reference,
     performed_by, verified_by, verified_at, created_at, updated_at)
SELECT
    b.id, 'partial_restore', DATE_SUB(@dummy_now, INTERVAL 2 DAY), 45, 15,
    CASE WHEN MOD(b.id, 2) = 0 THEN 'passed' ELSE 'passed_with_findings' END,
    'Dummy restore test findings.', 'Dummy restore remediation plan.',
    CONCAT('DUMMY-EVIDENCE-RESTORE-', b.id),
    (SELECT id FROM users WHERE username = 'system-admin' LIMIT 1),
    (SELECT id FROM users WHERE username = 'auditor' LIMIT 1), @dummy_now, @dummy_now, @dummy_now
FROM resilience_backup_records b WHERE b.backup_reference LIKE 'DUMMY-BKP-%';

INSERT INTO data_retention_schedules
    (record_type, description, retention_months, owner_name, status,
     next_review_at, legal_basis, created_by, updated_by, created_at, updated_at)
VALUES
    ('DUMMY-Risk Register', 'Dummy risk register retention schedule.', 84, 'Risk Management', 'active', '2027-01-31', 'Dummy legal basis.', (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY-Audit Evidence', 'Dummy audit evidence retention schedule.', 120, 'Compliance', 'active', '2027-02-28', 'Dummy legal basis.', (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), @dummy_now, @dummy_now),
    ('DUMMY-Incident Records', 'Dummy incident record retention schedule.', 60, 'Risk Management', 'active', '2027-03-31', 'Dummy legal basis.', (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), (SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), @dummy_now, @dummy_now);

INSERT INTO data_purge_requests
    (data_retention_schedule_id, scope_reference, estimated_record_count,
     requested_at, proposed_purge_at, legal_hold_checked, justification, status,
     review_notes, requested_by, reviewed_by, reviewed_at, executed_by,
     executed_at, evidence_reference, created_at, updated_at)
SELECT
    s.id, CONCAT('DUMMY-PURGE-SCOPE-', s.id), 100 + s.id, @dummy_now,
    DATE_ADD(@dummy_now, INTERVAL 30 DAY), 1,
    'Dummy purge request for lifecycle testing.', 'requested', NULL,
    (SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), NULL, NULL,
    NULL, NULL, CONCAT('DUMMY-PURGE-EVIDENCE-', s.id), @dummy_now, @dummy_now
FROM data_retention_schedules s WHERE s.record_type LIKE 'DUMMY-%';

INSERT INTO scheduled_reports
    (user_id, name, scope, format, frequency, send_time, day_of_week,
     day_of_month, timezone, recipients, filters, is_active, last_run_at,
     next_run_at, created_at, updated_at)
VALUES
    ((SELECT id FROM users WHERE username = 'risk-manager' LIMIT 1), 'DUMMY Report Risk Monitoring', 'monitoring', 'pdf', 'daily', '08:00', NULL, NULL, 'Asia/Jakarta', JSON_ARRAY('risk-manager@dummy.local'), JSON_OBJECT('year', 2026), 1, DATE_SUB(@dummy_now, INTERVAL 1 DAY), DATE_ADD(@dummy_now, INTERVAL 1 DAY), @dummy_now, @dummy_now),
    ((SELECT id FROM users WHERE username = 'compliance-officer' LIMIT 1), 'DUMMY Report Compliance Overview', 'overall', 'xlsx', 'weekly', '09:00', 1, NULL, 'Asia/Jakarta', JSON_ARRAY('compliance-officer@dummy.local'), JSON_OBJECT('year', 2026), 1, DATE_SUB(@dummy_now, INTERVAL 7 DAY), DATE_ADD(@dummy_now, INTERVAL 7 DAY), @dummy_now, @dummy_now),
    ((SELECT id FROM users WHERE username = 'auditor' LIMIT 1), 'DUMMY Report Audit Evidence', 'board', 'pdf', 'monthly', '10:00', NULL, 1, 'Asia/Jakarta', JSON_ARRAY('auditor@dummy.local'), JSON_OBJECT('year', 2026), 1, DATE_SUB(@dummy_now, INTERVAL 30 DAY), DATE_ADD(@dummy_now, INTERVAL 30 DAY), @dummy_now, @dummy_now);

INSERT INTO scheduled_report_runs
    (scheduled_report_id, trigger_type, status, scope, format, recipient_count,
     output_filename, error_message, ran_at, created_at, updated_at)
SELECT
    s.id, 'manual', 'success', s.scope, s.format, 1,
    CONCAT('DUMMY-', s.name, '.pdf'), NULL, DATE_SUB(@dummy_now, INTERVAL 1 DAY), @dummy_now, @dummy_now
FROM scheduled_reports s WHERE s.name LIKE 'DUMMY Report %';

DROP TEMPORARY TABLE _dummy_seq;

COMMIT;

-- ---------------------------------------------------------------------------
-- VERIFICATION QUERIES
-- ---------------------------------------------------------------------------
SELECT 'risks' AS object_name, COUNT(*) AS dummy_count FROM risks WHERE risk_id LIKE 'DUMMY-RISK-%'
UNION ALL SELECT 'inherent analyses', COUNT(*) FROM risk_inherent_analyses ia JOIN risks r ON r.id = ia.risk_id WHERE r.risk_id LIKE 'DUMMY-RISK-%'
UNION ALL SELECT 'residual analyses', COUNT(*) FROM risk_residual_analyses ra JOIN risks r ON r.id = ra.risk_id WHERE r.risk_id LIKE 'DUMMY-RISK-%'
UNION ALL SELECT 'treatments', COUNT(*) FROM risk_treatments t JOIN risks r ON r.id = t.risk_id WHERE r.risk_id LIKE 'DUMMY-RISK-%'
UNION ALL SELECT 'controls', COUNT(*) FROM risk_controls c WHERE c.control_code LIKE 'DUMMY-CTRL-%'
UNION ALL SELECT 'KRIs', COUNT(*) FROM kris WHERE metric_name LIKE 'DUMMY-KRI-%'
UNION ALL SELECT 'incidents', COUNT(*) FROM risk_incidents WHERE incident_code LIKE 'DUMMY-INC-%'
UNION ALL SELECT 'vendors', COUNT(*) FROM vendors WHERE name LIKE 'DUMMY Vendor %'
UNION ALL SELECT 'scenarios', COUNT(*) FROM risk_scenarios WHERE scenario_code LIKE 'DUMMY-SCN-%'
UNION ALL SELECT 'compliance work programs', COUNT(*) FROM compliance_work_programs WHERE work_id LIKE 'DUMMY-CWP-%';

-- To remove only this dummy dataset later, run database/cleanup_dummy_rms.sql.
-- The cleanup section at the beginning makes this seed repeatable when the
-- seed file itself is run again. Do not use migrate:fresh for this purpose
-- because that destroys non-dummy data as well.
