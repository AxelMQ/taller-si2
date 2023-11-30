<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SolicitudController extends Controller
{
    //
    public function registerSolicitud(Request $request)
    {
        try {
            // info('Request received:', $request->all());
            $request->validate([
                'nota' => 'nullable',
                'latitud_longitud' => 'nullable',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'audio' => 'nullable|mimes:mp3,wav,ogg,aac|max:5120',

            ]);

            $user = Auth::user();

            if (!$user->cliente) {
                return response()->json([
                    "message" => "El usuario no es un Cliente."
                ], Response::HTTP_BAD_REQUEST);
            }


            $image = $request->file('imagen');
            $audio = $request->file('audio');


            $imageName = null;
            $audioName = null;

            if ($image) {
                $clientId = $user->cliente->id;
                $imagePath = "cliente_$clientId/solicitud_images";
                if (!Storage::exists($imagePath)) {
                    Storage::makeDirectory($imagePath);
                }

                $imageName = 'solicitud_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs($imagePath, $imageName, 'public');
            }

            if ($audio) {
                $audio = $request->file('audio');

                if (!$audio->isValid()) {
                    return response()->json([
                        "message" => "Archivo de audio no válido.",
                    ], Response::HTTP_BAD_REQUEST);
                }

                $allowedAudioTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/aac'];

                $audioMimeType = $audio->getClientMimeType();
                // info('Request received:', $request->all());
                // info('Tipo de MIME de...', [$audioMimeType]);
                // info('allowedAudioTypes:', $allowedAudioTypes);


                if (!in_array($audioMimeType, $allowedAudioTypes)) {
                    return response()->json([
                        "message" => "Tipo de archivo de audio no permitido.",
                        "received_type" => $audioMimeType,
                        "allowed_types" => $allowedAudioTypes,
                    ], Response::HTTP_BAD_REQUEST);
                }
                $clientId = $user->cliente->id;
                $audioPath = "cliente_$clientId/solicitud_audios";
                if (!Storage::exists($audioPath)) {
                    Storage::makeDirectory($audioPath);
                }

                $audioName = 'audio_' . uniqid() . '.' . $audio->getClientOriginalExtension();
                $audioPath = $audio->storeAs($audioPath, $audioName, 'public');
            }

            // ...

            if ($request->has('latitud_longitud')) {
                $latitudLongitud = $request->input('latitud_longitud');

                preg_match_all('/-?\d+\.\d+/', $latitudLongitud, $matches);

                if (count($matches[0]) == 2) {
                    $latitud = $matches[0][0];
                    $longitud = $matches[0][1];
                } else {
                    return response()->json([
                        "message" => 'Formato de latitud_longitud no válido.'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                $latitud = null;
                $longitud = null;
            }

            $solicitud = new Solicitud();
            $solicitud->nota = $request->nota;
            $solicitud->estado = 'Proceso';
            $solicitud->latitud = $latitud;
            $solicitud->longitud = $longitud;
            $solicitud->imagen = $imagePath;
            $solicitud->audio = $audioPath;
            $solicitud->cliente_id = $user->cliente->id;
            $solicitud->save();
            //TODO: 
            // NOTIFCAR A LOS TALLERES.
            $this->notificarTalleres($solicitud);
            // info('solicitud registered successfully:', $solicitud->toArray());
            return response()->json([
                "message" => 'Solictud Registrado exitosamente',
                "solicitud" => $solicitud
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {

            // info('Excepción capturada: ' . $e->getMessage());
            return response()->json([
                "message" => "No se logró Registrar la Solictud",
                "error" => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function notificarTalleres($solicitud)
    {
        //TODO: NOTIFICAR A LOS TALLERES
    }

    public function getSolicitudesEnProceso()
    {
        try {

            $solicitudes = Solicitud::where('estado', 'Proceso')->get();

            return response()->json([
                'message' => 'Solicitudes obtenidas exitosamente',
                'solicitudes' => $solicitudes
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'No se pudieron obtener las solicitudes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSolicitudPorId($id)
    {
        try {
            $solicitud = Solicitud::with('cliente.user.datoPersonal')->find($id);

            if (!$solicitud) {
                return response()->json(['message' => 'Solicitud no encontrada'], 404);
            }

            return response()->json([
                'message' => 'Solicitud obtenida exitosamente',
                'solicitud' => $solicitud
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'No se pudo obtener la solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getAllSolicitudes()
    {
        try {

            $solicitudes = Solicitud::all();

            return response()->json([
                'message' => 'Solicitudes obtenidas exitosamente',
                'solicitudes' => $solicitudes
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'No se pudieron obtener las solicitudes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
