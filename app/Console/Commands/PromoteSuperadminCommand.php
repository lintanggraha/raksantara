<?php

namespace App\Console\Commands;

use App\Support\AuditLogger;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PromoteSuperadminCommand extends Command
{
    protected $signature = 'user:promote-superadmin
        {username : Username yang akan dipromosikan}
        {--division=CO - COMPLIANCE : Division default jika user belum punya division}
        {--create : Buat user baru jika username belum ada}
        {--name= : Nama lengkap user baru (wajib saat --create)}
        {--email= : Email user baru (wajib saat --create)}
        {--password= : Password user baru (opsional, otomatis di-generate jika kosong)}';

    protected $description = 'Promote existing user menjadi superadmin secara aman melalui CLI';

    public function handle(): int
    {
        $username = trim((string) $this->argument('username'));
        $division = trim((string) $this->option('division'));
        $createWhenMissing = (bool) $this->option('create');

        $user = User::query()
            ->where('username', $username)
            ->first();

        if (is_null($user)) {
            if (!$createWhenMissing) {
                $this->error('User tidak ditemukan: ' . $username);
                $this->line('Gunakan --create untuk membuat user baru sekaligus promote.');
                return self::FAILURE;
            }

            $name = trim((string) $this->option('name'));
            $email = strtolower(trim((string) $this->option('email')));

            if ($name === '' || $email === '') {
                $this->error('Saat memakai --create, --name dan --email wajib diisi.');
                return self::FAILURE;
            }

            if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
                $this->error('Email sudah dipakai: ' . $email);
                return self::FAILURE;
            }

            $plainPassword = trim((string) $this->option('password'));
            $isGeneratedPassword = $plainPassword === '';
            if ($isGeneratedPassword) {
                $plainPassword = $this->generatePassword();
            }

            $user = User::create([
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'division' => $division !== '' ? $division : 'CO - COMPLIANCE',
                'pending_division' => null,
                'division_verified_at' => now(),
                'password' => Hash::make($plainPassword),
                'force_password_change' => $isGeneratedPassword,
                'password_changed_at' => $isGeneratedPassword ? null : now(),
            ]);

            $this->info('User baru berhasil dibuat: ' . $user->username);
            if ($isGeneratedPassword) {
                $this->warn('Temporary password: ' . $plainPassword);
                $this->line('User wajib ganti password pada login pertama.');
            }
        }

        $before = $user->toArray();

        $user->is_superadmin = true;
        if ($user->division === null || trim((string) $user->division) === '') {
            $user->division = $division !== '' ? $division : 'CO - COMPLIANCE';
        }
        $user->pending_division = null;
        $user->division_verified_at = $user->division_verified_at ?: now();
        $user->save();

        AuditLogger::logModelEvent(
            'user.promoted_to_superadmin.via_cli',
            $user,
            $before,
            $user->fresh()->toArray()
        );

        $this->info('User berhasil dipromosikan menjadi superadmin: ' . $user->username);
        $this->line('Division: ' . (string) $user->division);

        return self::SUCCESS;
    }

    private function generatePassword(int $length = 16): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*_-=+';
        $maxIndex = strlen($alphabet) - 1;

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, $maxIndex)];
        }

        if (!preg_match('/[A-Za-z]/', $password)) {
            $password .= Str::upper(Str::random(1));
        }

        if (!preg_match('/\d/', $password)) {
            $password .= (string) random_int(2, 9);
        }

        return $password;
    }
}
