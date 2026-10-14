<?php

namespace App\Services\Dashboard;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * Filters shared by every section of the main dashboard.
 */
class DashboardFilters
{
    public const PERIOD_MONTH = 'month';
    public const PERIOD_QUARTER = 'quarter';

    public function __construct(
        public readonly ?string $owner = null,
        public readonly ?int $categoryId = null,
        public readonly string $period = self::PERIOD_MONTH,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $owner = trim((string) $request->query('owner', ''));
        $categoryId = (int) $request->query('category', 0);
        $period = $request->query('period') === self::PERIOD_QUARTER ? self::PERIOD_QUARTER : self::PERIOD_MONTH;

        return new self($owner !== '' ? $owner : null, $categoryId > 0 ? $categoryId : null, $period);
    }

    /**
     * Current period to date and the full previous period it is compared with.
     *
     * @return array{start: CarbonImmutable, end: CarbonImmutable, prev_start: CarbonImmutable, prev_end: CarbonImmutable, label: string, prev_label: string}
     */
    public function window(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();

        if ($this->period === self::PERIOD_QUARTER) {
            $start = $now->firstOfQuarter()->startOfDay();
            $prevStart = $start->subQuarterNoOverflow();

            return [
                'start' => $start,
                'end' => $now,
                'prev_start' => $prevStart,
                'prev_end' => $start->subSecond(),
                'label' => 'Q' . $start->quarter . ' ' . $start->year,
                'prev_label' => 'Q' . $prevStart->quarter . ' ' . $prevStart->year,
            ];
        }

        $start = $now->startOfMonth();
        $prevStart = $start->subMonthNoOverflow();

        return [
            'start' => $start,
            'end' => $now,
            'prev_start' => $prevStart,
            'prev_end' => $start->subSecond(),
            'label' => $start->locale('id')->translatedFormat('F Y'),
            'prev_label' => $prevStart->locale('id')->translatedFormat('F Y'),
        ];
    }

    public function cacheKey(): string
    {
        return md5(json_encode([$this->owner, $this->categoryId, $this->period]));
    }

    public function toQuery(): array
    {
        return array_filter([
            'owner' => $this->owner,
            'category' => $this->categoryId,
            'period' => $this->period,
        ]);
    }
}
