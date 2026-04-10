<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guardAgainstPrimaryDatabaseUsage();
    }

    private function guardAgainstPrimaryDatabaseUsage(): void
    {
        if (!app()->environment('testing')) {
            return;
        }

        $connection = (string) config('database.default');
        $currentConfig = (array) config("database.connections.{$connection}", []);
        $currentDatabase = (string) ($currentConfig['database'] ?? '');

        if ($currentDatabase === '' || $currentDatabase === ':memory:') {
            return;
        }

        $primaryEnv = $this->readPrimaryEnvironmentValues();
        $primaryConnection = (string) ($primaryEnv['DB_CONNECTION'] ?? '');
        $primaryDatabase = (string) ($primaryEnv['DB_DATABASE'] ?? '');

        if ($primaryConnection === '' || $primaryDatabase === '') {
            return;
        }

        if ($connection !== $primaryConnection || $currentDatabase !== $primaryDatabase) {
            return;
        }

        $currentHost = (string) ($currentConfig['host'] ?? '');
        $currentPort = (string) ($currentConfig['port'] ?? '');
        $primaryHost = (string) ($primaryEnv['DB_HOST'] ?? '');
        $primaryPort = (string) ($primaryEnv['DB_PORT'] ?? '');

        if ($primaryHost !== '' && $currentHost !== '' && $primaryHost !== $currentHost) {
            return;
        }

        if ($primaryPort !== '' && $currentPort !== '' && $primaryPort !== $currentPort) {
            return;
        }

        throw new RuntimeException(
            'Unsafe test configuration: testing environment is pointing to the primary database from .env. ' .
            'Configure a dedicated testing database via .env.testing before running the test suite.'
        );
    }

    private function readPrimaryEnvironmentValues(): array
    {
        $path = base_path('.env');
        if (!is_file($path)) {
            return [];
        }

        $values = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim((string) $line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
        }

        return $values;
    }
}
