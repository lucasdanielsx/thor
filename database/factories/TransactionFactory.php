<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Shared\Enums\TransactionStatus;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'value' => 100,
            'status' => TransactionStatus::Created,
            'payload' => "{\"test\" : \"test\"}",
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that Transaction is paid
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function paidStatus()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => TransactionStatus::Paid,
            ];
        });
    }
}
