<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{

    public function __constructor() {
        // pa dejar pasar al usuario sin haber iniciado sesion, no le valida en la funcion login
        $this -> middleware('auth:api', ['except' => ['login']]);
    }
    //
    public function registerUser ( Request $request ) {
        try {
            //code...
            $validators = Validator::make($request -> all(),
            [
                'name' =>'required|string',
                'username' =>'required|string',
                'matricula' =>'required|string',
                'email' =>'required|email',
                'password' => 'required|string',
                'role_id' => 'required|integer',
            ],[
                'name.required' => 'El campo nombre es requerido',
                'username.required' => 'El campo usuario es requerido',
                'matricula.required' => 'El campo matricula es requerido',
                'email.required' => 'El campo email es requerido',
                'password.required' => 'El campo password es requerido',
                'role_id.required' => 'El campo role_id es requerido',

            ]);
            if ( $validators -> fails() ) {
                return response() -> json($validators -> errors() -> toJson(), 400);
            }

            $user = User::create([
                'name' => $request -> name,
                'username' => $request -> username,
                'email' => $request -> email,
                'role_id' => $request -> role_id,
                'matricula' => $request -> matricula,
                'password' => bcrypt($request -> password),
            ]);

            $token = JWTAuth::fromUser($user);
            return response() -> json(compact('user', 'token'), 201);

        } catch (\Exception $th) {
            //throw $th;
            return response() -> json(['message' => 'Error al crear el usuario!', $th -> getMessage()], 400);
        }
    }

    public function login(Request $request) {
        try {
            //code...
            if ( !Auth::attemp($request -> only('email', 'password')) ) {
                return response() -> json(['message' => 'Unauthorized'], 401);
            }
            $user = User::where('email', $request['email']) 
            -> addSelect(['role' => role::select('role') -> whereColumn('role_id', 'id')]) -> firstOfFail();

            $token = JWTAuth::fromUser($user);
            Log::info('token generado'. $token);
            
            return response() -> json([
                'message' => 'Success',
                'user' => $user,
                'token' => 'mi token'
            ], 201);

        } catch (\Throwable $th) {
            //throw $th;
            return response() -> json(['message' => 'Error al crear el usuario!',
            $th -> getMessage()], 400);
        };
    }
}
