<?php

use App\Models\Bed;
use App\Models\BedOccupancy;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BedAdmitTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_admit_patient_to_available_bed(): void
    {
        $bed = Bed::factory()->create();
        $patient = Patient::factory()->create();

        $response = $this->postJson("/api/beds/{$bed->id}/admit", [
            'cpf'  => $patient->cpf,
            'name' => $patient->name,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.bed.id', $bed->id)
            ->assertJsonPath('data.patient.cpf', $patient->cpf);

        $this->assertDatabaseHas('bed_occupancies', [
            'bed_id'        => $bed->id,
            'patient_id'    => $patient->id,
            'discharged_at' => null,
        ]);
    }

    public function test_cannot_admit_patient_to_occupied_bed(): void
    {
        $bed = Bed::factory()->create();
        $patient1 = Patient::factory()->create();
        $patient2 = Patient::factory()->create();

        BedOccupancy::factory()->create([
            'bed_id'     => $bed->id,
            'patient_id' => $patient1->id,
        ]);

        $response = $this->postJson("/api/beds/{$bed->id}/admit", [
            'cpf' => $patient2->cpf,
        ]);

        $response->assertStatus(409);
    }

    public function test_cannot_admit_already_admitted_patient(): void
    {
        $bed1 = Bed::factory()->create();
        $bed2 = Bed::factory()->create();
        $patient = Patient::factory()->create();

        BedOccupancy::factory()->create([
            'bed_id'     => $bed1->id,
            'patient_id' => $patient->id,
        ]);

        $response = $this->postJson("/api/beds/{$bed2->id}/admit", [
            'cpf' => $patient->cpf,
        ]);

        $response->assertStatus(409);
    }

    public function test_admit_creates_patient_if_not_exists(): void
    {
        $bed = Bed::factory()->create();

        $response = $this->postJson("/api/beds/{$bed->id}/admit", [
            'cpf'  => '12345678901',
            'name' => 'João da Silva',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('patients', ['cpf' => '12345678901', 'name' => 'João da Silva']);
    }

    public function test_cpf_validation_requires_11_digits(): void
    {
        $bed = Bed::factory()->create();

        $response = $this->postJson("/api/beds/{$bed->id}/admit", [
            'cpf' => '123',
        ]);

        $response->assertStatus(422);
    }
}
