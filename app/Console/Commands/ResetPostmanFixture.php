<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetPostmanFixture extends Command
{
    protected $signature = 'postman:reset';

    protected $description = 'Hapus akun uji collection Postman (NIK dummy Dukcapil) supaya Register bisa 201 lagi';

    public function handle(): int
    {
        $niks = [
            '1671010101010001',
            '1671010101010002',
            '1671010101010003',
        ];

        $deleted = 0;

        User::query()
            ->whereIn('nik', $niks)
            ->orWhere('email', 'ahmad.fauzi.test@example.com')
            ->get()
            ->each(function (User $user) use (&$deleted) {
                $user->tokens()->delete();
                $user->delete();
                $deleted++;
            });

        $this->info("Akun uji dihapus: {$deleted}. Silakan Run Collection lagi di Postman.");

        return self::SUCCESS;
    }
}
