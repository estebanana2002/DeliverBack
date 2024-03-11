<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //
    public function registerUser ( Request $request ) {
        try {
            //code...
            // $data = $request->all();
            $validators = Validator::make($request -> all(),
            [
                'name' =>'required|string|max_length:40',
                'username' =>'required|string|max_length:40',
                'matricula' =>'required|string|max_length:40',
                'email' =>'required|email|max_length:40',
                'password' => 'required|string|min_length:8',
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
