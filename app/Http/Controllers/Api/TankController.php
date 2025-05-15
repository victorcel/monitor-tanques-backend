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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function store(Request $request, CreateTankUseCase $createTankUseCase): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:tanks',
            'capacity' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'diameter' => 'nullable|numeric|min:0',
        ]);
        
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
    public function update(int $id, Request $request, UpdateTankUseCase $updateTankUseCase): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'capacity' => 'sometimes|required|numeric|min:0',
            'height' => 'sometimes|required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'diameter' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|required|boolean',
        ]);
        
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