<?php

namespace Database\Factories;

use App\Models\Statement;
use App\Shared\Enums\StatementStatus;
use App\Shared\Enums\StatementType;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatementFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Statement::class;

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
            'status' => StatementStatus::Created,
            'type' => StatementType::In,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
