<?php

namespace Database\Factories;

use App\Models\Bed;
use Illuminate\Database\Eloquent\Factories\Factory;

class BedFactory extends Factory
{
    protected $model = Bed::class;

    public function definition(): array
    {
        return [
            'identifier'  => strtoupper($this->faker->unique()->bothify('??-###')),
            'description' => $this->faker->optional()->sentence(4),
        ];
    }
}
