<?php

namespace Database\Factories;

use App\Models\Wallet;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Wallet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'balance' => 100000
        ];
    }
}
