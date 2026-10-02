# Total Dashboard Overhaul & Command Center Architecture

## 1. Inventory of Existing Dashboard-like Views (Before Overhaul)

Prior to this overhaul, the application suffered from severe dashboard sprawl: **14 disconnected views** competed for user attention, causing cognitive overload, fragmented navigation, and inconsistent risk metric reporting.

| # | View / Route | Purpose as Implemented | Target Audience | Primary Widgets & Metrics | Active Filters | Data Sources | Navigation Path |
|---|---|---|---|---|---|---|---|
| **1** | `/dashboard` | Executive summary landing | Board, Senior Management | Macro score, priority distribution, top 5 risks, posture ring | View mode (`summary`, `monitoring`) | `RiskController@dashboard`, `RiskMetricService` | Nav > Dashboard > Executive Summary |
| **2** | `/dashboard-monitoring` | Operational dashboard with tabbed widgets | Risk Officers, Department Heads | Core status counts, SLA gauges, department rankings, benchmark scoring | Category, Cause Category, Granularity | `RiskController@managementDashboard`, `Risk` model | Nav > Dashboard > Operational Monitoring |
| **3** | `/risk-monitoring` | Duplicate operational view pointing to same blade | Risk Champions | Identical to `/dashboard-monitoring` | Same | Same | Alias route |
| **4** | `/risk-heatmap` | 5x5 Likelihood x Impact matrix | Risk Analysts, Committee | 5x5 grid cells with risk event tags | None (global scope) | `RiskScoreMatrix`, `RiskInherentAnalysis`, `RiskResidualAnalysis` | Nav > Risk Register > [Heatmap button] |
| **5** | `/risk-recap` | Annual risk portfolio recap | Risk Committee, Compliance | Year-based radar groups, inherent vs residual delta | Year selector | `RadarGroup`, `Risk` | Nav > Risk Register > [Recap button] |
| **6** | `/trends/analysis` | Temporal trend visualizer | CRO, Analysts | Multi-period trend lines (monthly, quarterly, yearly), moving averages | Time window, owner, granularity | `TrendAnalysisController`, `RiskMetricService` | Nav > Risk > Risk Trend Analysis |
| **7** | `/kris` | Key Risk Indicator monitor | Risk Operators | Indicator readings, green/amber/red status, thresholds | Department, Status | `Kri`, `KriLog` | Nav > Risk > Key Risk Indicators |
| **8** | `/incidents` | Loss event triage & tracking | Ops Risk, Audit | Incident register, severity tiers, Capa review status | Severity, Status | `RiskIncident`, `RiskIncidentAction` | Nav > Risk > Incident Management |
| **9** | `/treatments/kanban` | Mitigation task tracking | Risk Owners | Kanban columns: Proposed, Open, In Progress, Closed | Risk Owner | `RiskTreatment`, `Risk` | Nav > Risk > Mitigation Kanban |
| **10**| `/owners/workload` | Department workload review | Department Heads | Assigned risks per owner, completion rate, overdue items | Division | `User`, `Risk`, `RiskTreatment` | Nav > Risk > Owner Workload |
| **11**| `/enterprise-risk-profile` | Corporate profile & decisions | Board, ExCo | Risk concentration radar, Committee decisions log | Committee meeting | `RiskCommitteeDecision`, `Risk` | **Orphaned** (unlinked in sidebar) |
| **12**| `/strategic-risk-reviews` | Scenario stress test & maturity | Board Risk Committee | Macro stress scenarios, maturity dimension radar | Scenario type | `RiskScenario`, `RiskMaturityAssessment` | **Orphaned** (unlinked in sidebar) |
| **13**| `/audit-findings` (audit placeholder) | Audit findings tracking | Internal Audit | Audit status, non-conformity severity, remediation timeline | Status, Division | `ComplianceWorkProgram`, `AuditLog` | Nav > Audit (Compliance only) |
| **14**| `/operational-resilience` | BCP/DR restore testing | IT Ops, BCM | Backup records, restore test verification, RTO/RPO stats | System tier | `ResilienceBackupRecord`, `ResilienceRestoreTest` | **Orphaned** (unlinked in sidebar) |

---

## 2. First-Principles Dashboard Architecture

### The Governing Principle
> **"Something earns its own top-level dashboard if and only if it serves a distinct audience, answers a distinct core question, and operates on a distinct decision cadence. Otherwise, it must be integrated as a tab, filter, or contextual drill-down."**

Applying this test eliminates dashboard fragmentation and defines exactly **three cohesive Command Center tiers**:

```
+-----------------------------------------------------------------------------------+
|                        ENTERPRISE RISK COMMAND CENTER HIERARCHY                   |
+-----------------------------------------------------------------------------------+
|                                                                                   |
|  [TIER 1: STRATEGIC COMMAND CENTER]                                               |
|  Audience:  Board of Directors, Board Risk Committee, CEO, CRO                   |
|  Question:  "Is enterprise risk within approved appetite, where are systemic      |
|             threats, and are executive committee decisions being executed?"       |
|  Cadence:   Monthly / Quarterly Governance Cycle                                  |
|  Access:    /dashboard?view=strategic (Default landing)                           |
|                                                                                   |
|        │                                                                          |
|        ▼ (Drill down to actionable failures)                                      |
|                                                                                   |
|  [TIER 2: OPERATIONAL COMMAND CENTER]                                             |
|  Audience:  Risk Champions, Department Heads, 1st & 2nd Line Operators            |
|  Question:  "What operational failures, breached KRIs, overdue mitigations,      |
|             or open incidents need immediate action today?"                       |
|  Cadence:   Daily / Weekly Operational Standup                                    |
|  Access:    /dashboard?view=operational (Alias: /dashboard-monitoring)            |
|                                                                                   |
|        │                                                                          |
|        ▼ (Deep-dive into root causes and control efficacy)                        |
|                                                                                   |
|  [TIER 3: ANALYTICAL RISK INTELLIGENCE CENTER]                                    |
|  Audience:  Enterprise Risk Analysts, CRO, Internal Auditors, Regulators         |
|  Question:  "Where are systemic vulnerabilities, root causes, control efficacy   |
|             variances, and how do we benchmark against national standards?"       |
|  Cadence:   Ad-hoc, Audit Investigation, Regulatory Reporting                     |
|  Access:    /dashboard?view=analytical (Interactive 5x5 Matrix)                   |
|                                                                                   |
+-----------------------------------------------------------------------------------+
```

---

## 3. Information Architecture & Drill-Down Flow

```
========================================================================================
TIER 1: STRATEGIC RISK COMMAND CENTER (?view=strategic)
========================================================================================
[ Strategic Posture Hero Banner ]
  ├── Average Residual Score (with Inherent Baseline & Net Reduction)
  ├── Strategic Governance Posture Badge: Under Control vs Attention Required vs Breach
  └── Corporate Risk Appetite Gauge (Approved Limit vs Net Residual Exposure)

[ 4 Strategic Macro KPIs ]
  ├── Total Enterprise Portfolio Risks (-> Drill-down to Risk Register)
  ├── High & Extreme Systemic Exposure Count & Portfolio % (-> Immediate Tier 1 Focus)
  ├── Overdue Strategic Mitigations SLA Count (-> Drill-down to Kanban)
  └── Portfolio Mitigation SLA Compliance % vs 95% Benchmark

[ Posture & Priority Tier Breakdown ]
  ├── Circular Posture Ring (% of portfolio requiring executive attention)
  └── ISO 31000 Priority Tier Distribution (Low, Moderate, High, Extreme bars with counts & %)

[ Executive Action & Decisions Matrix ]
  ├── Management Attention List (Top 5 Systemic Risks with owners & control focus)
  └── Board/Committee Decisions Follow-Through (Active directives, target dates, owners)

[ Multi-Period Strategic Trajectory ]
  └── 6-Quarter Historical Exposure Trajectory (Demonstrating control effectiveness over time)

                               │
                               ▼ [Click "Operational View" Tab or Drill-down]
========================================================================================
TIER 2: OPERATIONAL RISK COMMAND CENTER (?view=operational)
========================================================================================
[ Operational Health Warning Strip (4 Alert Cards) ]
  ├── Red/Amber KRI Breaches (-> Jump to Key Risk Indicators /kris)
  ├── Overdue Action Plans SLA Breach (-> Jump to Mitigation Kanban /treatments/kanban)
  ├── Active Loss Events & Incidents (-> Jump to Incident Center /incidents)
  └── Risk Control Treatment Coverage % (-> Jump to Risk Register /risks)

[ Early-Warning KRI Monitor Table ]
  └── Indicator name, current value, warn/critical thresholds, Green/Amber/Red status badge

[ Operational Incident Triage ]
  └── Active incidents, severity (Critical/High/Med), detection time, mitigation status

[ Department Operational Workload & Rank Table ]
  └── Division name, active risks, closure rate %, avg score, SLA response %, composite score

                               │
                               ▼ [Click "Analytical View" Tab or Drill-down]
========================================================================================
TIER 3: ANALYTICAL INTELLIGENCE CENTER (?view=analytical)
========================================================================================
[ Interactive 5x5 Heatmap Matrix ]
  ├── Toggle between Residual (Net) and Inherent (Gross) risk distributions
  ├── 25 Cells (Likelihood 1-5 x Impact 1-5) colored by priority tier
  └── Direct clickable counts in each cell filtering to the Risk Register

[ Control Effectiveness Variance Breakdown ]
  └── Category-by-category waterfall: Gross Inherent -> Net Residual -> Net Control Reduction

[ International Regulatory Benchmark (NHSCFA Guidance) ]
  └── Internal Average vs UK NHS CFA 12.0 Escalation Baseline vs 9.0 Target Score

[ Root Cause & Systemic Vulnerability Pareto ]
  ├── Dominant Cause Categories (Process, People, Systems, External) with share %
  ├── High-Frequency NLP Cause Narrative Keywords
  └── Automated Systemic Recommendations mapped to ISO 31000 principles
```

---

## 4. What Changed (Implementation Details)

### 4.1. Single Source of Truth
- All metrics across all 3 Command Centers adhere 100% to `app/Support/RiskMetricService.php`.
- Display score resolution hierarchy strictly maintained: `Residual Risk Score -> Inherent Risk Score -> Base Risk Score`.
- Priority bucket thresholds unified across all views:
  - **Low**: $1.0 \le \text{Score} \le 5.0$ (`#56a86b` / `bg-success`)
  - **Moderate**: $5.1 \le \text{Score} \le 10.0$ (`#e0ae42` / `bg-warning`)
  - **High**: $10.1 \le \text{Score} \le 15.0$ (`#e47d34` / `bg-orange`)
  - **Extreme**: $15.1 \le \text{Score} \le 25.0$ (`#c84b4b` / `bg-danger`)

### 4.2. File Changes & Additions
1. **`resources/views/risks/partials/strategic-dashboard.blade.php`** *(New)*:
   - Houses the Executive Strategic Command Center for Board and senior leadership.
   - Includes real corporate risk appetite comparison against active `RiskAppetite` limits.
   - Embeds follow-through on Board directives from `RiskCommitteeDecision`.
2. **`resources/views/risks/partials/operational-dashboard.blade.php`** *(New)*:
   - Houses the Operational Risk Command Center.
   - Integrates live early-warning alerts from `Kri`, unresolved incidents from `RiskIncident`, and SLA metrics from `RiskTreatment`.
   - Ranks department risk management performance with composite scores.
3. **`resources/views/risks/partials/analytical-dashboard.blade.php`** *(New)*:
   - Houses the Analytical Intelligence Center.
   - Implements interactive 5x5 heatmap with instant Inherent vs Residual toggling.
   - Displays category-level control variance and NLP root cause keyword analytics.
4. **`resources/views/risks/unified-dashboard.blade.php`** *(Overhauled)*:
   - Replaced fragmented dual-view layout with the 3-Tier Command Center switcher.
   - Zero-reload client-side switching using `window.switchDashboardView()` with URL synchronization.
   - Added instant category and cause category filters in header.
5. **`app/Http/Controllers/RiskController.php`** *(Updated)*:
   - Implemented `buildUnifiedDashboardContext()` to supply 5x5 matrix distribution, active committee decisions, KRI breach alerts, open incidents, and active appetites without N+1 query overhead.
   - Updated `dashboard()`, `monitoring()`, and `managementDashboard()` to validate and route all 3 views cleanly while preserving legacy aliases (`summary`, `monitoring`).
6. **`resources/views/layouts/tabler.blade.php`** *(Updated)*:
   - Fixed unescaped trailing quote attribute syntax error on lines 1003 and 1098 (`data-dashboard-menu">` -> `data-dashboard-menu>`).
   - Updated Dashboard menu to expose the 3 Command Centers clearly.
   - Reconnected all 7 previously orphaned modules into proper navigation sections without visual clutter.

### 4.3. Backward Compatibility Matrix
| Incoming Request / Route | Resolved View | Behavior |
|---|---|---|
| `/dashboard` | `strategic` | Renders Strategic Command Center |
| `/dashboard?view=summary` | `strategic` | Backwards compatible alias |
| `/dashboard?view=strategic` | `strategic` | Direct Strategic View |
| `/dashboard-monitoring` | `operational` | Renders Operational Command Center |
| `/risk-monitoring` | `operational` | Backwards compatible alias |
| `/dashboard?view=monitoring` | `operational` | Backwards compatible alias |
| `/dashboard?view=operational` | `operational` | Direct Operational View |
| `/dashboard?view=analytical` | `analytical` | Direct Analytical View |
| `/risk-heatmap` | Dedicated | Preserved for standalone heatmap exports |
| `/risk-recap` | Dedicated | Preserved for annual recap audits |
