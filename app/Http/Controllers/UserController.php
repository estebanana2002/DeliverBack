<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Models\User;

class UserController extends Controller
{
    public function __construct() {
        // pa dejar pasar al usuario sin haber iniciado sesion, no le valida el token en la funcion login
        $this -> middleware('users:api', ['except' => ['getAllUsers', 'registerNewUser']]);
    }

    public function getAllUsers() {
        $users = User::with('role') -> get();
        return response() -> json($users, 200);
    }

    public function registerNewUser( Request $request ) {
        try {
            // return response() -> json($request, 201);
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
            $user -> load('role');

            // $token = JWTAuth::fromUser($user);
            return response() -> json($user, 201);

        } catch (\Exception $th) {
            //throw $th;
            return response() -> json([
                'message' => 'Error al crear el usuario!',
                $th -> getMessage()
        ], 400);
        }
    }



}
