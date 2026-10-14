<?php

namespace App\Console\Commands;

use App\BusinessService;
use App\Risk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Creates a DRAFT catalogue of business services from the risk register's
 * operating units, and links each unit's risks to its service. Units then
 * confirm or correct the draft. Only drafts without BIA/BCP/tests are ever
 * touched or removed.
 */
class SeedServiceCatalog extends Command
{
    protected $signature = 'bcm:seed-service-catalog {--rollback : Remove untouched draft services created by this command}';

    protected $description = 'Create a draft business service catalogue from the risk register';

    /** code => [name, owner unit, description] */
    private const CATALOGUE = [
        'SVC-001' => ['Core Banking Konvensional', 'BT - CORE BANKING SYSTEM CONVENTIONAL', 'Pemrosesan transaksi, rekening, GL, dan EOD core banking konvensional untuk bank klien.'],
        'SVC-002' => ['Core Banking Syariah', 'BT - CORE BANKING SYSTEM SYARIAH', 'Pemrosesan akad, bagi hasil, dan transaksi core banking syariah untuk bank klien.'],
        'SVC-003' => ['Aplikasi Perbankan (Kredit, Pembayaran, Treasury)', 'BT - BANKING APPLICATIONS', 'Loan origination, integrasi pembayaran, dan modul perbankan pendukung core.'],
        'SVC-004' => ['Digital Banking (Mobile & Internet Banking)', 'BT - DIGITAL BANKING', 'Kanal mobile/internet banking, QRIS, BI-FAST, dan SNAP BI untuk nasabah.'],
        'SVC-005' => ['Platform Solusi Digital & API', 'DI - DIGITAL SOLUTION', 'API gateway, onboarding digital, dan layanan open API.'],
        'SVC-006' => ['Switching & Middleware', 'IF - SWITCHING & MIDDLEWARE', 'Switching ISO 8583, ESB, message broker, dan antarmuka jaringan pembayaran.'],
        'SVC-007' => ['Aplikasi Pendukung (CRM, AML, DMS, EDW)', 'BT - SURROUNDING APPLICATION', 'Aplikasi pendukung di sekitar core banking.'],
        'SVC-008' => ['Operasional Transaksi & Settlement', 'OM - BUSINESS & DIGITAL OPERATION', 'Rekonsiliasi, settlement, kliring, dan penanganan dispute transaksi.'],
        'SVC-009' => ['Pelaporan Regulator', 'PM - REGULATORY & FINANCIAL REPORTING', 'Penyusunan dan pengiriman laporan ke OJK, BI, dan PPATK.'],
        'SVC-010' => ['Layanan Nasabah & Call Center', 'CR - CUSTOMER RELATIONS', 'Call center, IVR, chatbot, dan penanganan pengaduan nasabah.'],
        'SVC-011' => ['Infrastruktur Data Center', 'OM - DATA CENTER OPERATION', 'Daya, pendingin, proteksi kebakaran, dan akses fisik data center.'],
        'SVC-012' => ['Jaringan & Infrastruktur TI', 'IF - INFRASTRUCTURE', 'Jaringan, konektivitas ISP, storage, dan platform infrastruktur.'],
        'SVC-013' => ['Operasi & Monitoring TI', 'OM - IT OPERATION MANAGEMENT', 'Change, patch, monitoring, dan operasi harian sistem TI.'],
        'SVC-014' => ['Keamanan Siber (SOC)', 'CS - CYBER SECURITY', 'Deteksi, respons, dan perlindungan terhadap serangan siber.'],
    ];

    public function handle(): int
    {
        if ($this->option('rollback')) {
            return $this->rollback();
        }

        $created = 0;
        $linked = 0;
        DB::transaction(function () use (&$created, &$linked) {
            foreach (self::CATALOGUE as $code => [$name, $unit, $description]) {
                $service = BusinessService::query()->firstOrCreate(
                    ['code' => $code],
                    ['name' => $name, 'owner_unit' => $unit, 'description' => $description, 'status' => 'draft']
                );
                if ($service->wasRecentlyCreated) {
                    $created++;
                }
                if ($service->status !== 'draft') {
                    continue; // Confirmed by the unit: leave its risk links alone.
                }

                $riskIds = Risk::query()->where('risk_owner', $unit)
                    ->whereRaw("LOWER(COALESCE(status, 'open')) <> 'closed'")
                    ->pluck('id');
                $linked += count($service->risks()->syncWithoutDetaching($riskIds)['attached']);
            }
        });

        $this->info("Draft service catalogue: {$created} service(s) created, {$linked} risk link(s) added.");
        $this->line('Shared infrastructure (data center, network, SOC) also supports other services; units should add those links when confirming.');

        return self::SUCCESS;
    }

    private function rollback(): int
    {
        $removed = BusinessService::query()
            ->whereIn('code', array_keys(self::CATALOGUE))
            ->where('status', 'draft')
            ->doesntHave('bia')->doesntHave('bcp')->doesntHave('tests')
            ->get()
            ->each(fn ($service) => $service->delete())
            ->count();

        $this->info("Removed {$removed} untouched draft service(s).");

        return self::SUCCESS;
    }
}
