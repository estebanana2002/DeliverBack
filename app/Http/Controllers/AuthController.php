<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\role;
use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function __construct() {
        // pa dejar pasar al usuario sin haber iniciado sesion, no le valida el token en la funcion login
        $this -> middleware('auth:api', ['except' => ['login', 'registerUser']]);
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
