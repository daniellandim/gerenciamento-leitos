<?php

use App\Models\Bed;
use App\Models\BedOccupancy;
use App\Models\Patient;
use App\services\BedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BedServiceTest extends TestCase
{
    use RefreshDatabase;

    private BedService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BedService();
    }

    public function test_admit_returns_occupancy(): void
    {
        $bed     = Bed::factory()->create();
        $patient = Patient::factory()->create();

        $occupancy = $this->service->admit($bed, $patient);

        $this->assertInstanceOf(BedOccupancy::class, $occupancy);
        $this->assertEquals($bed->id, $occupancy->bed_id);
        $this->assertEquals($patient->id, $occupancy->patient_id);
        $this->assertNull($occupancy->discharged_at);
    }

    public function test_admit_throws_when_bed_occupied(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(409);

        $bed      = Bed::factory()->create();
        $patient1 = Patient::factory()->create();
        $patient2 = Patient::factory()->create();

        BedOccupancy::factory()->create(['bed_id' => $bed->id, 'patient_id' => $patient1->id]);

        $this->service->admit($bed, $patient2);
    }

    public function test_admit_throws_when_patient_already_admitted(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(409);

        $bed1    = Bed::factory()->create();
        $bed2    = Bed::factory()->create();
        $patient = Patient::factory()->create();

        BedOccupancy::factory()->create(['bed_id' => $bed1->id, 'patient_id' => $patient->id]);

        $this->service->admit($bed2, $patient);
    }

    public function test_discharge_fills_discharged_at(): void
    {
        $bed     = Bed::factory()->create();
        $patient = Patient::factory()->create();
        BedOccupancy::factory()->create(['bed_id' => $bed->id, 'patient_id' => $patient->id]);

        $occupancy = $this->service->discharge($bed);

        $this->assertNotNull($occupancy->discharged_at);
    }

    public function test_discharge_throws_when_bed_empty(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(409);

        $bed = Bed::factory()->create();
        $this->service->discharge($bed);
    }

    public function test_transfer_moves_patient_to_new_bed(): void
    {
        $sourceBed = Bed::factory()->create();
        $targetBed = Bed::factory()->create();
        $patient   = Patient::factory()->create();

        BedOccupancy::factory()->create(['bed_id' => $sourceBed->id, 'patient_id' => $patient->id]);

        $newOccupancy = $this->service->transfer($sourceBed, $targetBed);

        $this->assertEquals($targetBed->id, $newOccupancy->bed_id);
        $this->assertEquals($patient->id, $newOccupancy->patient_id);
        $this->assertFalse($sourceBed->fresh()->isOccupied());
    }

    public function test_transfer_throws_for_same_bed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(422);

        $bed     = Bed::factory()->create();
        $patient = Patient::factory()->create();
        BedOccupancy::factory()->create(['bed_id' => $bed->id, 'patient_id' => $patient->id]);

        $this->service->transfer($bed, $bed);
    }
}
