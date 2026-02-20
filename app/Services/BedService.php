<?php

namespace App\Services;

use App\Models\Bed;
use App\Models\BedOccupancy;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class BedService
{
    /**
     * @throws Throwable
     */
    public function admit(Bed $bed, Patient $patient): BedOccupancy
    {
        return DB::transaction(function () use ($bed, $patient) {
            if ($bed->isOccupied()) {
                throw new \Exception("Leito '{$bed->identifier}' já está ocupado.", Response::HTTP_CONFLICT);
            }

            if ($patient->isAdmitted()) {
                $currentBed = $patient->currentOccupancy->bed->identifier;
                throw new \Exception(
                    "Paciente já está internado no leito '{$currentBed}'.",
                    Response::HTTP_CONFLICT
                );
            }

            return BedOccupancy::create([
                'bed_id'        => $bed->id,
                'patient_id'    => $patient->id,
                'admitted_at'   => now(),
                'discharged_at' => null,
            ]);
        });
    }

    /**
     * @throws Throwable
     */
    public function discharge(Bed $bed): BedOccupancy
    {
        return DB::transaction(function () use ($bed) {
            $occupancy = $bed->currentOccupancy;

            if (!$occupancy) {
                throw new \Exception("Leito '{$bed->identifier}' já está desocupado.", Response::HTTP_CONFLICT);
            }

            $occupancy->update(['discharged_at' => now()]);

            return $occupancy->fresh(['bed', 'patient']);
        });
    }

    public function transfer(Bed $sourceBed, Bed $targetBed): BedOccupancy
    {
        return DB::transaction(function () use ($sourceBed, $targetBed) {
            if ($sourceBed->id === $targetBed->id) {
                throw new \Exception('O leito de origem e destino não podem ser o mesmo.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $occupancy = $sourceBed->currentOccupancy;

            if (!$occupancy) {
                throw new \Exception("Leito '{$sourceBed->identifier}' não possui paciente internado.", Response::HTTP_CONFLICT);
            }

            if ($targetBed->isOccupied()) {
                throw new \Exception("Leito destino '{$targetBed->identifier}' já está ocupado.", Response::HTTP_CONFLICT);
            }

            $occupancy->update(['discharged_at' => now()]);

            return BedOccupancy::create([
                'bed_id'        => $targetBed->id,
                'patient_id'    => $occupancy->patient_id,
                'admitted_at'   => now(),
                'discharged_at' => null,
            ]);
        });
    }

    public function findBedByCpf(string $cpf): BedOccupancy
    {
        $patient = Patient::where('cpf', $cpf)->first();

        if (!$patient) {
            throw new \DomainException('Paciente não encontrado.', Response::HTTP_NOT_FOUND);
        }

        $occupancy = $patient->currentOccupancy()->with(['bed', 'patient'])->first();

        if (!$occupancy) {
            throw new \DomainException('Paciente não está internado em nenhum leito.', Response::HTTP_NOT_FOUND);
        }

        return $occupancy;
    }
}
