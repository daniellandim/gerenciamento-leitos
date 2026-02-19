<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\BedOccupancy;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_find_bed_by_cpf(): void
    {
        $bed     = Bed::factory()->create(['identifier' => 'UTI-01']);
        $patient = Patient::factory()->create(['cpf' => '98765432100']);

        BedOccupancy::factory()->create(['bed_id' => $bed->id, 'patient_id' => $patient->id]);

        $response = $this->getJson('/api/patients/search?cpf=98765432100');

        $response->assertStatus(200)
                 ->assertJsonPath('data.patient.cpf', '98765432100')
                 ->assertJsonPath('data.bed.identifier', 'UTI-01');
    }

    public function test_returns_404_for_unknown_cpf(): void
    {
        $response = $this->getJson('/api/patients/search?cpf=00000000000');

        $response->assertStatus(404);
    }

    public function test_returns_404_when_patient_not_admitted(): void
    {
        Patient::factory()->create(['cpf' => '11122233344']);

        $response = $this->getJson('/api/patients/search?cpf=11122233344');

        $response->assertStatus(404);
    }

    public function test_cpf_is_required(): void
    {
        $response = $this->getJson('/api/patients/search');

        $response->assertStatus(422);
    }
}
