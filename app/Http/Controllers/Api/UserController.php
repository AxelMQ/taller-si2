<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\DatoPersonal;
use App\Models\Taller;
use App\Models\Tecnico;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    //
    public function registrarCliente(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required',
                'email' => 'required|email|unique:datosPersonales',
                'usuario' => 'required|unique:users',
                'password' => ['required', 'confirmed'],

            ], [
                'nombre.required' => 'Dato Requerido',
                'email.requiered' => 'Dato Requerido',
            ]);

            DB::beginTransaction();

            $user = new User();
            $user->usuario = $request->usuario;
            $user->password = bcrypt($request->password);
            $user->tipo = 'cliente';
            $user->save();

            $cliente = new Cliente();
            $cliente->user_id = $user->id;
            $cliente->save();

            $datoPersonales = new DatoPersonal();
            $datoPersonales->nombre = $request->nombre;
            $datoPersonales->email = $request->email;
            $datoPersonales->user_id = $user->id;
            $datoPersonales->save();

            DB::commit();

            return response()->json([
                "message" => "Cuenta creada exitosamente.",
                "usuario" => $user,
                "datoPersonal" => $datoPersonales
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "message" => "No se logro crear su Cuenta, verifique sus datos.",
                "error" => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function registerTaller(Request $request)
    {
        try {
            
            // info('Request received:', $request->all());
            $request->validate([
                'nombre' => 'required',
                'direccion' => 'required',
                'email' => 'required|email|unique:datosPersonales',
                'telefono' => 'required',
                'latitud_longitud' => 'required',
                'password' => ['required', 'confirmed'],
            ]);

            $user = new User();
            $user->usuario = $request->nombre.'_taller';
            $user->password = bcrypt($request->password);
            $user->tipo = 'taller';
            $user->save();

            $datoPersonales = new DatoPersonal();
            $datoPersonales->nombre = $request->nombre;
            $datoPersonales->email = $request->email;
            $datoPersonales->telefono = $request->telefono;
            $datoPersonales->user_id = $user->id;
            $datoPersonales->save();

            $latitudLongitud = $request->latitud_longitud;
            list($latitud, $longitud) = explode(',', $latitudLongitud);

            $taller = new Taller();
            $taller->direccion = $request->direccion;
            $taller->latitud = $latitud;
            $taller->longitud = $longitud;
            $taller->user_id = $user->id;
            $taller->save();

            // info('Car registered successfully:', $car->toArray());
            return response()->json([
                "message" => "Taller Registrado Exitosamente",
                "user" => $user,
                "taller" => $taller,
                "datoPersonal" => $datoPersonales,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "No se logro Registrar el Taller",
                "error" => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function registrarTecnico(Request $request)
    {
        try {
            // info('Request received:', $request->all());
            $request->validate([
                'nombre' => 'required',
                'email' => 'required|email|unique:datosPersonales',
                'telefono' => 'required',
                'password' => ['required', 'confirmed'],
            ]);

            $userTaller = Auth::user();
            
            if (!$userTaller->taller) {
                return response()->json([
                    "message" => "El usuario no tiene un Taller asociado",
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = new User();
            $user->usuario = $request->nombre.'_tec';
            $user->password = bcrypt($request->password);
            $user->tipo = 'tecnico';
            $user->save();

            $datoPersonales = new DatoPersonal();
            $datoPersonales->nombre = $request->nombre;
            $datoPersonales->email = $request->email;
            $datoPersonales->telefono = $request->telefono;
            $datoPersonales->user_id = $user->id;
            $datoPersonales->save();

            $tecnico = new Tecnico();
            $tecnico->estado = 'Disponible';
            $tecnico->user_id = $user->id;
            $tecnico->taller_id = $userTaller->taller->id;
            $tecnico->save();

            // info('TECNICO registered successfully:', $tecnico->toArray());
            return response()->json([
                "message" => "Tecnico Registrado Exitosamente",
                "tecnico" => $user,
                "datoPersonal" => $datoPersonales,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "No se logro Registrar al Tecnico",
                "error" => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function allUsers()
    {
        try {
            $users = User::with(['datoPersonal'])->get();

            return response()->json([
                "users" => $users,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "No se pudieron obtener los Users",
                "error" => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
