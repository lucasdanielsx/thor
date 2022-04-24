<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $customerUser = \App\Models\User::create([
            'id' => '2fbab582-b592-4dc8-9c08-a1fbe2072fbf',
            'name' => 'Lucas Daniel',
            'email' => 'lucas@gmail.com',
            'type' => 1,
            'document' => '94271368040',
            'password' => 'encrypted',
            'document_type' => 1,
            'created_at' => Carbon::parse('2022-01-01'),
            'updated_at' => Carbon::parse('2022-01-01')
        ]);

        \App\Models\Wallet::create([
            'id' => 'f5095b3d-0754-4c57-a755-5bd07fb3e906',
            'user_id' => $customerUser->id,
            'balance' => 1000000,
            'created_at' => Carbon::parse('2022-01-01'),
            'updated_at' => Carbon::parse('2022-01-01')
        ]);

        $storeUser = \App\Models\User::create([
            'id' => '05804b24-71a0-4aa8-8a6e-dae786b5b6d1',
            'name' => 'Lojista',
            'email' => 'lojista@gmail.com',
            'type' => 2,
            'document' => '41297905000152',
            'password' => 'encrypted',
            'document_type' => 2,
            'created_at' => Carbon::parse('2022-01-01'),
            'updated_at' => Carbon::parse('2022-01-01')
        ]);

        \App\Models\Wallet::create([
            'id' => 'e38b6c3a-1f7e-4ffc-955d-483d92923bb9',
            'user_id' => $storeUser->id,
            'balance' => 1000000,
            'created_at' => Carbon::parse('2022-01-01'),
            'updated_at' => Carbon::parse('2022-01-01')
        ]);
    }
}
