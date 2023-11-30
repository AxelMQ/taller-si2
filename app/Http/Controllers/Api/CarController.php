<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CarController extends Controller
{
    //
    public function registerCar(Request $request)
    {
        try {
            // info('Request received:', $request->all());
            $request->validate([
                'modelo' => 'required',
                'placa' => 'required',
                'year' => 'required',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $user = Auth::user();
            // $user = $request->user();

            if (!$user->cliente) {
                return response()->json([
                    "message" => "El usuario no tiene un cliente asociado",
                ], Response::HTTP_BAD_REQUEST);
            }

            $image = $request->file('image');

            if ($image) {
                $imageName = 'car_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('car_images', $imageName, 'public');
            } else {
                $imagePath = null;
            }

            $car = new Car();
            $car->modelo = $request->modelo;
            $car->placa = $request->placa;
            // $car->marca = $request->marca;
            $car->year = $request->year;
            $car->image = $imagePath;
            $car->cliente_id = $user->cliente->id;
            $car->save();

            // info('Car registered successfully:', $car->toArray());
            return response()->json([
                "message" => 'Vehiculo Registrado correctamente',
                "car" => $car
            ], Response::HTTP_CREATED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "message" => "No se logró Registrar el Vehículo",
                "error" => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getCars()
    {
        try {

            $car = Car::all();

            return response()->json([
                "cars" => $car
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al obtener los Vehículos.",
                "error" => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getCarUser()
    {
        try {
            $user = Auth::user();

            $vehiculos = $user->cliente->cars;
            return response()->json([
                "vehiculos" => $vehiculos
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al obtener los Vehículos del usuario autenticado.",
                "error" => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteCar($id)
    {
        try {
            $car = Car::find($id);

            if (!$car) {
                return response()->json([
                    "message" => "No se Logro encontrar el vehiculo"
                ], Response::HTTP_NOT_FOUND);
            }

            if ($car->image) {
                Storage::disk('public')->delete($car->image);
            }

            $car->delete();

            return response()->json([
                "message" => "Vehiculo Eliminado exitosamente",
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al eliminar el Vehiculo.",
                "error" => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
