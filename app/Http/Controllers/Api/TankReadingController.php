<?php

namespace App\Http\Controllers\Api;

use App\Application\DTOs\CreateTankReadingDTO;
use App\Application\UseCases\GetLatestTankReadingUseCase;
use App\Application\UseCases\ListTankReadingsUseCase;
use App\Application\UseCases\RegisterTankReadingUseCase;
use App\Domain\Exceptions\TankNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\TankReadingBatchRequest;
use App\Http\Requests\TankReadingDateRangeRequest;
use App\Http\Requests\TankReadingStoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use DateTime;

class TankReadingController extends Controller
{
    /**
     * Obtener todas las lecturas de un tanque
     */
    public function index(int $tankId, ListTankReadingsUseCase $listTankReadingsUseCase): JsonResponse
    {
        try {
            $readings = $listTankReadingsUseCase->execute($tankId);
            
            return response()->json([
                'data' => array_map(fn ($reading) => $reading->toArray(), $readings),
            ]);
        } catch (TankNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Registrar una nueva lectura IoT
     */
    public function store(TankReadingStoreRequest $request, RegisterTankReadingUseCase $registerTankReadingUseCase): JsonResponse
    {
        $validated = $request->validated();
        
        try {
            $dto = CreateTankReadingDTO::fromArray($validated);
            $reading = $registerTankReadingUseCase->execute($dto);
            
            return response()->json([
                'message' => 'Lectura registrada con éxito',
                'data' => $reading->toArray(),
            ], Response::HTTP_CREATED);
        } catch (TankNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Obtener la última lectura de un tanque
     */
    public function latest(int $tankId, GetLatestTankReadingUseCase $getLatestTankReadingUseCase): JsonResponse
    {
        try {
            $reading = $getLatestTankReadingUseCase->execute($tankId);
            
            if (!$reading) {
                return response()->json([
                    'message' => 'No hay lecturas disponibles para este tanque',
                ], Response::HTTP_NOT_FOUND);
            }
            
            return response()->json([
                'data' => $reading->toArray(),
            ]);
        } catch (TankNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Obtener lecturas por rango de fechas
     */
    public function getByDateRange(int $tankId, TankReadingDateRangeRequest $request, ListTankReadingsUseCase $listTankReadingsUseCase): JsonResponse
    {
        $validated = $request->validated();
        
        try {
            $startDate = new DateTime($validated['start_date']);
            $endDate = new DateTime($validated['end_date']);
            
            $readings = $listTankReadingsUseCase->executeWithDateRange($tankId, $startDate, $endDate);
            
            return response()->json([
                'data' => array_map(fn ($reading) => $reading->toArray(), $readings),
            ]);
        } catch (TankNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }
    
    /**
     * Registrar múltiples lecturas en lote
     */
    public function storeBatch(TankReadingBatchRequest $request, RegisterTankReadingUseCase $registerTankReadingUseCase): JsonResponse
    {
        $validated = $request->validated();
        
        $results = [];
        $errors = [];
        
        foreach ($validated['readings'] as $index => $readingData) {
            try {
                $dto = CreateTankReadingDTO::fromArray($readingData);
                $reading = $registerTankReadingUseCase->execute($dto);
                $results[] = $reading->toArray();
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'message' => $e->getMessage(),
                ];
            }
        }
        
        return response()->json([
            'message' => count($results) . ' lecturas registradas con éxito',
            'data' => $results,
            'errors' => $errors,
        ], empty($results) ? Response::HTTP_BAD_REQUEST : Response::HTTP_CREATED);
    }
}