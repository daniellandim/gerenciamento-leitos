<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\BedOccupancy;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BedListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_all_beds_with_status(): void
    {
        $occupiedBed   = Bed::factory()->create(['identifier' => 'A-001']);
        $availableBed  = Bed::factory()->create(['identifier' => 'A-002']);
        $patient       = Patient::factory()->create();

        BedOccupancy::factory()->create([
            'bed_id'     => $occupiedBed->id,
            'patient_id' => $patient->id,
        ]);

        $response = $this->getJson('/api/beds');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');

        $data = collect($response->json('data'));

        $occupied  = $data->firstWhere('identifier', 'A-001');
        $available = $data->firstWhere('identifier', 'A-002');

        $this->assertEquals('occupied', $occupied['status']);
        $this->assertNotNull($occupied['patient']);

        $this->assertEquals('available', $available['status']);
        $this->assertNull($available['patient']);
    }

    public function test_can_get_status_of_single_occupied_bed(): void
    {
        $bed     = Bed::factory()->create();
        $patient = Patient::factory()->create();

        BedOccupancy::factory()->create(['bed_id' => $bed->id, 'patient_id' => $patient->id]);

        $response = $this->getJson("/api/beds/{$bed->id}/status");

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'occupied')
                 ->assertJsonPath('data.patient.cpf', $patient->cpf);
    }

    public function test_can_get_status_of_available_bed(): void
    {
        $bed = Bed::factory()->create();

        $response = $this->getJson("/api/beds/{$bed->id}/status");

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'available')
                 ->assertJsonPath('data.patient', null);
    }
}
