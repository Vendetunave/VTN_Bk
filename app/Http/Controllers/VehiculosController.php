<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

use App\Models\Vehicles;
use App\Models\imagenes;
use App\Models\Marcas;
use App\Models\Modelos;
use App\Models\Favoritos;
use App\Models\Busquedas;
use App\Models\Imagenes_vehiculo;
use App\Models\TipoVehiculos;
use App\Models\Colores;
use App\Models\Combustibles;
use App\Models\Transmisiones;
use App\Models\TipoPrecio;
use App\Models\ubicacion_departamentos;
use App\Models\ubicacion_ciudades;
use App\Models\TipoMoto;

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
            'ciudad' => $request->query('ciudad') ? $request->query('ciudad') : null,
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
            'vehicles.id', 'vehicles.tipo_moto', 'vehicles.title', 'vehicles.descripcion', 'vehicles.precio','vehicles.condicion', 'vehicles.cilindraje',
            'vehicles.ano', 'vehicles.kilometraje', 'vehicles.placa', 'UC.nombre AS labelCiudad',
            'I.nombre AS nameImage', 'I.extension', 'I.new_image', 'M.nombre AS modelo', 'MA.nombre AS marca', 
            'MO.nombre AS combustible', 'TA.nombre AS transmision', 'TP.nombre AS tipoPrecioLabel'
        );
        if($filtros['categoria'] === 'motos'){
            $selectArray[] = 'TM.nombre AS tipoMotoLabel';
        }
        $result = Vehicles::select(
                $selectArray
            )
            ->join('imagenes AS I', 'I.id_vehicle', \DB::raw('vehicles.id AND I.order = 1'))
            ->join('ubicacion_ciudades AS UC', 'UC.id', 'vehicles.ciudad_id')
            ->join('ubicacion_departamentos AS UD', 'UD.id', 'UC.id_departamento')
            ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->join('tipo_precio AS TP', 'TP.id', 'vehicles.tipo_precio')
            ->join('combustibles AS MO', 'MO.id', 'vehicles.combustible')
            ->join('transmisiones AS TA', 'TA.id', 'vehicles.transmision')
            ->where('vehicles.activo', 1);
        /****/

        
        if($filtros['categoria'] === 'motos'){
            $result->join('tipo_moto AS TM', 'TM.id', 'vehicles.tipo_moto');
        }

        //Filtros complete
        $total_all = $result->groupBy('vehicles.id')->get();
        $collection = collect($total_all);
        $filteredMarcas = $collection;


        if ($filtros['categoria']) {
            $result->where('vehicles.tipo_vehiculo', $this->parse_slug_id($filtros['categoria']));
        }
        
        $marcas_all = $result->groupBy('vehicles.id')->get();
        $collectionMarcas = collect($marcas_all);
        $colector = $collectionMarcas;
        $contadorMarcas = $colector->countBy('marca');
        
        
        //Tag search Filter
        if( $filtros['q'] ){
            $result->where('vehicles.title', 'LIKE', '%'.$filtros['q'].'%');
        }
        if( $filtros['ubicacion'] ){
            $result->where('UD.nombre', $filtros['ubicacion']);
        }
        if( $filtros['ciudad'] ){
            $result->where('UC.nombre', $filtros['ciudad']);
        }
        if( $filtros['motor'] ){
            $result->where('MO.nombre', $filtros['motor']);
        }
        if( $filtros['tipo'] ){
            $result->where('vehicles.tipo_moto', $this->parse_slug_id_tipo($filtros['tipo']));
        }
        //Cateoria Filter
        
        if ($filtros['marca']) {
            $total_modelos = Modelos::select('modelos.id', 'modelos.nombre')
                ->join('marcas AS MA', 'MA.id', 'marca_id')
                ->where('MA.nombre', $filtros['marca'])
                ->get();
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
        $result = $result->groupBy('vehicles.id')->offset(($filtros['page'] - 1) * 20)->limit(20)->get();

        if(isset($total_modelos)){
            $collectionModelos = collect($total_modelos);
            $filteredModelos = $collectionModelos;
        }

        $total_departamentos = ubicacion_departamentos::orderBy('nombre')->get();
        $filterredDepartamentos = collect($total_departamentos);

        if($filtros['ubicacion']){
            $total_ciudades = ubicacion_ciudades::select('ubicacion_ciudades.id', 'ubicacion_ciudades.nombre')
            ->join('ubicacion_departamentos AS UD', 'UD.id', 'ubicacion_ciudades.id_departamento')
            ->where('UD.nombre', $filtros['ubicacion'])
            ->orderBy('ubicacion_ciudades.nombre')
            ->get();
            $filterredCiudades = collect($total_ciudades);
        }

        $contadores = array(
            'marcas' => $contadorMarcas,
            'modelos' => (isset($filteredModelos)) ? $filteredModelos->countBy('nombre') : [],
            'anios' => $filteredMarcas->countBy('ano'),
            'caja' => $filteredMarcas->countBy('transmision'),
            'combustible' => $filteredMarcas->countBy('combustible'),
            'tipo' => array(),
            'ubicacion' => $filterredDepartamentos->countBy('nombre'),
            'ciudad' => (isset($filterredCiudades)) ? $filterredCiudades->countBy('nombre') : []
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
                //Si esta logueado entonces almacena la busqueda
                $existBusqueda = Busquedas::where('user_id', $user->id)->where('vehiculo_id', $vehiculo->id)->get();
                if (COUNT($existBusqueda) == 0) {
                    $busqueda = Busquedas::insert([
                        'user_id' => $user->id,
                        'vehiculo_id' => $vehiculo->id,
                        'fecha' => date('Y-m-d'),
                    ]);
                }
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

    public function modelos($id){
        $modelos = Modelos::select('*')->where('marca_id', $id)->get();
        $result = [
            'modelos' => $modelos,
        ];
        return $result;
    }
    public function marcas($id){
        $marcas = Marcas::select('*')->where('categoria_id', $id)->get();
        $result = [
            'marcas' => $marcas,
        ];
        return $result;
    }

    public function insert(Request $request)
    {
        try {
            $vehiculosSku = Vehicles::all()->count();
            $sku = '000000';
            $vehiculosSku = ($vehiculosSku * 1) + 1;
            if ($vehiculosSku < 10) {
                $sku = '00000' . $vehiculosSku;
            }
            if ($vehiculosSku >= 10 && $vehiculosSku < 100) {
                $sku = '0000' . $vehiculosSku;
            }
            if ($vehiculosSku >= 100 && $vehiculosSku < 1000) {
                $sku = '000' . $vehiculosSku;
            }
            if ($vehiculosSku >= 1000 && $vehiculosSku < 10000) {
                $sku = '00' . $vehiculosSku;
            }
            if ($vehiculosSku >= 10000 && $vehiculosSku < 100000) {
                $sku = '0' . $vehiculosSku;
            }
            if ($vehiculosSku >= 100000) {
                $sku = $vehiculosSku;
            }

            $precioVehiculo = str_replace('.', '', $request->precio_vehiculo);
            $kmVehiculo = str_replace('.', '', $request->kilometraje_vehiculo);
            $cilindrajeVehiculo = str_replace('.', '', $request->cilindraje_vehiculo);

            $vehiculoId = Vehicles::insertGetId([
                'title' => $request->titulo_vehiculo,
                'descripcion' => $request->descripcion_vehiculo,
                'condicion' => $request->estado_vehiculo,
                'precio' => (int) $precioVehiculo,
                'tipo_precio' => $request->tipo_precio_vehiculo,
                'promocion' => $request->promocion,
                'permuta' => $request->permuta,
                'kilometraje' => (int) $kmVehiculo,
                'combustible' => $request->combustible_vehiculo,
                'color' => $request->color_vehiculo,
                'transmision' => $request->transmision_vehiculo,
                'placa' => $request->placa_vehiculo,
                'ciudad_id' => $request->ciudad_vehiculo,
                'vendedor_id' => $request->user_id,
                'activo' => 0,
                'aprobado_promocion' => 0,
                'tipo_vehiculo' => $request->tipo_vehiculo,
                'modelo_id' => $request->modelo,
                'ano' => $request->anio,
                'fecha_creacion' => new DateTime(),
                'fecha_publicacion' => date("Y-m-d H:i:s"),
                'vendido' => 0,
                'contacto' => str_replace(' ', '', $request->contacto_vehiculo),
                'sku' => $sku,
                'cilindraje' => (int) $cilindrajeVehiculo,
                'financiacion' => $request->financiacion,
                'tipo_moto' => ($request->tipo_vehiculo === 5)? 1 : 0,
                'blindado' => ($request->blindado_vehiculo == 2)? 0: $request->blindado_vehiculo,
            ]);

            $images = $request->images;
            foreach ($images as $keyImage => $itemImage) {
                $image = $itemImage;
                $image = str_replace('data:image/jpeg;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $name = uniqid();
                $imageName = $name . '.' . 'jpeg';


                Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $imageName, base64_decode($image), 'public');

                $imageConvert = (string) Image::make(base64_decode($image))->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $name . '.' . 'webp', $imageConvert, 'public');

                if (($keyImage + 1) == 1) {
                    $imageThumb = Image::make(base64_decode($image));
                    $w = $imageThumb->width();
                    $h = $imageThumb->height();
                    if ($w > $h) {
                        $imageThumb->resize(300, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } else {
                        $imageThumb->resize(null, 300, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    }

                    $imageThumbJpeg = $imageThumb;
                    $imageThumb->encode('webp', 100);
                    $imageThumbJpeg->encode('jpeg', 100);

                    Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.webp', $imageThumb, 'public');
                    Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.jpeg', $imageThumbJpeg, 'public');
                }

                $imagenId = imagenes::insertGetId([
                    'nombre' => $name,
                    'path' => 'vendetunave/images/vehiculos/',
                    'extension' => 'jpeg',
                    'order' => ($keyImage + 1),
                    'id_vehicle' => $vehiculoId,
                    'new_image' => (($keyImage + 1) == 1) ? 2 : 1
                ]);

                $imagevehiculo = Imagenes_vehiculo::insert([
                    'id_vehicle' => $vehiculoId,
                    'id_image' => $imagenId
                ]);
            }

            $response = [
                'vehiculoId' => $vehiculoId,
                'status' => true,
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'error' => $th,
                'status' => false,
            ];

            return $response;
        }

    }

    public function edit_vehicle(Request $request)
    {
        try {
            $vehiculo = Vehicles::select(
                'vehicles.*',
                'C.nombre AS combustibleLabel',
                'CO.nombre AS colorLabel',
                'T.nombre AS transmisionLabel',
                'M.nombre AS modeloLabel',
                'MA.nombre AS marcaLabel',
                'MA.id AS marcaId',
                'TV.nombre AS tipoLabel',
                'UC.nombre AS ciudadLabel',
                'UD.nombre AS departamentoLabel',
                'UD.id AS departamento',
                'TP.nombre AS tipoPrecioLabel'
            )
                ->join('tipo_vehiculos AS TV', 'TV.id', 'vehicles.tipo_vehiculo')
                ->join('combustibles AS C', 'C.id', 'vehicles.combustible')
                ->join('colores AS CO', 'CO.id', 'vehicles.color')
                ->join('transmisiones AS T', 'T.id', 'vehicles.transmision')
                ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
                ->join('marcas AS MA', 'MA.id', 'M.marca_id')
                ->join('ubicacion_ciudades AS UC', 'UC.id', 'vehicles.ciudad_id')
                ->join('ubicacion_departamentos AS UD', 'UD.id', 'UC.id_departamento')
                ->join('tipo_precio AS TP', 'TP.id', 'vehicles.tipo_precio')
                ->where('vehicles.id', $request->id)
                ->first();

            if($vehiculo->vendedor_id !== $request->user_id){
                $response = [
                    'status' => true,
                    'intruder' => true,
                    'msj' => 'No deberías estas aquí :)'
                ];

                return $response;
            }

            $categories = TipoVehiculos::all();
            $combustibles = Combustibles::all();
            $colores = Colores::all();
            $transmisiones = Transmisiones::all();
            $tipoPrecio = TipoPrecio::all();
            $departamentos = ubicacion_departamentos::select('*')->orderBy('nombre')->get();
            $ciudades = ubicacion_ciudades::select('*')->where('id_departamento', $vehiculo->departamento)->orderBy('nombre')->get();
            $marcas = Marcas::where('categoria_id', $vehiculo->tipo_vehiculo)
                ->get();
            $modelos = Modelos::where('marca_id', $vehiculo->marcaId)->get();

            $imagenes = imagenes::select(
                \DB::raw('CONCAT("https://vendetunave.s3.amazonaws.com/", imagenes.path, imagenes.nombre, ".", imagenes.extension) AS url, imagenes.id AS imageId'),
                'order'
            )
                ->join('imagenes_vehiculo AS IV', 'IV.id_image', 'imagenes.id')
                ->where('IV.id_vehicle', $request->id)
                ->orderBy('imagenes.order', 'ASC')
                ->get();

            $imagesArray = [];

            for ($i = 0; $i < 10; $i++) {
                $encontrado = false;
                foreach ($imagenes as $item) {
                    if (($i + 1) === $item->order) {
                        array_push($imagesArray, (object) array('url' => $item->url, 'imageId' => $item->imageId, 'order' => $item->order));
                        $encontrado = true;
                        break;
                    }
                }

                if (!$encontrado) {
                    array_push($imagesArray, (object) array('url' => '', 'imageId' => '', 'order' => ($i + 1)));
                }
            }

            $tipoMoto = TipoMoto::all();

            $result = [
                'status' => true,
                'intruder' => false,
                'categories' => $categories,
                'combustibles' => $combustibles,
                'colores' => $colores,
                'transmisiones' => $transmisiones,
                'tipoPrecio' => $tipoPrecio,
                'departamentos' => $departamentos,
                'ciudades' => $ciudades,
                'marcas' => $marcas,
                'tipoMoto' => $tipoMoto,
                'modelos' => $modelos,
                'vehiculo' => $vehiculo,
                'imagenes' => $imagesArray,
            ];

            return $result;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
            ];

            return $response;
        }
    }

    public function update_vehicle(Request $request)
    {
        try {
            $validVehicle = \DB::table('vehicles')->select('vendedor_id', 'precio')->where('id', $request->id)->first();
            if($validVehicle->vendedor_id !== $request->user_id){
                $response = [
                    'status' => true,
                    'intruder' => true,
                    'msj' => 'No deberías estas aquí :)'
                ];

                return $response;
            }

            $precioVehiculo = str_replace('.', '', $request->precio_vehiculo);
            $kmVehiculo = str_replace('.', '', $request->kilometraje_vehiculo);
            $cilindrajeVehiculo = str_replace('.', '', $request->cilindraje_vehiculo);

            $republicar = 0;

            if($validVehicle->precio !== (int) $precioVehiculo){
                $republicar = 1;
            }

            $vehiculos = \DB::table('vehicles')->where('id', $request->id)->update([
                'title' => $request->titulo_vehiculo,
                'descripcion' => $request->descripcion_vehiculo,
                'condicion' => $request->estado_vehiculo,
                'precio' => (int) $precioVehiculo,
                'tipo_precio' => $request->tipo_precio_vehiculo,
                'promocion' => $request->promocion,
                'permuta' => $request->permuta,
                'kilometraje' => (int) $kmVehiculo,
                'combustible' => $request->combustible_vehiculo,
                'color' => $request->color_vehiculo,
                'transmision' => $request->transmision_vehiculo,
                'blindado' => $request->blindado_vehiculo,
                'placa' => $request->placa_vehiculo,
                'ciudad_id' => $request->ciudad_vehiculo,
                'activo' => 0,
                'aprobado_promocion' => 0,
                'tipo_vehiculo' => $request->tipo_vehiculo,
                'modelo_id' => $request->modelo_vehiculo,
                'ano' => $request->anio_vehiculo,
                'vendido' => 0,
                'contacto' => $request->contacto_vehiculo,
                'cilindraje' => (int) $cilindrajeVehiculo,
                'financiacion' => $request->financiacion,
                'tipo_moto' => $request->tipo_moto_select,
                'republicar' => $republicar
            ]);

            // $images = $request->image;
            // foreach ($images as $itemImage) {
            //     $image = $itemImage["uri"];
            //     $image = str_replace('data:image/jpeg;base64,', '', $image);
            //     $image = str_replace(' ', '+', $image);
            //     $name = uniqid();
            //     $imageName = $name . '.' . 'jpeg';


            //     Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $imageName, base64_decode($image), 'public');

            //     $imageConvert = (string) Image::make(base64_decode($image))->encode('webp', 100);
            //     Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $name . '.' . 'webp', $imageConvert, 'public');

            //     if ($itemImage["order"] == 1) {
            //         $imageThumb = Image::make(base64_decode($image));
            //         $w = $imageThumb->width();
            //         $h = $imageThumb->height();
            //         if ($w > $h) {
            //             $imageThumb->resize(300, null, function ($constraint) {
            //                 $constraint->aspectRatio();
            //             });
            //         } else {
            //             $imageThumb->resize(null, 300, function ($constraint) {
            //                 $constraint->aspectRatio();
            //             });
            //         }

            //         $imageThumbJpeg = $imageThumb;
            //         $imageThumb->encode('webp', 100);
            //         $imageThumbJpeg->encode('jpeg', 100);

            //         Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.webp', $imageThumb, 'public');
            //         Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.jpeg', $imageThumbJpeg, 'public');
            //     }

            //     $imagenId = imagenes::insertGetId([
            //         'nombre' => $name,
            //         'path' => 'vendetunave/images/vehiculos/',
            //         'extension' => 'jpeg',
            //         'order' => $itemImage["order"],
            //         'id_vehicle' => $request->id,
            //         'new_image' => ($itemImage["order"] == 1) ? 2 : 1
            //     ]);

            //     $imagevehiculo = Imagenes_vehiculo::insert([
            //         'id_vehicle' => $request->id,
            //         'id_image' => $imagenId
            //     ]);
            // }

            $response = [
                'status' => true,
                'intruder' => false,
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
            ];

            return $response;
        }
    }

}
