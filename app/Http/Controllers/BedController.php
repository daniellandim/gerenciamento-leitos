<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdmitPatientRequest;
use App\Http\Requests\TransferPatientRequest;
use App\Http\Resources\BedResource;
use App\Http\Resources\OccupancyResource;
use App\Models\Bed;
use App\Models\Patient;
use App\services\BedService;
use Illuminate\Http\JsonResponse;

class BedController extends Controller
{

    public function __construct(private readonly BedService $bedService)
    {
    }

    public function index()
    {
        $beds = Bed::with(['currentOccupancy.patient'])->get();

        return BedResource::collection($beds);
    }

    public function status(Bed $bed)
    {

        $bed->load('currentOccupancy.patient');


        return BedResource::make($bed);
    }

    public function admit(AdmitPatientRequest $request, Bed $bed): JsonResponse
    {
        $patient = Patient::firstOrCreate(
            ['cpf' => $request->cpf],
            ['name' => $request->name ?? 'Não informado']
        );

        try {
            $occupancy = $this->bedService->admit($bed, $patient);
            $occupancy->load(['bed', 'patient']);

            return response()->json([
                'message' => 'Paciente internado com sucesso.',
                'data'    => OccupancyResource::make($occupancy),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }
    }

    public function discharge(Bed $bed): JsonResponse
    {
        try {
            $occupancy = $this->bedService->discharge($bed);

            return response()->json([
                'message' => 'Leito desocupado com sucesso.',
                'data'    => OccupancyResource::make($occupancy),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }
    }

    public function transfer(TransferPatientRequest $request, Bed $bed): JsonResponse
    {

        $targetBed = Bed::findOrFail($request->target_bed_id);

        try {
            $occupancy = $this->bedService->transfer($bed, $targetBed);
            $occupancy->load(['bed', 'patient']);

            return response()->json([
                'message' => 'Paciente transferido com sucesso.',
                'data'    => OccupancyResource::make($occupancy),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }
    }

}
