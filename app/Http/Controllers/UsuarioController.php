<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Users;
use App\Models\Busquedas;
use App\Models\Respuestas;
use App\Models\Favoritos;
use App\Models\FavoritesDataSheets;
use App\Models\Vehicles;
use App\Models\Accesorios;

use App\Models\TipoVehiculos;
use App\Models\Combustibles;
use App\Models\Colores;
use App\Models\Transmisiones;
use App\Models\TipoPrecio;
use App\Models\TipoMoto;
use App\Models\ubicacion_ciudades;
use App\Models\ubicacion_departamentos;
use App\Models\tipo_accesorio;


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
    public function favoritos(Request $request, $id){

        $vehiculos = Favoritos::select('favoritos.id', 'favoritos.vehiculo_id', 'V.title', 'V.precio', 'I.nombre AS nameImage', 'I.extension', 'UC.nombre AS labelCiudad')
            ->join('vehicles AS V', 'V.id', 'favoritos.vehiculo_id')
            ->join('ubicacion_ciudades AS UC', 'UC.id', 'V.ciudad_id')
            ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'V.id')
            ->join('imagenes AS I', 'I.id', 'IV.id_image')
            ->where('user_id', $id)
            ->where('V.activo', 1)
            ->groupBy('V.id')
            ->orderBy('favoritos.id', 'DESC')
            ->get();
        
        $fichas_tecnicas = FavoritesDataSheets::select()
            ->join('data_sheet AS DS', 'DS.id', 'favorites_data_sheet.datasheet_id')
            ->join('images_data_sheet AS I', 'I.datasheet_id', \DB::raw('DS.id AND I.order = 1'))
            ->where('favorites_data_sheet.user_id', $id)
            ->where('DS.active', 1)
            ->orderBy('favorites_data_sheet.id', 'DESC')
            ->get();

        $result = [
            'vehiculos' => $vehiculos,
            'fichas_tecnicas' => $fichas_tecnicas
        ];
        return $result;

    }
    public function publicaciones(Request $request, $id){
        /****/
        $vehicles = Vehicles::select(
            'vehicles.id',
            'vehicles.title',
            'vehicles.sku',
            'vehicles.ano',
            'vehicles.precio',
            'vehicles.fecha_creacion',
            'vehicles.activo',
            'vehicles.vendido',
            'UC.nombre AS labelCiudad',
            'I.nombre AS nameImage',
            'I.extension',
            'M.nombre AS modeloLabel'
        )
        ->leftJoin('ubicacion_ciudades AS UC', 'UC.id', 'vehicles.ciudad_id')
        ->leftJoin('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
        ->leftJoin('imagenes AS I', 'I.id', 'IV.id_image')
        ->leftJoin('modelos AS M', 'M.id', 'vehicles.modelo_id')
        ->where('vehicles.vendedor_id', $id)
        ->where('vehicles.vendido', 0)
        ->orderBy('vehicles.fecha_creacion', 'DESC')
        ->groupBy('vehicles.id')
        ->get();

        $accesorios = Accesorios::select(
            'accesorios.id',
            'accesorios.title',
            'accesorios.precio',
            'accesorios.fecha_creacion',
            'accesorios.activo',
            'accesorios.vendido',
            'UC.nombre AS labelCiudad',
            'I.nombre AS nameImage',
            'I.extension'
        )
            ->leftJoin('ubicacion_ciudades AS UC', 'UC.id', 'accesorios.ciudad_id')
            ->leftJoin('imagenes_accesorios AS IV', 'IV.accesorio_id', 'accesorios.id')
            ->leftJoin('imagenes AS I', 'I.id', 'IV.image_id')
            ->where('vendedor_id', $id)
            ->where('accesorios.vendido', 0)
            ->orderBy('accesorios.fecha_creacion', 'DESC')
            ->orderBy('accesorios.id', 'DESC')
            ->groupBy('accesorios.id')
            ->get();

        $result = [
            'vehiculos' => $vehicles,
            'accesorios' => $accesorios
        ];
        return $result;

    }
    public function form_producto(Request $request){

        $categories = TipoVehiculos::all();
        $tipoAccesorio = tipo_accesorio::all();
        $combustibles = Combustibles::all();
        $colores = Colores::all();
        $transmisiones = Transmisiones::all();
        $tipoPrecio = TipoPrecio::all();
        $departamentos = ubicacion_departamentos::select('*')->orderBy('nombre')->get();
        $tipoMoto = TipoMoto::all();

        $result = [
            'categories' => $categories,
            'combustibles' => $combustibles,
            'colores' => $colores,
            'transmisiones' => $transmisiones,
            'tipoPrecio' => $tipoPrecio,
            'departamentos' => $departamentos,
            'tipoAccesorio' => $tipoAccesorio,
            'tipoMoto' => $tipoMoto,
        ];
        return $result;
    }
    public function make_comment(Request $request){
        $user = Users::where('id', $request->id)->first();
        if ($user->locked == 0) {
            $respuesta = Respuestas::insert(['pregunta_id' => $request->idPregunta, 'respuesta' => $request->comentario, 'user_id' => $request->id, 'fecha' => date('Y-m-d H:i:s')]);
        }
        $result = [
            'state' => ($user->locked == 0) ? true : false
        ];
        return $result;
    }
}
