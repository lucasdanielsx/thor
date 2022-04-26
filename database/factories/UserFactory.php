<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'document' => '94271368040',
            'document_type' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'type' => 1,
            'password' => 'encrypted'
        ];
    }

    /**
     * Indicate that the user is STORE.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function storeUser()
    {
        return $this->state(function (array $attributes) {
            return [
                'document' => '41297905000152',
                'document_type' => 2,
                'type' => 2
            ];
        });
    }
}
