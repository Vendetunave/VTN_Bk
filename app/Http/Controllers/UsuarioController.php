<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Users;
use App\Models\Busquedas;

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
    public function busquedas(Request $request, $id){

        $page = $request->query('page') ? $request->query('page') : 1;
        $busquedasTotal = Busquedas::leftJoin('vehicles AS V', 'V.id', 'busquedas.vehiculo_id')
            ->where('user_id', $id)
            ->where('V.activo', 1)
            ->count();

        $busquedas = Busquedas::select('busquedas.id', 'busquedas.vehiculo_id', 'busquedas.fecha', 'V.title', 'I.nombre AS nameImage', 'I.extension', 'UC.nombre AS labelCiudad')
            ->leftJoin('vehicles AS V', 'V.id', 'busquedas.vehiculo_id')
            ->leftJoin('ubicacion_ciudades AS UC', 'UC.id', 'V.ciudad_id')
            ->leftJoin('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'V.id')
            ->leftJoin('imagenes AS I', 'I.id', 'IV.id_image')
            ->where('user_id', $id)
            ->where('V.activo', 1)
            ->groupBy('V.id')
            ->orderBy('busquedas.id', 'DESC')
            ->offset(($page - 1) * 10)->limit(10)->get();

        $result = [
            'busquedas' => $busquedas,
            'busquedasTotal' => $busquedasTotal,
            'page' => $page * 1,
        ];

        return $result;
    }
    public function publicaciones(){

    }
    public function favoritos(){

    }
    
}
