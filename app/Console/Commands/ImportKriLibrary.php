<?php

namespace App\Console\Commands;

use App\Kri;
use App\Risk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Loads a curated KRI library (one KRI per risk) from a JSON file.
 *
 * Re-running updates the same KRIs; they are identified by the library tag in
 * kris.source_reference, so KRIs created by users are never touched. New KRIs
 * start without a value: units record the first reading themselves.
 */
class ImportKriLibrary extends Command
{
    protected $signature = 'kri:import-library
        {path=storage/app/kri/kri_library_2026.json : JSON file relative to the project root}
        {--tag=kri-library-2026 : Library tag stored in kris.source_reference}
        {--dry-run : Validate and report without writing}
        {--rollback : Remove library KRIs that have no readings yet}';

    protected $description = 'Import (or roll back) the curated KRI library into the KRI register';

    private const REFRESH_MINUTES = [60, 1440, 10080, 43200, 129600];

    public function handle(): int
    {
        $tag = (string) $this->option('tag');

        if ($this->option('rollback')) {
            return $this->rollback($tag);
        }

        $path = base_path((string) $this->argument('path'));
        if (!is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $items = json_decode((string) file_get_contents($path), true);
        if (!is_array($items)) {
            $this->error('The file is not a JSON array.');

            return self::FAILURE;
        }

        $risks = Risk::query()->pluck('id', 'risk_id');
        $errors = [];
        $rows = [];
        foreach ($items as $index => $item) {
            $problem = $this->validateItem(is_array($item) ? $item : [], $risks->all());
            if ($problem === null && isset($rows[$item['risk_code']])) {
                $problem = 'duplicate risk_code (one library KRI per risk)';
            }
            if ($problem !== null) {
                $errors[] = '#' . ($index + 1) . ' ' . ($item['risk_code'] ?? '?') . ': ' . $problem;
                continue;
            }
            $rows[$item['risk_code']] = $item;
        }

        if (!empty($errors)) {
            $this->error('Validation failed; nothing was written.');
            foreach (array_slice($errors, 0, 30) as $error) {
                $this->line('  ' . $error);
            }

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->info(count($rows) . ' KRI(s) are valid. Dry run: nothing was written.');

            return self::SUCCESS;
        }

        $created = 0;
        $updated = 0;
        DB::transaction(function () use ($rows, $risks, $tag, &$created, &$updated) {
            foreach ($rows as $code => $item) {
                $kri = Kri::query()->firstOrNew([
                    'risk_id' => $risks[$code],
                    'source_reference' => $tag,
                ]);
                $isNew = !$kri->exists;

                $kri->fill([
                    'metric_name' => $item['metric_name'],
                    'description' => $item['description'],
                    'data_source' => $item['data_source'],
                    'source_identifier' => $item['source_identifier'],
                    'expected_refresh_minutes' => (int) $item['expected_refresh_minutes'],
                    'threshold_type' => $item['threshold_type'],
                    'threshold_warn' => $item['threshold_warn'],
                    'threshold_critical' => $item['threshold_critical'],
                ]);

                if ($isNew) {
                    $kri->status = 'Green';
                    $kri->data_quality_status = 'unverified';
                    $kri->data_quality_notes = 'Belum ada nilai pertama dari sumber data.';
                } elseif ($kri->current_value !== null) {
                    // Thresholds may have changed: keep the status in line with the current reading.
                    $kri->status = $kri->calculateStatusForValue($kri->current_value);
                }

                $kri->save();
                $isNew ? $created++ : $updated++;
            }
        });

        $this->info("KRI library '{$tag}': {$created} created, {$updated} updated.");

        return self::SUCCESS;
    }

    private function validateItem(array $item, array $risks): ?string
    {
        $validator = Validator::make($item, [
            'risk_code' => 'required|string',
            'metric_name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'threshold_type' => 'required|in:lower_is_better,higher_is_better',
            'threshold_warn' => 'required|numeric',
            'threshold_critical' => 'required|numeric',
            'data_source' => 'required|in:manual,api,file-import,webhook,risk-register-import',
            'source_identifier' => 'required|string|max:255',
            'expected_refresh_minutes' => 'required|integer|in:' . implode(',', self::REFRESH_MINUTES),
        ]);
        if ($validator->fails()) {
            return implode(' ', $validator->errors()->all());
        }

        if (!array_key_exists($item['risk_code'], $risks)) {
            return 'risk not found in the register';
        }

        $warn = (float) $item['threshold_warn'];
        $critical = (float) $item['threshold_critical'];
        if ($item['threshold_type'] === 'lower_is_better' && $critical < $warn) {
            return 'lower_is_better needs threshold_critical >= threshold_warn';
        }
        if ($item['threshold_type'] === 'higher_is_better' && $critical > $warn) {
            return 'higher_is_better needs threshold_critical <= threshold_warn';
        }

        return null;
    }

    private function rollback(string $tag): int
    {
        $query = Kri::query()->where('source_reference', $tag);
        $withReadings = (clone $query)->where(fn ($q) => $q->whereNotNull('current_value')->orWhereHas('logs'))->count();
        $deletable = (clone $query)->whereNull('current_value')->whereDoesntHave('logs');

        if ($this->option('dry-run')) {
            $this->info($deletable->count() . " KRI(s) would be removed; {$withReadings} kept because they already have readings.");

            return self::SUCCESS;
        }

        $removed = $deletable->delete();
        $this->info("Removed {$removed} KRI(s) from library '{$tag}'; kept {$withReadings} that already have readings.");

        return self::SUCCESS;
    }
}
