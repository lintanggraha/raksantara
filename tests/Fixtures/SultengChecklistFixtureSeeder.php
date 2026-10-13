<?php

namespace Tests\Fixtures;

use App\SultengChecklistItem;
use Illuminate\Database\Seeder;

/**
 * Generic, non-sensitive Sulteng checklist used by the test suite. The real
 * investigation seeder stays outside version control.
 */
class SultengChecklistFixtureSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'A' => [
                'name' => 'Firewall dan WAF',
                'items' => [
                    [1, 'Rule Firewall', 'Infra'],
                    [2, 'Log Firewall', 'Infra'],
                    [3, 'Rule Web Application Firewall (WAF)', 'Infra'],
                    [4, 'Log Web Application Firewall (WAF)', 'Infra'],
                ],
            ],
            'B' => [
                'name' => 'Server Aplikasi',
                'items' => [
                    [1, 'Konfigurasi Hardening Server', 'Infra'],
                    [2, 'Log Akses Server', 'Security'],
                ],
            ],
        ];

        $sort = 1;
        foreach ($categories as $categoryCode => $category) {
            foreach ($category['items'] as [$number, $title, $pic]) {
                SultengChecklistItem::updateOrCreate(
                    ['code' => "{$categoryCode}.{$number}"],
                    [
                        'category_code' => $categoryCode,
                        'category_name' => $category['name'],
                        'item_number' => $number,
                        'title' => $title,
                        'clean_title' => SultengChecklistItem::sanitizeTitleForFilename($title),
                        'period' => '1–10 Januari 2026',
                        'pic_division' => $pic,
                        'status' => 'Belum Diterima',
                        'sort_order' => $sort++,
                    ]
                );
            }
        }
    }
}
