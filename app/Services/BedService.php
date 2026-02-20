<?php

namespace App\Services;

use App\Models\Bed;
use App\Models\BedOccupancy;
use App\Models\Patient;
use Illuminate\Support\Facades\Cache;
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
        return Cache::lock("bed:{$bed->id}", 10)
            ->block(5, function () use ($bed, $patient) {

                return DB::transaction(function () use ($bed, $patient) {

                    if ($bed->isOccupied()) {
                        throw new \Exception(
                            "Leito '{$bed->identifier}' já está ocupado.",
                            Response::HTTP_CONFLICT
                        );
                    }

                    if ($patient->isAdmitted()) {
                        throw new \Exception("Paciente já está internado.", Response::HTTP_CONFLICT);
                    }

                    return BedOccupancy::create([
                        'bed_id'        => $bed->id,
                        'patient_id'    => $patient->id,
                        'admitted_at'   => now(),
                        'discharged_at' => null,
                    ]);
                });
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
                throw new \DomainException("Leito '{$bed->identifier}' já está desocupado.", Response::HTTP_CONFLICT);
            }

            $occupancy->update(['discharged_at' => now()]);

            return $occupancy->fresh(['bed', 'patient']);
        });
    }

    public function transfer(Bed $sourceBed, Bed $targetBed): BedOccupancy
    {
        if ($sourceBed->id === $targetBed->id) {
            throw new \DomainException(
                'O leito de origem e destino não podem ser o mesmo.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return Cache::lock("bed:{$targetBed->id}", 10)
            ->block(5, function () use ($sourceBed, $targetBed) {

                return DB::transaction(function () use ($sourceBed, $targetBed) {

                    $sourceBed->refresh();
                    $targetBed->refresh();

                    $occupancy = $sourceBed->currentOccupancy;

                    if (!$occupancy) {
                        throw new \DomainException(
                            "Leito '{$sourceBed->identifier}' não possui paciente internado.",
                            Response::HTTP_CONFLICT
                        );
                    }

                    if ($targetBed->isOccupied()) {
                        throw new \DomainException(
                            "Leito destino '{$targetBed->identifier}' já está ocupado.",
                            Response::HTTP_CONFLICT
                        );
                    }

                    $occupancy->update(['discharged_at' => now()]);

                    try {
                        return BedOccupancy::create([
                            'bed_id'        => $targetBed->id,
                            'patient_id'    => $occupancy->patient_id,
                            'admitted_at'   => now(),
                            'discharged_at' => null,
                        ]);
                    } catch (\Illuminate\Database\QueryException $e) {
                        throw new \DomainException(
                            "Leito destino '{$targetBed->identifier}' já está ocupado.",
                            Response::HTTP_CONFLICT
                        );
                    }
                });
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
