<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Taller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:sanctum')->except('login', 'logout');
    // }

    public function login(Request $request)
    {
        try {

            info('Request received:', $request->all());
            $request->validate([
                'usuario' => 'required',
                'password' => 'required',

            ]);

            $user = User::where('usuario', $request->usuario)->first();

            if (!$user) {
                return response()->json([
                    "message" => "Usuario no encontrado",
                ], Response::HTTP_NOT_FOUND);
            }

            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('token')->plainTextToken;
                $cookie = cookie('cookie_token', $token, 60 * 24);

                return response()->json([
                    "message" => "Inicio de sesión exitoso.",
                    "token" => $token,
                    "tipo" => $user->tipo,
                    "user" => $user,
                ], Response::HTTP_OK)->withCookie($cookie);

                info('User login successfully:', $user->toArray());
            } else {
                return response()->json([
                    "message" => "Contraseña incorrecta",
                ], Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {

            return response()->json([
                "message" => "Error al verificar la contraseña.",
                "error" => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout exitoso',
        ]);
    }

    public function userInfo()
    {
        try {
            $user = Auth::user();

            if ($user) {
                $userData = User::with('datoPersonal')->find($user->id);

                if ($user->tipo === 'taller') {
                    $userTaller = User::with('taller')->find($user->id);
                    return response()->json([
                        'message' => 'Información del usuario obtenida exitosamente',
                        'user' => $userData,
                        'taller' => $userTaller->taller,
                    ], Response::HTTP_OK);
                }

                if ($user->tipo === 'tecnico') {
                    $userTecnico = User::with('tecnico', 'tecnico.taller')->find($user->id);

                    $tallerId = $userTecnico->tecnico->taller_id;

                    $tallerInfo = Taller::find($tallerId);
                    
                    $userTaller = User::with('datoPersonal')->find($tallerInfo->user_id);

                    return response()->json([
                        'message' => 'Información del usuario obtenida exitosamente',
                        'user' => $userData,
                        'tecnico' => $userTecnico->tecnico,
                        'taller' => $userTaller,
                    ], Response::HTTP_OK);
                }

                return response()->json([
                    'message' => 'Información del usuario obtenida exitosamente',
                    'user' => $userData,
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'Usuario no autenticado',
                ], Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la información del usuario',
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
