<?php

namespace App\Http\Controllers\Api;

use App\Application\DTOs\CreateTankDTO;
use App\Application\UseCases\CreateTankUseCase;
use App\Application\UseCases\DeleteTankUseCase;
use App\Application\UseCases\GetTankUseCase;
use App\Application\UseCases\ListTanksUseCase;
use App\Application\UseCases\UpdateTankUseCase;
use App\Domain\Exceptions\TankNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\TankStoreRequest;
use App\Http\Requests\TankUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TankController extends Controller
{
    /**
     * Listar todos los tanques
     */
    public function index(ListTanksUseCase $listTanksUseCase): JsonResponse
    {
        $tanks = $listTanksUseCase->execute();
        
        return response()->json([
            'data' => array_map(fn ($tank) => $tank->toArray(), $tanks),
        ]);
    }

    /**
     * Crear un nuevo tanque
     */
    public function store(TankStoreRequest $request, CreateTankUseCase $createTankUseCase): JsonResponse
    {
        $validated = $request->validated();
        
        $dto = CreateTankDTO::fromArray($validated);
        $tank = $createTankUseCase->execute($dto);
        
        return response()->json([
            'message' => 'Tanque creado con éxito',
            'data' => $tank->toArray(),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar un tanque específico
     */
    public function show(int $id, GetTankUseCase $getTankUseCase): JsonResponse
    {
        try {
            $tank = $getTankUseCase->execute($id);
            
            return response()->json([
                'data' => $tank->toArray(),
            ]);
        } catch (TankNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Actualizar un tanque existente
     */
    public function update(int $id, TankUpdateRequest $request, UpdateTankUseCase $updateTankUseCase): JsonResponse
    {
        $validated = $request->validated();
        
        try {
            $tank = $updateTankUseCase->execute($id, $validated);
            
            return response()->json([
                'message' => 'Tanque actualizado con éxito',
                'data' => $tank->toArray(),
            ]);
        } catch (TankNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Eliminar un tanque
     */
    public function destroy(int $id, DeleteTankUseCase $deleteTankUseCase): JsonResponse
    {
        try {
            $deleteTankUseCase->execute($id);
            
            return response()->json([
                'message' => 'Tanque eliminado con éxito',
            ]);
        } catch (TankNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }
}