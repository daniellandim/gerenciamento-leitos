<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\BedOccupancy;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BedTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_transfer_patient_to_available_bed(): void
    {
        $sourceBed = Bed::factory()->create();
        $targetBed = Bed::factory()->create();
        $patient   = Patient::factory()->create();

        BedOccupancy::factory()->create([
            'bed_id'     => $sourceBed->id,
            'patient_id' => $patient->id,
        ]);

        $response = $this->postJson("/api/beds/{$sourceBed->id}/transfer", [
            'target_bed_id' => $targetBed->id,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.bed.id', $targetBed->id)
                 ->assertJsonPath('data.patient.cpf', $patient->cpf);

        // Source bed should now be free
        $this->assertFalse($sourceBed->fresh()->isOccupied());

        // Target bed should be occupied
        $this->assertTrue($targetBed->fresh()->isOccupied());
    }

    public function test_cannot_transfer_from_empty_bed(): void
    {
        $sourceBed = Bed::factory()->create();
        $targetBed = Bed::factory()->create();

        $response = $this->postJson("/api/beds/{$sourceBed->id}/transfer", [
            'target_bed_id' => $targetBed->id,
        ]);

        $response->assertStatus(409);
    }

    public function test_cannot_transfer_to_occupied_bed(): void
    {
        $sourceBed = Bed::factory()->create();
        $targetBed = Bed::factory()->create();
        $patient1  = Patient::factory()->create();
        $patient2  = Patient::factory()->create();

        BedOccupancy::factory()->create(['bed_id' => $sourceBed->id, 'patient_id' => $patient1->id]);
        BedOccupancy::factory()->create(['bed_id' => $targetBed->id, 'patient_id' => $patient2->id]);

        $response = $this->postJson("/api/beds/{$sourceBed->id}/transfer", [
            'target_bed_id' => $targetBed->id,
        ]);

        $response->assertStatus(409);
    }

    public function test_cannot_transfer_to_same_bed(): void
    {
        $bed     = Bed::factory()->create();
        $patient = Patient::factory()->create();

        BedOccupancy::factory()->create(['bed_id' => $bed->id, 'patient_id' => $patient->id]);

        $response = $this->postJson("/api/beds/{$bed->id}/transfer", [
            'target_bed_id' => $bed->id,
        ]);

        $response->assertStatus(422);
    }
}
