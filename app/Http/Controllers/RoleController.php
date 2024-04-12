<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    //

    public function __construct() {

    }

    public function getAllRoles() {
        $roles = Role::all();
        return response()->json($roles);
    }
}
