<?php

namespace Database\Factories;

use App\Models\Bed;
use App\Models\BedOccupancy;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class BedOccupancyFactory extends Factory
{
    protected $model = BedOccupancy::class;

    public function definition(): array
    {
        return [
            'bed_id'       => Bed::factory(),
            'patient_id'   => Patient::factory(),
            'admitted_at'  => now(),
            'discharged_at' => null,
        ];
    }

    public function discharged(): static
    {
        return $this->state(fn () => ['discharged_at' => now()]);
    }
}
