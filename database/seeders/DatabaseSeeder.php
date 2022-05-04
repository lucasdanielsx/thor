<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
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
        $customerUser = User::create([
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

        Wallet::create([
            'id' => 'f5095b3d-0754-4c57-a755-5bd07fb3e906',
            'user_id' => $customerUser->id,
            'balance' => 1000000,
            'created_at' => Carbon::parse('2022-01-01'),
            'updated_at' => Carbon::parse('2022-01-01')
        ]);

        $storeUser = User::create([
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

        Wallet::create([
            'id' => 'e38b6c3a-1f7e-4ffc-955d-483d92923bb9',
            'user_id' => $storeUser->id,
            'balance' => 100000,
            'created_at' => Carbon::parse('2022-01-01'),
            'updated_at' => Carbon::parse('2022-01-01')
        ]);

        $customerUser = User::create([
            'id' => '76e9f72f-0707-4d84-a346-68ba55c4dc78',
            'name' => 'Test 1',
            'email' => 'test1@gmail.com',
            'type' => 1,
            'document' => '81322172099',
            'password' => 'encrypted',
            'document_type' => 1,
            'created_at' => Carbon::parse('2022-01-01'),
            'updated_at' => Carbon::parse('2022-01-01')
        ]);

        Wallet::create([
            'id' => 'b7f32a9b-ea88-49db-9902-78044ecce5ea',
            'user_id' => $customerUser->id,
            'balance' => 0,
            'created_at' => Carbon::parse('2022-01-01'),
            'updated_at' => Carbon::parse('2022-01-01')
        ]);

        $customerUser = User::create([
            'id' => '295a8802-b87c-46cb-a063-c6db734cb73b',
            'name' => 'test 2',
            'email' => 'test2@gmail.com',
            'type' => 1,
            'document' => '01104112000',
            'password' => 'encrypted',
            'document_type' => 1,
            'created_at' => Carbon::parse('2022-01-01'),
            'updated_at' => Carbon::parse('2022-01-01')
        ]);

        Wallet::create([
            'id' => 'fab8fb72-216c-4009-9a4c-53eebaec7c02',
            'user_id' => $customerUser->id,
            'balance' => 0,
            'created_at' => Carbon::parse('2022-01-01'),
            'updated_at' => Carbon::parse('2022-01-01')
        ]);

        $storeUser = User::create([
            'id' => '6cd95ad0-1ed6-45f7-8576-3ca6bddc95ef',
            'name' => 'Test Store',
            'email' => 'teststore@gmail.com',
            'type' => 2,
            'document' => '12484300000172',
            'password' => 'encrypted',
            'document_type' => 2,
            'created_at' => Carbon::parse('2022-01-01'),
            'updated_at' => Carbon::parse('2022-01-01')
        ]);

        Wallet::create([
            'id' => 'b2356ba0-77cd-4670-ba8c-97a96845a9d9',
            'user_id' => $storeUser->id,
            'balance' => 0,
            'created_at' => Carbon::parse('2022-01-01'),
            'updated_at' => Carbon::parse('2022-01-01')
        ]);
    }
}
