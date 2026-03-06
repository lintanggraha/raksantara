<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ThreatIntelligenceService
{
    private const DEFAULT_DAYS = 30;
    private const DEFAULT_LIMIT = 12;
    private const CACHE_TTL_MINUTES = 15;

    public function buildDashboard(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);

        $kevCatalog = $this->fetchKevCatalog();
        $filteredKevItems = $this->filterKevItems($kevCatalog['items'], $filters['keyword'], $filters['limit']);
        $kevIndex = collect($kevCatalog['items'])
            ->filter(function (array $item) {
                return $item['cve_id'] !== '';
            })
            ->keyBy('cve_id');

        $nvdFeed = $this->fetchNvdFeed($filters);
        $cves = collect($nvdFeed['items'])
            ->map(function (array $item) use ($kevIndex) {
                $kevMatch = $kevIndex->get($item['cve_id']);

                $item['is_known_exploited'] = !is_null($kevMatch);
                $item['kev_summary'] = is_array($kevMatch) ? [
                    'date_added' => $kevMatch['date_added'],
                    'due_date' => $kevMatch['due_date'],
                    'required_action' => $kevMatch['required_action'],
                    'known_ransomware_campaign_use' => $kevMatch['known_ransomware_campaign_use'],
                ] : null;

                return $item;
            })
            ->filter(function (array $item) use ($filters) {
                if ($filters['severity'] === '') {
                    return true;
                }

                return $item['severity'] === $filters['severity'];
            })
            ->take($filters['limit'])
            ->values()
            ->all();

        return [
            'filters' => $filters,
            'summary' => [
                'displayed_cves' => count($cves),
                'critical_cves' => count(array_filter($cves, function (array $item) {
                    return $item['severity'] === 'CRITICAL';
                })),
                'known_exploited_on_page' => count(array_filter($cves, function (array $item) {
                    return (bool) ($item['is_known_exploited'] ?? false);
                })),
                'kev_items' => count($filteredKevItems),
                'ransomware_flagged' => count(array_filter($filteredKevItems, function (array $item) {
                    return strtoupper((string) ($item['known_ransomware_campaign_use'] ?? '')) === 'KNOWN';
                })),
                'high_or_critical' => count(array_filter($cves, function (array $item) {
                    return in_array($item['severity'], ['CRITICAL', 'HIGH'], true);
                })),
                'kev_match_ratio' => count($cves) > 0
                    ? (int) round((count(array_filter($cves, function (array $item) {
                        return (bool) ($item['is_known_exploited'] ?? false);
                    })) / count($cves)) * 100)
                    : 0,
                'nvd_total_results' => (int) ($nvdFeed['total_results'] ?? 0),
            ],
            'analytics' => [
                'severity_breakdown' => $this->buildSeverityBreakdown($cves),
                'publication_trend' => $this->buildPublicationTrend($cves),
                'vendor_exposure' => $this->buildVendorExposure($filteredKevItems),
                'priority_queue' => $this->buildPriorityQueue($cves),
            ],
            'cves' => $cves,
            'kev' => [
                'items' => $filteredKevItems,
                'catalog_version' => $kevCatalog['catalog_version'],
                'released_at' => $kevCatalog['released_at'],
                'count' => count($filteredKevItems),
            ],
            'sources' => [
                'nvd' => $nvdFeed['source'],
                'kev' => $kevCatalog['source'],
            ],
            'generated_at' => now()->format('d M Y H:i'),
        ];
    }

    private function normalizeFilters(array $filters): array
    {
        $severity = strtoupper(trim((string) ($filters['severity'] ?? '')));
        $allowedSeverities = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'];

        if (!in_array($severity, $allowedSeverities, true)) {
            $severity = '';
        }

        $days = (int) ($filters['days'] ?? self::DEFAULT_DAYS);
        if (!in_array($days, [7, 30, 90], true)) {
            $days = self::DEFAULT_DAYS;
        }

        $limit = (int) ($filters['limit'] ?? self::DEFAULT_LIMIT);
        if (!in_array($limit, [10, 12, 20, 50], true)) {
            $limit = self::DEFAULT_LIMIT;
        }

        return [
            'keyword' => trim((string) ($filters['keyword'] ?? '')),
            'severity' => $severity,
            'days' => $days,
            'limit' => $limit,
        ];
    }

    private function fetchNvdFeed(array $filters): array
    {
        $cacheKey = 'threat-intel:nvd:' . md5(json_encode($filters));
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $apiKey = trim((string) config('services.nvd.api_key'));
        $querySize = max(20, min($filters['limit'] * 4, 100));
        $request = Http::acceptJson()
            ->timeout(12)
            ->retry(2, 250);

        if ($apiKey !== '') {
            $request = $request->withHeaders([
                'apiKey' => $apiKey,
            ]);
        }

        try {
            $response = $request->get((string) config('services.nvd.base_url'), [
                'resultsPerPage' => $querySize,
                'pubStartDate' => now()->subDays($filters['days'])->startOfDay()->toIso8601String(),
                'pubEndDate' => now()->endOfDay()->toIso8601String(),
                'keywordSearch' => $filters['keyword'] !== '' ? $filters['keyword'] : null,
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException('NVD API returned HTTP ' . $response->status());
            }

            $payload = $response->json();
            $result = [
                'items' => collect((array) data_get($payload, 'vulnerabilities', []))
                    ->map(function ($item) {
                        return $this->mapNvdItem((array) $item);
                    })
                    ->filter(function (array $item) {
                        return $item['cve_id'] !== '';
                    })
                    ->values()
                    ->all(),
                'total_results' => (int) data_get($payload, 'totalResults', 0),
                'source' => [
                    'label' => 'NVD CVE API',
                    'status' => 'ok',
                    'url' => 'https://nvd.nist.gov/developers/vulnerabilities',
                    'note' => $apiKey !== '' ? 'API key configured' : 'Running on public quota',
                ],
            ];

            Cache::put($cacheKey, $result, now()->addMinutes(self::CACHE_TTL_MINUTES));

            return $result;
        } catch (\Throwable $e) {
            return [
                'items' => [],
                'total_results' => 0,
                'source' => [
                    'label' => 'NVD CVE API',
                    'status' => 'error',
                    'url' => 'https://nvd.nist.gov/developers/vulnerabilities',
                    'note' => $e->getMessage(),
                ],
            ];
        }
    }

    private function fetchKevCatalog(): array
    {
        $cacheKey = 'threat-intel:kev';
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $urls = array_values(array_filter([
            trim((string) config('services.cisa.kev_url')),
            trim((string) config('services.cisa.kev_mirror_url')),
        ]));

        $lastError = 'KEV feed is not configured.';

        foreach ($urls as $index => $url) {
            try {
                $response = Http::acceptJson()
                    ->timeout(12)
                    ->retry(1, 250)
                    ->get($url);

                if (!$response->successful()) {
                    throw new \RuntimeException('KEV feed returned HTTP ' . $response->status());
                }

                $payload = (array) $response->json();
                $result = [
                    'items' => collect((array) data_get($payload, 'vulnerabilities', []))
                        ->map(function ($item) {
                            return $this->mapKevItem((array) $item);
                        })
                        ->filter(function (array $item) {
                            return $item['cve_id'] !== '';
                        })
                        ->sortByDesc('date_added_sort')
                        ->values()
                        ->all(),
                    'catalog_version' => (string) data_get($payload, 'catalogVersion', '-'),
                    'released_at' => $this->formatDate(data_get($payload, 'dateReleased')),
                    'source' => [
                        'label' => 'CISA Known Exploited Vulnerabilities',
                        'status' => 'ok',
                        'url' => $url,
                        'note' => $index === 0 ? 'Primary feed' : 'GitHub mirror fallback',
                    ],
                ];

                Cache::put($cacheKey, $result, now()->addMinutes(self::CACHE_TTL_MINUTES));

                return $result;
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
            }
        }

        return [
            'items' => [],
            'catalog_version' => '-',
            'released_at' => '-',
            'source' => [
                'label' => 'CISA Known Exploited Vulnerabilities',
                'status' => 'error',
                'url' => 'https://www.cisa.gov/known-exploited-vulnerabilities-catalog',
                'note' => $lastError,
            ],
        ];
    }

    private function filterKevItems(array $items, string $keyword, int $limit): array
    {
        return collect($items)
            ->filter(function (array $item) use ($keyword) {
                if ($keyword === '') {
                    return true;
                }

                $haystack = implode(' ', [
                    (string) ($item['cve_id'] ?? ''),
                    (string) ($item['vendor_project'] ?? ''),
                    (string) ($item['product'] ?? ''),
                    (string) ($item['vulnerability_name'] ?? ''),
                    (string) ($item['short_description'] ?? ''),
                    (string) ($item['notes'] ?? ''),
                ]);

                return str_contains(mb_strtolower($haystack), mb_strtolower($keyword));
            })
            ->take($limit)
            ->values()
            ->all();
    }

    private function mapNvdItem(array $item): array
    {
        $cve = (array) ($item['cve'] ?? []);
        $metrics = $this->extractMetricSummary((array) ($cve['metrics'] ?? []));

        return [
            'cve_id' => trim((string) ($cve['id'] ?? '')),
            'description' => $this->extractEnglishDescription((array) ($cve['descriptions'] ?? [])),
            'severity' => $metrics['severity'],
            'base_score' => $metrics['base_score'],
            'vector' => $metrics['vector'],
            'published_date_key' => $this->sortableDate($cve['published'] ?? null),
            'published_at' => $this->formatDate($cve['published'] ?? null),
            'modified_at' => $this->formatDate($cve['lastModified'] ?? null),
            'vuln_status' => trim((string) ($cve['vulnStatus'] ?? '-')),
            'cwes' => $this->extractCwes((array) ($cve['weaknesses'] ?? [])),
            'detail_url' => 'https://nvd.nist.gov/vuln/detail/' . trim((string) ($cve['id'] ?? '')),
            'reference_url' => $this->extractReferenceUrl((array) ($cve['references'] ?? [])),
        ];
    }

    private function buildSeverityBreakdown(array $cves): array
    {
        $levels = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW', 'UNKNOWN'];
        $values = [];

        foreach ($levels as $level) {
            $values[] = count(array_filter($cves, function (array $item) use ($level) {
                return ($item['severity'] ?? 'UNKNOWN') === $level;
            }));
        }

        return [
            'labels' => ['Critical', 'High', 'Medium', 'Low', 'Unknown'],
            'values' => $values,
        ];
    }

    private function buildPublicationTrend(array $cves): array
    {
        $grouped = collect($cves)
            ->filter(function (array $item) {
                return trim((string) ($item['published_date_key'] ?? '')) !== '';
            })
            ->groupBy('published_date_key')
            ->sortKeys();

        return [
            'labels' => $grouped->keys()->map(function (string $dateKey) {
                return Carbon::parse($dateKey)->format('d M');
            })->values()->all(),
            'values' => $grouped->map(function ($items) {
                return $items->count();
            })->values()->all(),
        ];
    }

    private function buildVendorExposure(array $kevItems): array
    {
        $topVendors = collect($kevItems)
            ->map(function (array $item) {
                $vendor = trim((string) ($item['vendor_project'] ?? 'Unknown'));

                return $vendor === '' ? 'Unknown' : $vendor;
            })
            ->countBy()
            ->sortDesc()
            ->take(6);

        return [
            'labels' => $topVendors->keys()->values()->all(),
            'values' => $topVendors->values()->values()->all(),
        ];
    }

    private function buildPriorityQueue(array $cves): array
    {
        return collect($cves)
            ->sort(function (array $left, array $right) {
                return $this->priorityScore($right) <=> $this->priorityScore($left);
            })
            ->take(5)
            ->map(function (array $item) {
                $reasons = [];

                if (($item['is_known_exploited'] ?? false) === true) {
                    $reasons[] = 'Known exploited';
                }

                if (($item['severity'] ?? '') === 'CRITICAL') {
                    $reasons[] = 'Critical severity';
                } elseif (($item['severity'] ?? '') === 'HIGH') {
                    $reasons[] = 'High severity';
                }

                if (!is_null($item['base_score'])) {
                    $reasons[] = 'CVSS ' . $item['base_score'];
                }

                return array_merge($item, [
                    'priority_label' => $this->resolvePriorityLabel($item),
                    'priority_reasons' => $reasons,
                ]);
            })
            ->values()
            ->all();
    }

    private function priorityScore(array $item): int
    {
        $score = 0;

        if (($item['is_known_exploited'] ?? false) === true) {
            $score += 1000;
        }

        $severity = trim((string) ($item['severity'] ?? 'UNKNOWN'));
        $score += match ($severity) {
            'CRITICAL' => 400,
            'HIGH' => 250,
            'MEDIUM' => 125,
            'LOW' => 50,
            default => 0,
        };

        $baseScore = (float) ($item['base_score'] ?? 0);
        $score += (int) round($baseScore * 10);

        $publishedDateKey = trim((string) ($item['published_date_key'] ?? ''));
        if ($publishedDateKey !== '') {
            $daysOld = abs((int) now()->startOfDay()->diffInDays(Carbon::parse($publishedDateKey), false));
            $score += max(0, 60 - $daysOld);
        }

        return $score;
    }

    private function resolvePriorityLabel(array $item): string
    {
        if (($item['is_known_exploited'] ?? false) === true && in_array(($item['severity'] ?? ''), ['CRITICAL', 'HIGH'], true)) {
            return 'Immediate';
        }

        if (($item['is_known_exploited'] ?? false) === true) {
            return 'Urgent';
        }

        if (($item['severity'] ?? '') === 'CRITICAL') {
            return 'Review now';
        }

        if (($item['severity'] ?? '') === 'HIGH') {
            return 'Watch closely';
        }

        return 'Monitor';
    }

    private function mapKevItem(array $item): array
    {
        return [
            'cve_id' => trim((string) ($item['cveID'] ?? '')),
            'vendor_project' => trim((string) ($item['vendorProject'] ?? '-')),
            'product' => trim((string) ($item['product'] ?? '-')),
            'vulnerability_name' => trim((string) ($item['vulnerabilityName'] ?? '-')),
            'short_description' => trim((string) ($item['shortDescription'] ?? '')),
            'required_action' => trim((string) ($item['requiredAction'] ?? '')),
            'date_added' => $this->formatDate($item['dateAdded'] ?? null),
            'date_added_sort' => $this->sortableDate($item['dateAdded'] ?? null),
            'due_date' => $this->formatDate($item['dueDate'] ?? null),
            'known_ransomware_campaign_use' => trim((string) ($item['knownRansomwareCampaignUse'] ?? 'Unknown')),
            'notes' => trim((string) ($item['notes'] ?? '')),
            'detail_url' => 'https://nvd.nist.gov/vuln/detail/' . trim((string) ($item['cveID'] ?? '')),
        ];
    }

    private function extractMetricSummary(array $metrics): array
    {
        foreach (['cvssMetricV40', 'cvssMetricV31', 'cvssMetricV30', 'cvssMetricV2'] as $metricKey) {
            $entries = $metrics[$metricKey] ?? null;
            if (!is_array($entries) || empty($entries)) {
                continue;
            }

            $entry = collect($entries)->first(function ($metric) {
                return strtoupper((string) data_get($metric, 'type', '')) === 'PRIMARY';
            });

            if (!is_array($entry)) {
                $entry = (array) reset($entries);
            }

            $cvssData = (array) ($entry['cvssData'] ?? []);
            $severity = strtoupper(trim((string) ($cvssData['baseSeverity'] ?? $entry['baseSeverity'] ?? 'UNKNOWN')));

            return [
                'severity' => $severity !== '' ? $severity : 'UNKNOWN',
                'base_score' => array_key_exists('baseScore', $cvssData)
                    ? (string) $cvssData['baseScore']
                    : null,
                'vector' => trim((string) ($cvssData['vectorString'] ?? '')),
            ];
        }

        return [
            'severity' => 'UNKNOWN',
            'base_score' => null,
            'vector' => '',
        ];
    }

    private function extractEnglishDescription(array $descriptions): string
    {
        foreach ($descriptions as $description) {
            if (strtolower((string) ($description['lang'] ?? '')) !== 'en') {
                continue;
            }

            return trim((string) ($description['value'] ?? ''));
        }

        $first = reset($descriptions);

        return trim((string) data_get($first, 'value', 'Description unavailable.'));
    }

    private function extractCwes(array $weaknesses): array
    {
        return collect($weaknesses)
            ->flatMap(function ($weakness) {
                return (array) ($weakness['description'] ?? []);
            })
            ->map(function ($description) {
                return trim((string) data_get($description, 'value', ''));
            })
            ->filter()
            ->unique()
            ->take(3)
            ->values()
            ->all();
    }

    private function extractReferenceUrl(array $references): ?string
    {
        $reference = collect($references)->first(function ($item) {
            return trim((string) ($item['url'] ?? '')) !== '';
        });

        $url = trim((string) data_get($reference, 'url', ''));

        return $url === '' ? null : $url;
    }

    private function formatDate($value): string
    {
        try {
            if (is_null($value) || trim((string) $value) === '') {
                return '-';
            }

            return Carbon::parse($value)->format('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    }

    private function sortableDate($value): string
    {
        try {
            if (is_null($value) || trim((string) $value) === '') {
                return '';
            }

            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return '';
        }
    }
}
