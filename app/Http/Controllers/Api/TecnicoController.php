<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tecnico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TecnicoController extends Controller
{
    //
    public function updateTecnico(Request $request)
    {
        try {
            $request->validate([
                'estado' => 'nullable',
                'latitud_longitud' => 'nullable',
            ]);

            $user = Auth::user();
            $tecnico = $user->tecnico;

            if (!$tecnico) {
                return response()->json([
                    "message" => 'El Usuario no es Tecnico.'
                ], Response::HTTP_NOT_FOUND);
            }


            if ($request->has('estado')) {
                $tecnico->estado = $request->input('estado');
            }

            if ($request->has('latitud_longitud')) {
                $latitudLongitud = $request->input('latitud_longitud');

                preg_match_all('/-?\d+\.\d+/', $latitudLongitud, $matches);

                if (count($matches[0]) == 2) {
                    $tecnico->latitud = $matches[0][0];
                    $tecnico->longitud = $matches[0][1];
                } else {
                    return response()->json([
                        "message" => 'Formato de latitud_longitud no válido.'
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
            $tecnico->save();

            return response()->json([
                "message" => "Datos del Tecnico Actualizados exitosamente.",
                "tecnico" => $tecnico,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al actualizar datos del Tecnico.",
                "error" => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getTecnicos()
    {
        try {
            $tecnicos = Tecnico::with(['taller', 'user'])->get();

            return response()->json([
                "tecnicos" => $tecnicos
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al obtener los Tecnicos.",
                "error" => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getTecnicoTaller()
    {
        try {
            $user = Auth::user();
            $tallerId = $user->taller->id;

            //$taller = $user->taller->with('tecnicos.user.datoPersonal')->first();

            //$tecnicos = $taller ? $taller->tecnicos : [];
            $tecnicos = Tecnico::where('taller_id', $tallerId)
                ->with('user.datoPersonal')
                ->get();

            return response()->json([
                "tecnicos" => $tecnicos
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al obtener los Tecnicos del Taller.",
                "error" => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteTecnico($id)
    {
        try {
            $tecnico = Tecnico::find($id);

            if (!$tecnico) {
                return response()->json([
                    "message" => "No se encontro el Tecnico."
                ], Response::HTTP_NOT_FOUND);
            }

            $tecnico->delete();

            return response()->json([
                "message" => "Tecnico eliminado exitosamente."
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al eliminar el tecnico.",
                "error" => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
