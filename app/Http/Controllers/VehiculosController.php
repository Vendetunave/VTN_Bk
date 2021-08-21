<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Vehicles;
use App\Models\imagenes;
use App\Models\DataSheet;
use App\Models\Modelos;
use App\Models\ImagesDataSheet;
use App\Models\TipoVehiculos;
use App\Models\Favoritos;

use App\Models\Accesorios;

use DateTime;

use Illuminate\Support\Facades\Auth;

class VehiculosController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function parse_slug_id($slug){
        switch ($slug) {
            case 'carros':
                return 1;
            case 'motos':
                return 5;
            case 'camiones':
                return 2;
            case 'carros_coleccion':
                return 3;
            case 'otros':
                return 6;
        }
    }
    public function parse_slug_id_tipo($slug){
        switch ($slug) {
            case 'Chopper':
                return 1;
            case 'Enduro':
                return 2;
            case 'Naked':
                return 3;
            case 'Cross':
                return 4;
            case 'Cuatrimotos':
                return 5;
            case 'Deportivas':
                return 6;
            case 'Motocarros':
                return 7;
            case 'Motos de Calle':
                return 8;
            case 'Scooters':
                return 9;
            case 'Touring':
                return 10;
            case 'Triciclos':
                return 11;
            case 'Otros Tipos':
                return 12;
        }
    }
    public function find(Request $request)
    {
        $filtros = array(
            'categoria' => $request->query('categoria') ? $request->query('categoria') : null,
            'ubicacion' => $request->query('ubicacion') ? $request->query('ubicacion') : null,
            'marca' => $request->query('marca') ? $request->query('marca') : null,
            'motor' => $request->query('motor') ? $request->query('motor') : null,
            'tipo' => $request->query('tipo') ? $request->query('tipo') : null,
            'modelo' => $request->query('modelo') ? $request->query('modelo') : null,
            'estado' => $request->query('estado') ? $request->query('estado') : null,
            'transmision' => $request->query('transmision') ? $request->query('transmision') : null,
            'kilometraje' => $request->query('kilometraje') ? $request->query('kilometraje') : null,
            'precio' => $request->query('precio') ? $request->query('precio') : null,
            'orden' => $request->query('orden') ? $request->query('orden') : null,
            'page' => $request->query('page') ? $request->query('page') : 1,
            'permuta' => $request->query('permuta') ? $request->query('permuta') : false,
            'promocion' => $request->query('promocion') ? $request->query('promocion') : false,
            'blindaje' => $request->query('blindaje') ? $request->query('blindaje') : false,
            'ano' => $request->query('ano') ? $request->query('ano') : null,
            'anio' => $request->query('anio') ? $request->query('anio') : null,
            'q' => $request->query('q') ? $request->query('q') : null
        );
        $selectArray = array(
            'vehicles.id', 'vehicles.tipo_moto', 'vehicles.title', 'vehicles.precio','vehicles.condicion',
            'vehicles.ano', 'vehicles.kilometraje', 'UC.nombre AS labelCiudad',
            'I.nombre AS nameImage', 'I.extension', 'I.new_image', 'M.nombre AS modelo', 'MA.nombre AS marca', 
            'MO.nombre AS combustible', 'TA.nombre AS transmision'
        );
        if($filtros['categoria'] === 'motos'){
            $selectArray[] = 'TM.nombre AS tipoMotoLabel';
        }
        $result = Vehicles::select(
                $selectArray
            )
            ->join('imagenes AS I', 'I.id_vehicle', \DB::raw('vehicles.id AND I.order = 1'))
            ->join('ubicacion_ciudades AS UC', 'UC.id', 'vehicles.ciudad_id')
            ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->join('combustibles AS MO', 'MO.id', 'vehicles.combustible')
            ->join('transmisiones AS TA', 'TA.id', 'vehicles.transmision')
            if($filtros['categoria'] === 'motos'){
                ->join('tipo_moto AS TM', 'TM.id', 'vehicles.tipo_moto');
            }
            ->where('vehicles.activo', 1);
        //Tag search Filter
        if( $filtros['q'] ){
            $result->where('vehicles.title', 'LIKE', '%'.$filtros['q'].'%');
        }
        if( $filtros['ubicacion'] ){
            $result->where('UC.nombre', $filtros['ubicacion']);
        }
        if( $filtros['motor'] ){
            $result->where('MO.nombre', $filtros['motor']);
        }
        if( $filtros['tipo'] ){
            $result->where('vehicles.tipo_moto', $this->parse_slug_id_tipo($filtros['tipo']));
        }
        //Cateoria Filter
        if ($filtros['categoria']) {
            $result->where('vehicles.tipo_vehiculo', $this->parse_slug_id($filtros['categoria']));
        } else {
            $result->where('vehicles.tipo_vehiculo', 1);
        }
        if ($filtros['marca']) {
            $result->where('MA.nombre', $filtros['marca']);
        }
        if ($filtros['modelo']) {
            $result->where('M.nombre', $filtros['modelo']);
        }
        if ($filtros['transmision']) {
            $result->where('TA.nombre', $filtros['transmision']);
        }
        if ($filtros['ano']) {
            $result->where('ano', $filtros['ano']);
        }
        //
        if ($filtros['promocion']) {
            $parseBoolean = $filtros['promocion'] ? 1 : 0;
            $result->where('vehicles.promocion', $parseBoolean)->where('vehicles.aprobado_promocion', 1);
        }
        if ($filtros['permuta']) {
            $parseBoolean = $filtros['permuta'] ? 1 : 0;
            $result->where('vehicles.permuta', $parseBoolean);
        }
        if ($filtros['blindaje']) {
            $parseBoolean = $filtros['blindaje'] ? 1 : 0;
            $result->where('vehicles.blindado', $parseBoolean);
        }
        //
        if ($filtros['anio']) {
            $decodeParam = $filtros['anio'];
            $arrayPrices = explode(":", $decodeParam);
            $result->whereBetween('ano', $arrayPrices);
        }
        if ($filtros['precio']) {
            $decodeParam = $filtros['precio'];
            $arrayPrices = explode(":", $decodeParam);
            $result->whereBetween('precio', $arrayPrices);
        }
        if ($filtros['kilometraje']) {
            $decodeParam = $filtros['kilometraje'];
            $arrayPrices = explode(":", $decodeParam);
            $result->whereBetween('kilometraje', $arrayPrices);
        }
        switch ($filtros['orden']) {
            case 1:
                $result->orderBy('vehicles.condicion', 'ASC');
                break;
            case 2:
                $result->orderBy('vehicles.condicion', 'DESC');
                break;
            case 3:
                $result->orderBy('vehicles.precio', 'ASC');
                break;
            case 4:
                $result->orderBy('vehicles.precio', 'DESC');
                break;
            default:
                $result->orderBy('vehicles.fecha_publicacion', 'DESC');
        }
        if ($filtros['estado']) {
            //$estado = ($estado == 2) ? 'Usado' : 'Nuevo';
            $result->where('vehicles.condicion', $filtros['estado']);
        }
        $total_records = count($result->groupBy('vehicles.id')->get());
        $total_all = $result->groupBy('vehicles.id')->get();
        $result = $result->groupBy('vehicles.id')->offset(($filtros['page'] - 1) * 20)->limit(20)->get();

        //Filtros complete
        $collection = collect($total_all);
        $filteredMarcas = $collection;

        $contadores = array(
            'marcas' => $filteredMarcas->countBy('marca'),
            'modelos' => $filteredMarcas->countBy('modelo'),
            'anios' => $filteredMarcas->countBy('ano'),
            'caja' => $filteredMarcas->countBy('transmision'),
            'combustible' => $filteredMarcas->countBy('combustible'),
            'tipo' => array(),
            'ubicacion' => $filteredMarcas->countBy('labelCiudad')
        );
        if($filtros['categoria'] === 'motos'){
            $contadores['tipo'] = $filteredMarcas->countBy('tipoMotoLabel');
        }
        $response = [
            'page' => $filtros['page'],
            'total_records' => $total_records,
            'vehicles' => $result,
            'filtros' => $filtros,
            'contadores' => $contadores
        ];

        return $response;
    }
    public function detalle($slug){

            $arrayUrl = explode('-', $slug);
            $id = $arrayUrl[COUNT($arrayUrl) - 1];

            $vehiculoViews = Vehicles::select('views')->where('vehicles.id', $id) ->first();
            if ($vehiculoViews) {
                \DB::table('vehicles')->where('id', $id)->update(['views' => ($vehiculoViews->views + 1)]);
            }

            $vehiculo = Vehicles::select(
                'vehicles.*',
                'C.nombre AS combustibleLabel',
                'CO.nombre AS colorLabel',
                'T.nombre AS transmisionLabel',
                'M.nombre AS modeloLabel',
                'M.id AS modeloId',
                'MA.nombre AS marcaLabel',
                'MA.id AS marcaId',
                'TV.nombre AS tipoLabel',
                'TV.id AS tipoId',
                'TM.nombre AS tipoMotoLabel',
                'TM.id AS tipoMotoId',
                'UC.nombre AS ciudadLabel',
                'UD.nombre AS departamentoLabel',
                'TP.nombre AS tipoPrecioLabel',
                'I.nombre AS nameImage', 'I.extension', 'I.new_image'
            )
                ->join('imagenes AS I', 'I.id_vehicle', \DB::raw('vehicles.id AND I.order = 1'))
                ->join('tipo_vehiculos AS TV', 'TV.id', 'vehicles.tipo_vehiculo')
                ->leftJoin('tipo_moto AS TM', 'TM.id', 'vehicles.tipo_moto')
                ->join('combustibles AS C', 'C.id', 'vehicles.combustible')
                ->join('colores AS CO', 'CO.id', 'vehicles.color')
                ->join('transmisiones AS T', 'T.id', 'vehicles.transmision')
                ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
                ->join('marcas AS MA', 'MA.id', 'M.marca_id')
                ->join('ubicacion_ciudades AS UC', 'UC.id', 'vehicles.ciudad_id')
                ->join('ubicacion_departamentos AS UD', 'UD.id', 'UC.id_departamento')
                ->join('tipo_precio AS TP', 'TP.id', 'vehicles.tipo_precio')
                ->where('vehicles.id', $id)
                ->first();

            $urlCategory = str_replace(' ', '-', $vehiculo->tipoLabel) . '_' . $vehiculo->tipoId;
            $urlTypeMoto = str_replace(' ', '-', $vehiculo->tipoMotoLabel) . '_' . $vehiculo->tipoMotoId;
            $urlMarca = str_replace(' ', '-', $vehiculo->marcaLabel) . '_' . $vehiculo->marcaId;
            $urlModelo = str_replace(' ', '-', $vehiculo->modeloLabel) . '_' . $vehiculo->modeloId;

            $date1 = new DateTime($vehiculo->fecha_publicacion);
            $date2 = new DateTime();
            $diff = $date1->diff($date2);
            $diasPublicado = $diff->days;

            $imagenes = imagenes::select(
                \DB::raw('CONCAT("https://vendetunave.s3.amazonaws.com/", imagenes.path, imagenes.nombre, ".") AS url'),
                'imagenes.extension',
                'imagenes.new_image'
            )
                ->join('imagenes_vehiculo AS IV', 'IV.id_image', 'imagenes.id')
                ->where('IV.id_vehicle', $id)
                ->orderBy('imagenes.order', 'ASC')
                ->get();

            $vehiculosRelacionados = Vehicles::select('vehicles.*', 'I.nombre AS nameImage', 'I.extension', 'I.new_image')
                ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
                ->join('imagenes AS I', 'I.id', 'IV.id_image')
                ->where('activo', 1)
                ->where('vehicles.modelo_id', $vehiculo->modelo_id)
                ->where('vehicles.id', '<>', $vehiculo->id)
                ->groupBy('vehicles.id')
                ->limit(10)
                ->get();

            $vehiculosRelacionadosCount = Vehicles::where('activo', 1)
                ->where('vehicles.modelo_id', $vehiculo->modelo_id)
                ->where('vehicles.id', '<>', $vehiculo->id)
                ->count();

            $vehicleFav = array();

            $user = Auth::user();
            if($user){
                $vehicleFav = Favoritos::where('vehiculo_id', $vehiculo->id)->where('user_id', $user->id)->get();
            }

            $response = [
                'status' => true,
                'vehiculo' => $vehiculo,
                'imagenes' => $imagenes,
                'vehiculosRelacionados' => $vehiculosRelacionados,
                'vehiculosRelacionadosCount' => $vehiculosRelacionadosCount,
                'vehicleFav' => $vehicleFav,
                'diasPublicado' => $diasPublicado,
                'urlCategory' => $urlCategory,
                'urlTypeMoto' => $urlTypeMoto,
                'urlMarca' => $urlMarca,
                'urlModelo' => $urlModelo
            ];

            return $response;
    }
    public function fichas_tecnicas(Request $request){
        $filtros = array(
            'categoria' => $request->query('categoria') ? $request->query('categoria') : null,
            'ubicacion' => $request->query('ubicacion') ? $request->query('ubicacion') : null,
            'marca' => $request->query('marca') ? $request->query('marca') : null,
            'motor' => $request->query('motor') ? $request->query('motor') : null,
            'modelo' => $request->query('modelo') ? $request->query('modelo') : null,
            'estado' => $request->query('estado') ? $request->query('estado') : null,
            'transmision' => $request->query('transmision') ? $request->query('transmision') : null,
            'kilometraje' => $request->query('kilometraje') ? $request->query('kilometraje') : null,
            'precio' => $request->query('precio') ? $request->query('precio') : null,
            'orden' => $request->query('orden') ? $request->query('orden') : null,
            'page' => $request->query('page') ? $request->query('page') : 1,
            'q' => $request->query('q') ? $request->query('q') : null
        );
        $result = DataSheet::select(
            'data_sheet.id',
            'data_sheet.title',
            'data_sheet.description',
            'data_sheet.price',
            'data_sheet.year',
            'data_sheet.autonomy',
            'data_sheet.engine',
            'data_sheet.power',
            'C.nombre AS combustibleLabel',
            'T.nombre AS transmisionLabel',
            'I.name AS nameImage',
            'I.ext AS extension',
            'TP.nombre AS tipo',
            'MA.nombre AS marca',
            \DB::raw('2 AS new_image')
        )
            ->join('images_data_sheet AS I', 'I.datasheet_id', \DB::raw('data_sheet.id AND I.order = 1'))
            ->join('combustibles AS C', 'C.id', 'data_sheet.fuel_id')
            ->join('transmisiones AS T', 'T.id', 'data_sheet.transmission_id')
            ->join('tipo_vehiculos AS TP', 'TP.id', 'data_sheet.vehicle_type_id')
            ->join('modelos AS M', 'M.id', 'data_sheet.model_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->where('data_sheet.active', 1);
        
        if( $filtros['q'] ){
            $result->where('data_sheet.title', 'LIKE', '%'.$filtros['q'].'%');
        }

        switch ($filtros['orden']) {
            case 3:
                $result->orderBy('data_sheet.price', 'ASC');
                break;
            case 4:
                $result->orderBy('data_sheet.price', 'DESC');
                break;
            default:
                $result->orderBy('data_sheet.id', 'DESC');
        }
        $total_records = count($result->groupBy('data_sheet.id')->get());
        $total_all = $result->groupBy('data_sheet.id')->get();
        $result = $result->groupBy('data_sheet.id')->offset(($filtros['page'] - 1) * 20)->limit(20)->get();

        //Filtros complete
        $collection = collect($total_all);
        $filteredMarcas = $collection;

        $contadores = array(
            'tipo' => $filteredMarcas->countBy('tipo'),
            'caja' => $filteredMarcas->countBy('transmisionLabel'),
            'marca' => $filteredMarcas->countBy('marca'),
            'combustible' => $filteredMarcas->countBy('combustibleLabel')
        );
        $response = [
            'page' => $filtros['page'],
            'total_records' => $total_records,
            'vehicles' => $result,
            'filtros' => $filtros,
            'contadores' => $contadores
        ];

        return $response;
    }
    public function accesorios(Request $request){
        $filtros = array(
            'categoria' => $request->query('categoria') ? $request->query('categoria') : null,
            'ubicacion' => $request->query('ubicacion') ? $request->query('ubicacion') : null,
            'marca' => $request->query('marca') ? $request->query('marca') : null,
            'motor' => $request->query('motor') ? $request->query('motor') : null,
            'modelo' => $request->query('modelo') ? $request->query('modelo') : null,
            'estado' => $request->query('estado') ? $request->query('estado') : null,
            'transmision' => $request->query('transmision') ? $request->query('transmision') : null,
            'kilometraje' => $request->query('kilometraje') ? $request->query('kilometraje') : null,
            'precio' => $request->query('precio') ? $request->query('precio') : null,
            'orden' => $request->query('orden') ? $request->query('orden') : null,
            'page' => $request->query('page') ? $request->query('page') : 1
        );
        $result = Accesorios::select('accesorios.*', 'TP.nombre AS tipoAcc', 'I.nombre AS nameImage', 'I.extension', 'UC.nombre as ciudad')
            ->join('tipo_accesorio AS TP', 'TP.id', 'accesorios.tipo_accesorio')
            ->join('ubicacion_ciudades AS UC', 'UC.id', 'accesorios.ciudad_id')
            ->join('ubicacion_departamentos AS UD', 'UD.id', 'UC.id_departamento')
            ->join('imagenes_accesorios AS IA', 'IA.accesorio_id', 'accesorios.id')
            ->join('imagenes AS I', 'I.id', 'IA.image_id')
            ->where('activo', 1);
        
        switch ($filtros['orden']) {
            case 1:
                $result->orderBy('accesorios.condicion', 'ASC');
                break;
            case 2:
                $result->orderBy('accesorios.condicion', 'DESC');
                break;
            case 3:
                $result->orderBy('accesorios.precio', 'ASC');
                break;
            case 4:
                $result->orderBy('accesorios.precio', 'DESC');
                break;
            default:
                $result->orderBy('accesorios.id', 'DESC');
        }
        $total_records = count($result->groupBy('accesorios.id')->get());
        $total_all = $result->groupBy('accesorios.id')->get();
        $result = $result->groupBy('accesorios.id')->offset(($filtros['page'] - 1) * 20)->limit(20)->get();

        //Filtros complete
        $collection = collect($total_all);
        $filteredMarcas = $collection;

        $contadores = array(
            'tipo' => $filteredMarcas->countBy('tipoAcc'),
            'ciudad' => $filteredMarcas->countBy('ciudad'),
            'estado' => $filteredMarcas->countBy('condicion')
        );
        $response = [
            'page' => $filtros['page'],
            'total_records' => $total_records,
            'vehicles' => $result,
            'filtros' => $filtros,
            'contadores' => $contadores
        ];

        return $response;
    }
    public function accesorio($slug){
        $arrayUrl = explode('-', $slug);
        $id = $arrayUrl[COUNT($arrayUrl) - 1];
        try {
            $accesorio = Accesorios::select(
                'accesorios.*',
                'TA.nombre AS tipoLabel',
                'UC.nombre AS ciudadLabel',
                'UD.nombre AS departamentoLabel',
                'TP.nombre AS tipoPrecioLabel',
                'U.telefono'
            )
                ->leftJoin('users AS U', 'U.id', 'accesorios.vendedor_id')
                ->leftJoin('tipo_accesorio AS TA', 'TA.id', 'accesorios.tipo_accesorio')
                ->leftJoin('ubicacion_ciudades AS UC', 'UC.id', 'accesorios.ciudad_id')
                ->leftJoin('ubicacion_departamentos AS UD', 'UD.id', 'UC.id_departamento')
                ->leftJoin('tipo_precio AS TP', 'TP.id', 'accesorios.tipo_precio')
                ->where('accesorios.id', $id)
                ->first();
            $date1 = new DateTime($accesorio->fecha_creacion);
            $date2 = new DateTime();
            $diff = $date1->diff($date2);
            $diasPublicado = $diff->days;

            $imagenes = imagenes::select(
                \DB::raw('CONCAT("https://vendetunave.s3.amazonaws.com/", imagenes.path, imagenes.nombre, ".") AS url, imagenes.id AS imageId'),
                    'imagenes.extension',
                    'imagenes.new_image'
                )
                ->leftJoin('imagenes_accesorios AS IV', 'IV.image_id', 'imagenes.id')
                ->where('IV.accesorio_id', $id)
                ->get();

            $response = [
                'status' => true,
                'vehiculo' => $accesorio,
                'id' => $id,
                'imagenes' => $imagenes,
                'diasPublicado' => $diasPublicado,
                'vehiculosRelacionados' => [],
                'vehicleFav' => [],
                'vehicleFavRelacionados' => [],
            ];
            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
            ];
            return $response;
        }
    }
    public function ficha_tecnica($slug){
        $arrayUrl = explode('-', $slug);
        $id = $arrayUrl[COUNT($arrayUrl) - 1];

        $vehiculo = DataSheet::select(
            'data_sheet.*',
            'C.nombre AS combustibleLabel',
            'T.nombre AS transmisionLabel',
            'M.nombre AS modeloLabel',
            'MA.nombre AS marcaLabel',
            'TV.nombre AS tipoLabel',
            'I.name AS nameImage',
            'I.ext AS extension',
            \DB::raw('2 AS new_image')
        )
            ->join('images_data_sheet AS I', 'I.datasheet_id', \DB::raw('data_sheet.id AND I.order = 1'))
            ->join('tipo_vehiculos AS TV', 'TV.id', 'data_sheet.vehicle_type_id')
            ->join('combustibles AS C', 'C.id', 'data_sheet.fuel_id')
            ->join('transmisiones AS T', 'T.id', 'data_sheet.transmission_id')
            ->join('modelos AS M', 'M.id', 'data_sheet.model_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->where('data_sheet.id', $id)
            ->first();

        \DB::table('data_sheet')->where('id', $id)->update([
            'views' => $vehiculo->views + 1,
        ]);

        $imagenes = ImagesDataSheet::select(
            \DB::raw('CONCAT("https://vendetunave.s3.amazonaws.com/", path, name, ".") AS url'),
            'ext AS extension',
            \DB::raw('2 AS new_image')
        )
            ->where('datasheet_id', $id)
            ->orderBy('order', 'ASC')
            ->get();

        $vehiculosRelacionados = Vehicles::select('vehicles.*', 'I.nombre AS nameImage', 'I.extension', 'I.new_image')
            ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
            ->join('imagenes AS I', 'I.id', 'IV.id_image')
            ->where('activo', 1)
            ->where('vehicles.modelo_id', $vehiculo->model_id)
            ->groupBy('vehicles.id')
            ->limit(10)
            ->get();

        $vehiculosRelacionadosCount = Vehicles::where('activo', 1)
            ->where('vehicles.modelo_id', $vehiculo->model_id)
            ->count();

        $response = [
            'vehicle' => $vehiculo,
            'views' => $vehiculo->views + 1,
            'imagenes' => $imagenes,
            'vehiculosRelacionados' => $vehiculosRelacionados,
            'vehiculosRelacionadosCount' => $vehiculosRelacionadosCount
        ];

        return $response;

    }
    public function modelos($id){
        $modelos = Modelos::select('*')->where('marca_id', $id)->get();
        $result = [
            'modelos' => $modelos,
        ];
        return $result;
    }
}
