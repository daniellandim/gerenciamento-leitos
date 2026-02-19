<?php

namespace Database\Seeders;

use App\Models\Bed;
use App\Models\BedOccupancy;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 20 beds
        $beds = Bed::factory(20)->create();

        // Create 10 patients and admit them to the first 10 beds
        $patients = Patient::factory(10)->create();

        foreach ($patients as $index => $patient) {
            BedOccupancy::create([
                'bed_id'      => $beds[$index]->id,
                'patient_id'  => $patient->id,
                'admitted_at' => now(),
            ]);
        }

        $this->command->info('Seed completo: 20 leitos criados, 10 ocupados.');
    }
}
