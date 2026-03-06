<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ThreatIntelligencePageTest extends TestCase
{
    protected function tearDown(): void
    {
        Cache::flush();

        parent::tearDown();
    }

    public function test_verified_user_can_view_threat_intelligence_dashboard(): void
    {
        Cache::flush();
        Http::fake([
            'https://services.nvd.nist.gov/rest/json/cves/2.0*' => Http::response([
                'totalResults' => 1,
                'vulnerabilities' => [[
                    'cve' => [
                        'id' => 'CVE-2026-0001',
                        'published' => '2026-03-05T08:00:00.000',
                        'lastModified' => '2026-03-06T08:00:00.000',
                        'vulnStatus' => 'Analyzed',
                        'descriptions' => [
                            ['lang' => 'en', 'value' => 'Laravel demo remote code execution vulnerability.'],
                        ],
                        'metrics' => [
                            'cvssMetricV31' => [[
                                'type' => 'Primary',
                                'cvssData' => [
                                    'baseScore' => 9.8,
                                    'baseSeverity' => 'CRITICAL',
                                    'vectorString' => 'CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H',
                                ],
                            ]],
                        ],
                        'weaknesses' => [[
                            'description' => [
                                ['lang' => 'en', 'value' => 'CWE-94'],
                            ],
                        ]],
                        'references' => [[
                            'url' => 'https://example.test/advisories/CVE-2026-0001',
                        ]],
                    ],
                ]],
            ], 200),
            'https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json' => Http::response([
                'catalogVersion' => '2026.03.06',
                'dateReleased' => '2026-03-06T00:00:00Z',
                'vulnerabilities' => [[
                    'cveID' => 'CVE-2026-0001',
                    'vendorProject' => 'Laravel',
                    'product' => 'Framework',
                    'vulnerabilityName' => 'Laravel demo RCE',
                    'dateAdded' => '2026-03-06',
                    'dueDate' => '2026-03-21',
                    'shortDescription' => 'Exploit observed in the wild.',
                    'requiredAction' => 'Apply vendor update immediately.',
                    'knownRansomwareCampaignUse' => 'Known',
                    'notes' => 'Tracked for security operations.',
                ]],
            ], 200),
        ]);

        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $response = $this->get(route('threat-intelligence.index', [
            'keyword' => 'Laravel',
            'severity' => 'CRITICAL',
            'days' => 30,
            'limit' => 12,
        ]));

        $response->assertOk();
        $response->assertSee('Threat Intelligence');
        $response->assertSee('KEV Matches');
        $response->assertSee('Data Sources');
        $response->assertSee('NVD CVE API');
        $response->assertSee('CISA KEV Catalog');
        $response->assertSee('CVE-2026-0001');
        $response->assertSee('Known exploited');
        $response->assertSee('Laravel demo RCE');
    }

    public function test_kev_feed_falls_back_to_github_mirror_when_primary_feed_fails(): void
    {
        Cache::flush();
        Http::fake([
            'https://services.nvd.nist.gov/rest/json/cves/2.0*' => Http::response([
                'totalResults' => 0,
                'vulnerabilities' => [],
            ], 200),
            'https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json' => Http::response([], 503),
            'https://raw.githubusercontent.com/cisagov/kev-data/develop/known_exploited_vulnerabilities.json' => Http::response([
                'catalogVersion' => '2026.03.06',
                'dateReleased' => '2026-03-06T00:00:00Z',
                'vulnerabilities' => [[
                    'cveID' => 'CVE-2025-7777',
                    'vendorProject' => 'Fortinet',
                    'product' => 'FortiOS',
                    'vulnerabilityName' => 'Fortinet auth bypass',
                    'dateAdded' => '2026-03-06',
                    'dueDate' => '2026-03-20',
                    'shortDescription' => 'Mirror catalog entry.',
                    'requiredAction' => 'Update immediately.',
                    'knownRansomwareCampaignUse' => 'Unknown',
                    'notes' => '',
                ]],
            ], 200),
        ]);

        $user = $this->createUser('BT - DIGITAL BANKING', false);
        $this->actingAs($user);

        $response = $this->get(route('threat-intelligence.index'));

        $response->assertOk();
        $response->assertSee('Fortinet auth bypass');
        $response->assertSee('GitHub mirror fallback');
        $response->assertSee('CISA KEV JSON Mirror');
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        $user = new User([
            'name' => 'Threat Intel User ' . $suffix,
            'username' => 'threat_' . $suffix,
            'division' => $division,
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => 'password123',
        ]);

        $user->id = random_int(1000, 9999);

        return $user;
    }
}
