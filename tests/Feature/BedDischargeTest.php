<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\BedOccupancy;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BedDischargeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_discharge_patient_from_occupied_bed(): void
    {
        $bed      = Bed::factory()->create();
        $patient  = Patient::factory()->create();
        $occupancy = BedOccupancy::factory()->create([
            'bed_id'     => $bed->id,
            'patient_id' => $patient->id,
        ]);

        $response = $this->postJson("/api/beds/{$bed->id}/discharge");

        $response->assertStatus(200)
                 ->assertJsonPath('data.discharged_at', fn ($v) => ! is_null($v));

        $this->assertDatabaseMissing('bed_occupancies', [
            'id'           => $occupancy->id,
            'discharged_at' => null,
        ]);
    }

    public function test_cannot_discharge_from_empty_bed(): void
    {
        $bed = Bed::factory()->create();

        $response = $this->postJson("/api/beds/{$bed->id}/discharge");

        $response->assertStatus(409);
    }

    public function test_bed_becomes_available_after_discharge(): void
    {
        $bed     = Bed::factory()->create();
        $patient = Patient::factory()->create();

        BedOccupancy::factory()->create(['bed_id' => $bed->id, 'patient_id' => $patient->id]);

        $this->postJson("/api/beds/{$bed->id}/discharge");

        $status = $this->getJson("/api/beds/{$bed->id}/status");
        $status->assertJsonPath('data.status', 'available');
    }
}
