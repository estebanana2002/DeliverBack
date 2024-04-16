<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Models\User;

class UserController extends Controller
{
    public function __construct() {
        // pa dejar pasar al usuario sin haber iniciado sesion, no le valida el token en la funcion login
        $this -> middleware('users:api', ['except' => ['getAllUsers', 'getById', 'registerNewUser', 'editUser', 'deleteUser']]);
    }

    public function getAllUsers() {
        $users = User::with('role') -> get();
        return response() -> json($users, 200);
    }

    public function getById( int $id ) {
        $user = User::find($id);
        if ( is_null($user) ) {
            return response() -> json('User not found', 404);
        }
        return response() -> json($user, 200);
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
                'role_id' => ['required', 'integer'],
            ], [
                'name.required' => 'El nombre es requerido',
                'username.required' => 'El username es requerido',
                'username.unique' => 'Este username ya fue registrado',
                'matricula.required' => 'La matricula es requerida',
                'matricula.unique' => 'Esta matricula ya fue registrada',
                'email.required' => 'El correo es requerido',
                'email.unique' => 'Este correo ya fue registrado',
                'password.required' => 'La contraseña es requerida',
                'role_id.required' => 'El rol es requerido',
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
            $errors = [];
            if ($th instanceof ValidationException) {
                $errors = $th -> errors();
            }
            return response() -> json([
                'message' => 'Error al crear el usuario!',
                'errors' => $errors
            ], 400);
        }
    }

    public function editUser( Request $request ) {
        $user = User::find($request -> id);
        if ( is_null($user) ) {
            return response() -> json('User not found', 404);
        }

        $user -> update([
            'name' => $request -> name,
            'username' => $request -> username,
            'email' => $request -> email,
            'role_id' => $request -> role_id,
            'matricula' => $request -> matricula,
        ]);

        $user -> load('role');
        return response() -> json($user, 200);
    }

    public function deleteUser(int $id) {
        $user = User::find($id);
        if ( is_null($user) ) {
            return response() -> json('User not found', 404);
        }

        $user -> delete();

        return response() -> json('User deleted', 200);
    }

}
