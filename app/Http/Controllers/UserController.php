<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\role;
use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{

    public function __construct() {
        // pa dejar pasar al usuario sin haber iniciado sesion, no le valida el token en la funcion login
        $this -> middleware('auth:api', ['except' => ['login', 'registerUser']]);
    }
    //
    public function registerUser( Request $request ) {
        try {
            //code...
            $request -> validate([
                'name' => ['required', 'string'],
                'username' => ['required', 'string', 'unique:'.User::class],
                'matricula' => ['required', 'string', 'unique:'.User::class],
                'email' => ['required', 'email', 'unique:'.User::class],
                'password' => ['required', 'string'],
                // 'password' => ['required', 'regex: /^(?=.*[A-Z])(?!(\d)\1|\d{2})(?=.*\d.*\d)(?!.*(\d)\2)[A-Za-z\d]{5,}$/'],
                'role_id' => ['required', 'integer'],
            ], [
                'name.required' => 'El campo nombre es requerido',
                'username.required' => 'El campo usuario es requerido',
                'username.unique' => 'ya hay un username registrado',
                'matricula.required' => 'El campo matricula es requerido',
                'matricula.unique' => 'Esta matricula ya fue registrada',
                'email.required' => 'El campo email es requerido',
                'email.unique' => 'Este correo ya fue registrado',
                'password.required' => 'El campo password es requerido',
                'role_id.required' => 'El campo role_id es requerido',
            ]);

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
            return response() -> json([
                'message' => 'Error al crear el usuario!',
                $th -> getMessage()
        ], 400);
        }
    }

    public function login(Request $request) {
        try {
            //code...
            if ( !Auth::attempt($request -> only('email', 'password')) ) {
                return response() -> json(['message' => 'Unauthorized'], 401);
            }
            $user = User::where('email', $request['email'])
            -> addSelect(['role' => role::select('role') -> whereColumn('role_id', 'id')]) -> firstOrFail();

            $token = JWTAuth::fromUser($user);
            Log::info('token generado'. $token);

            return response() -> json([
                'message' => 'Success',
                'user' => $user,
                'token' => $this -> respondWithToken($token)
            ], 201);

        } catch (\Throwable $th) {
            //throw $th;
            return response() -> json(['message' => 'algo salio mal!',
            $th -> getMessage()], 400);
        };
    }

    public function logout() {
        $user = Auth::user();
        $user_token = $user -> tokens();
        $user_token -> delete();
        return response() -> json(['message' => 'Logged out']);
    }

    protected function respondWithToken($token)
    {
        $expiration = JWTAuth::factory()->getTTL() * 60;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiration,
            'expiration_date' => now()->addSeconds($expiration)->toDateTimeString(),
        ]);
    }
}
