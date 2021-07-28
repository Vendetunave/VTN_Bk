<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Users;

use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function profile($id){
        $result = Users::select( 'nombre', 'telefono', 'email', 'genero', 'fecha_nacimiento', 'image')
                ->where('id', $id)
                ->first();
        $result->status = true;
        return $result;
    }
    public function publicaciones(){

    }
    public function favoritos(){

    }
    public function busquedas(){

    }
}
