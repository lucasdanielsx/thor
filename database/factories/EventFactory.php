<?php

namespace Database\Factories;

use App\Models\Event;
use App\Shared\Enums\EventType;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'type' => EventType::TransactionAuthorized,
            'payload' => "{\"test\" : \"test\"}",
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
