<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

use App\Models\Users;
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
    public function parse_slug_id($slug)
    {
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
    public function parse_slug_id_tipo($slug)
    {
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
            'q' => $request->query('q') ? $request->query('q') : null,
            'vendedor' => $request->query('vendedor') ? $request->query('vendedor') : null
        );
        $selectArray = array(
            'vehicles.id', 'vehicles.tipo_moto', 'vehicles.title', 'vehicles.descripcion', 'vehicles.precio', 'vehicles.condicion',
            'vehicles.cilindraje', 'vehicles.ano', 'vehicles.kilometraje', 'vehicles.placa', 'vehicles.fecha_publicacion', 'UC.nombre AS labelCiudad',
            'I.nombre AS nameImage', 'I.extension', 'I.new_image', 'M.nombre AS modelo', 'MA.nombre AS marca',
            'MO.nombre AS combustible', 'TA.nombre AS transmision', 'TP.nombre AS tipoPrecioLabel',
            \DB::raw('IF(vehicles.financiacion = 1, TRUE, FALSE) AS financiacion'),
            \DB::raw('IF(vehicles.confiable = 1, TRUE, FALSE) AS confiable'),
            \DB::raw('IF(vehicles.blindado = 1, TRUE, FALSE) AS blindado'),
            \DB::raw('IF(vehicles.permuta = 1, TRUE, FALSE) AS permuta'),
            \DB::raw('IF(vehicles.promocion = 1, TRUE, FALSE) AS promocion'),
            \DB::raw('IF(vehicles.peritaje <> "", vehicles.peritaje, FALSE) AS peritaje'),
            'vehicles.premium',
            'vehicles.active_premium',
        );

        if ($filtros['vendedor'] || $filtros['categoria'] === 'motos') {
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

        if ($filtros['vendedor'] || $filtros['categoria'] === 'motos') {
            $result->leftJoin('tipo_moto AS TM', 'TM.id', 'vehicles.tipo_moto');
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
        if (!$filtros['vendedor'] && $filtros['q']) {
            $url = "https://suggestqueries.google.com/complete/search?output=toolbar&hl=es&q=" .  str_replace(" ", "%20", $filtros['q'])  . "&gl=co";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents

            $data = curl_exec($ch); // execute curl request
            curl_close($ch);

            $xml = simplexml_load_string(utf8_encode($data));
            $json = json_encode($xml);
            $array = json_decode($json, TRUE);
            $resultSuggestion = [
                $filtros['q'],
                str_replace(" ", "-", $filtros['q']),
                str_replace("-", " ", $filtros['q']),
                str_replace("-", "", $filtros['q']),
                str_replace(" -", "-", $filtros['q']),
                str_replace("- ", "-", $filtros['q']),
                str_replace(" - ", "", $filtros['q']),
                str_replace(" -", "", $filtros['q']),
                str_replace("- ", "", $filtros['q']),
                str_replace(" -", " ", $filtros['q']),
                str_replace("- ", " ", $filtros['q']),
                str_replace(" - ", " ", $filtros['q']),
                str_replace(" - ", "-", $filtros['q']),
                str_replace(" ", "", $filtros['q'])
            ];

            $portionsSearch = explode(" ", $filtros['q']);
            $reverseSearch = '';
            foreach (array_reverse($portionsSearch) as $item) {
                $reverseSearch .= $item . ' ';
            }

            array_push($resultSuggestion, $reverseSearch);
            array_push($resultSuggestion, str_replace(" ", "-", $reverseSearch));
            array_push($resultSuggestion, str_replace("-", " ", $reverseSearch));
            array_push($resultSuggestion, str_replace("-", "", $reverseSearch));
            array_push($resultSuggestion, str_replace(" -", "-", $reverseSearch));
            array_push($resultSuggestion, str_replace("- ", "-", $reverseSearch));
            array_push($resultSuggestion, str_replace(" - ", "", $reverseSearch));
            array_push($resultSuggestion, str_replace(" -", "", $reverseSearch));
            array_push($resultSuggestion, str_replace("- ", "", $reverseSearch));
            array_push($resultSuggestion, str_replace(" -", " ", $reverseSearch));
            array_push($resultSuggestion, str_replace("- ", " ", $reverseSearch));
            array_push($resultSuggestion, str_replace(" - ", " ", $reverseSearch));
            array_push($resultSuggestion, str_replace(" - ", "-", $reverseSearch));
            array_push($resultSuggestion, str_replace(" ", "", $reverseSearch));

            $foundMatches = array();
            $maxNumber = 0;
            preg_match_all('/([1-9]\d*|0)(,\d+)?/', $filtros['q'], $foundMatches);
            foreach ($foundMatches as $foundMatche) {
                foreach ($foundMatche as $match) {
                    if ($match !== "" && (int) $match > $maxNumber) $maxNumber = (int) $match;
                }
            }

            if (strlen($maxNumber) === 4) {
                $searchWithoutYear = str_replace($maxNumber, "", $filtros['q']);
                $searchWithoutYearReverse = str_replace($maxNumber, "", $reverseSearch);
                array_push($resultSuggestion, str_replace(" ", "-", $searchWithoutYear));
                array_push($resultSuggestion, str_replace("-", " ", $searchWithoutYear));
                array_push($resultSuggestion, str_replace("-", "", $searchWithoutYear));
                array_push($resultSuggestion, str_replace(" -", "-", $searchWithoutYear));
                array_push($resultSuggestion, str_replace("- ", "-", $searchWithoutYear));
                array_push($resultSuggestion, str_replace(" - ", "", $searchWithoutYear));
                array_push($resultSuggestion, str_replace(" -", "", $searchWithoutYear));
                array_push($resultSuggestion, str_replace("- ", "", $searchWithoutYear));
                array_push($resultSuggestion, str_replace(" -", " ", $searchWithoutYear));
                array_push($resultSuggestion, str_replace("- ", " ", $searchWithoutYear));
                array_push($resultSuggestion, str_replace(" - ", " ", $searchWithoutYear));
                array_push($resultSuggestion, str_replace(" - ", "-", $searchWithoutYear));
                array_push($resultSuggestion, str_replace(" ", "", $searchWithoutYear));

                array_push($resultSuggestion, str_replace(" ", "-", $searchWithoutYearReverse));
                array_push($resultSuggestion, str_replace("-", " ", $searchWithoutYearReverse));
                array_push($resultSuggestion, str_replace("-", "", $searchWithoutYearReverse));
                array_push($resultSuggestion, str_replace(" -", "-", $searchWithoutYearReverse));
                array_push($resultSuggestion, str_replace("- ", "-", $searchWithoutYearReverse));
                array_push($resultSuggestion, str_replace(" - ", "", $searchWithoutYearReverse));
                array_push($resultSuggestion, str_replace(" -", "", $searchWithoutYearReverse));
                array_push($resultSuggestion, str_replace("- ", "", $searchWithoutYearReverse));
                array_push($resultSuggestion, str_replace(" -", " ", $searchWithoutYearReverse));
                array_push($resultSuggestion, str_replace("- ", " ", $searchWithoutYearReverse));
                array_push($resultSuggestion, str_replace(" - ", " ", $searchWithoutYearReverse));
                array_push($resultSuggestion, str_replace(" - ", "-", $searchWithoutYearReverse));
                array_push($resultSuggestion, str_replace(" ", "", $searchWithoutYearReverse));
                $result->where('ano', $maxNumber);
            }

            for ($i = 0; $i <= strlen($filtros['q']); $i++) {
                array_push($resultSuggestion, substr($filtros['q'], 0, $i) . '.' . substr($filtros['q'], $i));
            }
            for ($i = 0; $i <= strlen($filtros['q']); $i++) {
                array_push($resultSuggestion, substr($filtros['q'], 0, $i) . '-' . substr($filtros['q'], $i));
            }
            for ($i = 0; $i <= strlen($filtros['q']); $i++) {
                array_push($resultSuggestion, substr($filtros['q'], 0, $i) . '/' . substr($filtros['q'], $i));
            }
            for ($i = 0; $i <= strlen($filtros['q']); $i++) {
                array_push($resultSuggestion, substr($filtros['q'], 0, $i) . '?' . substr($filtros['q'], $i));
            }
            for ($i = 0; $i <= strlen($filtros['q']); $i++) {
                array_push($resultSuggestion, substr($filtros['q'], 0, $i) . '¿' . substr($filtros['q'], $i));
            }
            for ($i = 0; $i <= strlen($filtros['q']); $i++) {
                array_push($resultSuggestion, substr($filtros['q'], 0, $i) . '!' . substr($filtros['q'], $i));
            }
            for ($i = 0; $i <= strlen($filtros['q']); $i++) {
                array_push($resultSuggestion, substr($filtros['q'], 0, $i) . '¡' . substr($filtros['q'], $i));
            }
            for ($i = 0; $i <= strlen($filtros['q']); $i++) {
                array_push($resultSuggestion, substr($filtros['q'], 0, $i) . '=' . substr($filtros['q'], $i));
            }

            foreach ($array["CompleteSuggestion"] as $item) {
                array_push($resultSuggestion, $item["suggestion"]["@attributes"]["data"]);
            }

            $result->Where(function ($query) use ($resultSuggestion) {
                foreach ($resultSuggestion as $suggestion) {
                    $query->orWhere('vehicles.title', 'LIKE', '%' . rtrim(ltrim($suggestion)) . '%');
                }
            });
        }
        if (!$filtros['vendedor'] && $filtros['ubicacion']) {
            $result->where('UD.nombre', $filtros['ubicacion']);
        }
        if (!$filtros['vendedor'] && $filtros['ciudad']) {
            $result->where('UC.nombre', $filtros['ciudad']);
        }
        if ($filtros['motor']) {
            $result->where('MO.nombre', $filtros['motor']);
        }
        if ($filtros['tipo']) {
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

        if ($filtros['estado']) {
            //$estado = ($estado == 2) ? 'Usado' : 'Nuevo';
            $result->where('vehicles.condicion', $filtros['estado']);
        }

        if ($filtros['vendedor']) {
            $vendedor = explode("-", $filtros['vendedor']);
            $result->where('vehicles.vendedor_id', $vendedor[COUNT($vendedor) - 1]);
        }

        if(
            $filtros['q'] ||
            $filtros['ubicacion'] ||
            $filtros['ciudad'] ||
            $filtros['motor'] ||
            $filtros['tipo'] ||
            $filtros['marca'] ||
            $filtros['modelo'] ||
            $filtros['transmision'] ||
            $filtros['ano'] ||
            $filtros['promocion'] ||
            $filtros['permuta'] ||
            $filtros['blindaje'] ||
            $filtros['anio'] ||
            $filtros['precio'] ||
            $filtros['kilometraje'] ||
            $filtros['estado'] ||
            $filtros['vendedor']
        ) {
            $result->orderBy('active_premium', 'DESC');
        }

        switch ($filtros['orden']) {
            case 1:
                $result->orderBy('vehicles.condicion', 'ASC');
                $result->orderBy('vehicles.fecha_publicacion', 'DESC');
                break;
            case 2:
                $result->orderBy('vehicles.condicion', 'DESC');
                $result->orderBy('vehicles.fecha_publicacion', 'DESC');
                break;
            case 3:
                $result->orderBy('vehicles.precio', 'ASC');
                $result->orderBy('vehicles.fecha_publicacion', 'DESC');
                break;
            case 4:
                $result->orderBy('vehicles.precio', 'DESC');
                $result->orderBy('vehicles.fecha_publicacion', 'DESC');
                break;
            default:
                $result->orderBy('vehicles.fecha_publicacion', 'DESC');
        }

        $total_records = count($result->groupBy('vehicles.id')->get());
        $result = $result->groupBy('vehicles.id')->offset(($filtros['page'] - 1) * 20)->limit(20)->get();
        
        if (isset($total_modelos)) {
            $collectionModelos = collect($total_modelos);
            $filteredModelos = $collectionModelos;
        }

        $total_departamentos = ubicacion_departamentos::orderBy('nombre')->get();
        $filterredDepartamentos = collect($total_departamentos);

        if (!$filtros['vendedor'] && $filtros['ubicacion']) {
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
        if (!$filtros['vendedor'] && $filtros['categoria'] === 'motos') {
            $contadores['tipo'] = $filteredMarcas->countBy('tipoMotoLabel');
        }

        $vendedor = null;
        if ($filtros['vendedor']) {
            $vendedorId = explode("-", $filtros['vendedor']);
            $vendedor = Users::select('id', 'nombre', 'facebook', 'instagram', 'tiktok', 'image', 'telefono', 'website')
            ->where('id', $vendedorId[COUNT($vendedorId) - 1])
            ->first();
        }

        $response = [
            'page' => $filtros['page'],
            'total_records' => $total_records,
            'vehicles' => $result,
            'vendedor' => $vendedor,
            'filtros' => $filtros,
            'contadores' => $contadores
        ];

        return $response;
    }
    public function detalle($slug)
    {

        $arrayUrl = explode('-', $slug);
        $id = $arrayUrl[COUNT($arrayUrl) - 1];

        $vehiculoViews = Vehicles::select('views')->where('vehicles.id', $id)->first();
        if ($vehiculoViews) {
            \DB::table('vehicles')->where('id', $id)->update(['views' => ($vehiculoViews->views + 1)]);

            $vehiculo = Vehicles::select(
                'vehicles.*',
                \DB::raw('IF(vehicles.financiacion = 1, TRUE, FALSE) AS financiacion'),
                \DB::raw('IF(vehicles.confiable = 1, TRUE, FALSE) AS confiable'),
                \DB::raw('IF(vehicles.blindado = 1, TRUE, FALSE) AS blindado'),
                \DB::raw('IF(vehicles.permuta = 1, TRUE, FALSE) AS permuta'),
                \DB::raw('IF(vehicles.promocion = 1, TRUE, FALSE) AS promocion'),
                \DB::raw('IF(vehicles.peritaje <> "", CONCAT("https://vendetunave.s3.amazonaws.com/vendetunave/pdf/peritaje/", vehicles.peritaje), FALSE) AS peritaje'),
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
                'I.nombre AS nameImage',
                'I.extension',
                'I.new_image',
                'U.id AS sellerId',
                'U.nombre AS sellerName'
            )
                ->join('users AS U', 'U.id', 'vehicles.vendedor_id')
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
                \DB::raw('CONCAT("https://d3bmp4azzreq60.cloudfront.net/fit-in/2000x2000/", imagenes.path, imagenes.nombre, ".") AS url'),
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
                ->where('vehicles.activo', 1)
                ->where('vehicles.modelo_id', $vehiculo->modelo_id)
                ->where('vehicles.id', '<>', $vehiculo->id)
                ->orderBy('vehicles.active_premium', 'DESC')
                ->groupBy('vehicles.id')
                ->limit(10)
                ->get();

            $vehiculosRelacionadosCount = Vehicles::where('activo', 1)
                ->where('vehicles.modelo_id', $vehiculo->modelo_id)
                ->where('vehicles.id', '<>', $vehiculo->id)
                ->count();

            $vehicleFav = array();

            $user = Auth::user();
            if ($user) {
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
                'vehicleExists' => true,
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

        $response = [
            'status' => true,
            'vehicleExists' => false
        ];

        return $response;
    }

    public function modelos($id)
    {
        $modelos = Modelos::select('*')->where('marca_id', $id)->get();
        $result = [
            'modelos' => $modelos,
        ];
        return $result;
    }
    public function marcas($id)
    {
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

            $user = \DB::table('users')->where('id', $request->user_id)->first();

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
                'vendedor_id' => $user->id,
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
                'tipo_moto' => ($request->tipo_vehiculo === 5) ? 1 : 0,
                'blindado' => ($request->blindado_vehiculo == 2) ? 0 : $request->blindado_vehiculo,
                'peritaje' => ($request->peritaje == 0) ? null : $request->peritaje,
                'confiable' => $user->confiable
            ]);

            $images = $request->images;
            foreach ($images as $keyImage => $itemImage) {
                \DB::table('imagenes')->where('id', $itemImage['id'])->update([
                    'order' => ($keyImage + 1),
                    'id_vehicle' => $vehiculoId,
                ]);

                Imagenes_vehiculo::insert([
                    'id_vehicle' => $vehiculoId,
                    'id_image' => $itemImage['id']
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
                \DB::raw('IF(vehicles.peritaje <> "", CONCAT("https://vendetunave.s3.amazonaws.com/vendetunave/pdf/peritaje/", vehicles.peritaje), FALSE) AS peritaje'),
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

            if ($vehiculo->vendedor_id !== $request->user_id) {
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
            if ($validVehicle->vendedor_id !== $request->user_id) {
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

            if ($validVehicle->precio !== (int) $precioVehiculo) {
                $republicar = 1;
            }

            \DB::table('vehicles')->where('id', $request->id)->update([
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
                'modelo_id' => $request->modelo,
                'ano' => $request->anio,
                'vendido' => 0,
                'contacto' => $request->contacto_vehiculo,
                'cilindraje' => (int) $cilindrajeVehiculo,
                'financiacion' => $request->financiacion,
                'tipo_moto' => $request->tipo_moto,
                'republicar' => $republicar,
                'peritaje' => $request->peritaje,
            ]);

            $images = $request->images;
            $imagesOld = \DB::table('imagenes')->where('id_vehicle', $request->id)->get();

            foreach ($imagesOld as $imageOld) {
                $encontrada = 0;
                foreach ($images as $itemImage) {
                    if ($imageOld->id === $itemImage['id']) $encontrada = 1;
                }

                if ($encontrada == 0) {
                    \DB::table('imagenes')->where('id', $imageOld->id)->delete();
                }
            }
            \DB::table('imagenes_vehiculo')->where('id_vehicle', $request->id)->delete();

            foreach ($images as $keyImage => $itemImage) {
                \DB::table('imagenes')->where('id', $itemImage['id'])->update([
                    'order' => ($keyImage + 1),
                    'id_vehicle' => $request->id,
                ]);

                Imagenes_vehiculo::insert([
                    'id_vehicle' => $request->id,
                    'id_image' => $itemImage['id']
                ]);
            }

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

    public function remove_vehicle(Request $request)
    {
        try {
            $busquedas = \DB::table('busquedas')
                ->where('vehiculo_id', $request->id)
                ->delete();

            $favoritos = \DB::table('favoritos')
                ->where('vehiculo_id', $request->id)
                ->delete();

            $vehicle = \DB::table('vehicles')
                ->where('id', $request->id)
                ->delete();

            $images = \DB::table('imagenes')
                ->where('id_vehicle', $request->id)
                ->delete();

            return ['status' => true];
        } catch (\Throwable $th) {
            return ['status' => false];
        }
    }

    public function sold_vehicle(Request $request)
    {
        try {
            $vehicle = \DB::table('vehicles')
                ->where('id', $request->id)
                ->update(['vendido' => 1, 'activo' => 0]);

            $response = [
                'status' => true,
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
            ];

            return $response;
        }
    }

    public function upload_vehicle_image(Request $request)
    {
        try {
            $image = $request->source;
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $name = uniqid();
            $imageName = $name . '.' . 'jpeg';


            Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $imageName, base64_decode($image), 'public');

            $imageConvert = (string) Image::make(base64_decode($image))->encode('webp', 100);
            Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $name . '.' . 'webp', $imageConvert, 'public');

            $imageThumb = Image::make(base64_decode($image));

            $imageThumbJpeg = $imageThumb;
            $imageThumb->encode('webp', 100);
            $imageThumbJpeg->encode('jpeg', 100);

            Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.webp', $imageThumb, 'public');
            Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.jpeg', $imageThumbJpeg, 'public');

            $imagenId = imagenes::insertGetId([
                'nombre' => $name,
                'path' => 'vendetunave/images/vehiculos/',
                'extension' => 'jpeg',
                'order' => 1,
                'id_vehicle' => 0,
                'new_image' => 2
            ]);


            $urlBase = 'https://vendetunave.s3.amazonaws.com';

            $response = [
                'status' => true,
                'imagen_id' => $imagenId,
                'url_image_jpeg' => $urlBase . '/vendetunave/images/vehiculos/' . $imageName,
                'url_image_webp' => $urlBase . '/vendetunave/images/vehiculos/' . $name . '.webp',
                'url_thumbnail_jpeg' => $urlBase . '/vendetunave/images/thumbnails/' . $name . '300x300.jpeg',
                'url_thumbnail_webp' => $urlBase . '/vendetunave/images/thumbnails/' . $name . '300x300.webp',
            ];

            return $response;
        } catch (\Throwable $th) {
            return ['status' => false];
        }
    }

    public function get_all_vehicles()
    {
        $vehicles = Vehicles::select('vehicles.id', 'vehicles.kilometraje', 'vehicles.ano', 'vehicles.confiable', 'vehicles.title', 'vehicles.precio', 'I.nombre AS nameImage', 'MA.nombre AS nombreMarca', 'M.nombre AS nombreModelo', 'premium')
            ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
            ->join('imagenes AS I', 'I.id', 'IV.id_image')
            ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->where('activo', 1)
            ->where('vendido', 0)
            ->groupBy('vehicles.id')
            ->orderBy('vehicles.fecha_creacion', 'DESC')
            ->get();

        $vehiclesApprove = Vehicles::select('vehicles.id', 'vehicles.kilometraje', 'vehicles.ano', 'vehicles.confiable', 'vehicles.title', 'vehicles.precio', 'I.nombre AS nameImage', 'MA.nombre AS nombreMarca', 'M.nombre AS nombreModelo', 'premium')
            ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
            ->join('imagenes AS I', 'I.id', 'IV.id_image')
            ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->where('activo', 0)
            ->where('vendido', 0)
            ->groupBy('vehicles.id')
            ->orderBy('vehicles.fecha_creacion', 'DESC')
            ->get();

        $vehiclesPromotional = Vehicles::select('vehicles.id', 'vehicles.kilometraje', 'vehicles.ano', 'vehicles.confiable', 'vehicles.title', 'vehicles.precio', 'I.nombre AS nameImage', 'MA.nombre AS nombreMarca', 'M.nombre AS nombreModelo', 'premium')
            ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
            ->join('imagenes AS I', 'I.id', 'IV.id_image')
            ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->where('promocion', 1)
            ->where('aprobado_promocion', 0)
            ->where('activo', 1)
            ->groupBy('vehicles.id')
            ->orderBy('vehicles.fecha_creacion', 'DESC')
            ->get();

        $response = [
            'vehicles' => $vehicles,
            'vehiclesApprove' => $vehiclesApprove,
            'vehiclesPromotional' => $vehiclesPromotional,
        ];

        return $response;
    }

    public function get_by_vehicle(Request $request)
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

            $categories = TipoVehiculos::all();
            $combustibles = Combustibles::all();
            $colores = Colores::all();
            $transmisiones = Transmisiones::all();
            $tipoPrecio = TipoPrecio::all();
            $departamentos = ubicacion_departamentos::orderBy('nombre')->get();
            $ciudades = ubicacion_ciudades::all();
            $marcas = Marcas::all();
            $modelos = Modelos::all();

            $imagenes = imagenes::select(
                \DB::raw('CONCAT("https://d3bmp4azzreq60.cloudfront.net/fit-in/2500x2500/", imagenes.path, imagenes.nombre, ".webp") AS url, imagenes.id AS imageId'),
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

    public function update_vehicle_admin(Request $request)
    {
        try {
            $validVehicle = \DB::table('vehicles')->select('precio', 'fecha_publicacion', 'peritaje')->where('id', $request->id)->first();
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
            $fechaPublicacion = $validVehicle->fecha_publicacion;

            if ((int) $validVehicle->precio !== (int) $request->precio) {
                $fechaPublicacion = date("Y-m-d H:i:s");
            }

            \DB::table('vehicles')->where('id', $request->id)->update([
                'title' => $request->title,
                'descripcion' => $request->descripcion,
                'condicion' => $request->condicion,
                'precio' => (int) $request->precio,
                'tipo_precio' => (int) $request->tipo_precio,
                'kilometraje' => (int) $request->kilometraje,
                'cilindraje' => (int) $request->cilindraje,
                'contacto' => (int) $request->contacto,
                'combustible' => $request->combustible,
                'color' => $request->color,
                'transmision' => $request->transmision,
                'tipo_vehiculo' => $request->tipo_vehiculo,
                'modelo_id' => $request->modelo_id,
                'ano' => $request->ano,
                'financiacion' => $request->financiacion,
                'promocion' => $request->promocion,
                'permuta' => $request->permuta,
                'blindado' => $request->blindado,
                'ciudad_id' => $request->ciudad_id,
                'confiable' => $request->confiable,
                'fecha_publicacion' => $fechaPublicacion,
                'republicar' => 0
            ]);

            if ($request->hasFile('image1')) {
                $image = $request->image1;
                $name = uniqid();

                $imageConvert = (string) Image::make($image)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $name . '.' . 'webp', $imageConvert, 'public');

                $imageThumb = Image::make($image);

                $imageThumb->encode('webp', 100);

                Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.webp', $imageThumb, 'public');

                $imageId = imagenes::insertGetId([
                    'nombre' => $name,
                    'path' => 'vendetunave/images/vehiculos/',
                    'extension' => 'webp',
                    'order' => 1,
                    'id_vehicle' => $request->id,
                    'new_image' => 2
                ]);
                Imagenes_vehiculo::insert([
                    'id_vehicle' => $request->id,
                    'id_image' => $imageId
                ]);
                if ($imagesArray[0]->imageId !== '') {
                    imagenes::where('id', $imagesArray[0]->imageId)->delete();
                    Imagenes_vehiculo::where('id_image', $imagesArray[0]->imageId)->delete();
                }
            }
            if ($request->hasFile('image2')) {
                $image = $request->image2;
                $name = uniqid();

                $imageConvert = (string) Image::make($image)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $name . '.' . 'webp', $imageConvert, 'public');

                $imageThumb = Image::make($image);

                $imageThumb->encode('webp', 100);

                Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.webp', $imageThumb, 'public');

                $imageId = imagenes::insertGetId([
                    'nombre' => $name,
                    'path' => 'vendetunave/images/vehiculos/',
                    'extension' => 'webp',
                    'order' => 2,
                    'id_vehicle' => $request->id,
                    'new_image' => 2
                ]);
                Imagenes_vehiculo::insert([
                    'id_vehicle' => $request->id,
                    'id_image' => $imageId
                ]);
                if ($imagesArray[1]->imageId !== '') {
                    imagenes::where('id', $imagesArray[1]->imageId)->delete();
                    Imagenes_vehiculo::where('id_image', $imagesArray[1]->imageId)->delete();
                }
            }
            if ($request->hasFile('image3')) {
                $image = $request->image3;
                $name = uniqid();

                $imageConvert = (string) Image::make($image)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $name . '.' . 'webp', $imageConvert, 'public');

                $imageThumb = Image::make($image);

                $imageThumb->encode('webp', 100);

                Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.webp', $imageThumb, 'public');

                $imageId = imagenes::insertGetId([
                    'nombre' => $name,
                    'path' => 'vendetunave/images/vehiculos/',
                    'extension' => 'webp',
                    'order' => 3,
                    'id_vehicle' => $request->id,
                    'new_image' => 2
                ]);
                Imagenes_vehiculo::insert([
                    'id_vehicle' => $request->id,
                    'id_image' => $imageId
                ]);
                if ($imagesArray[2]->imageId !== '') {
                    imagenes::where('id', $imagesArray[2]->imageId)->delete();
                    Imagenes_vehiculo::where('id_image', $imagesArray[2]->imageId)->delete();
                }
            }
            if ($request->hasFile('image4')) {
                $image = $request->image4;
                $name = uniqid();

                $imageConvert = (string) Image::make($image)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $name . '.' . 'webp', $imageConvert, 'public');

                $imageThumb = Image::make($image);

                $imageThumb->encode('webp', 100);

                Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.webp', $imageThumb, 'public');

                $imageId = imagenes::insertGetId([
                    'nombre' => $name,
                    'path' => 'vendetunave/images/vehiculos/',
                    'extension' => 'webp',
                    'order' => 4,
                    'id_vehicle' => $request->id,
                    'new_image' => 2
                ]);
                Imagenes_vehiculo::insert([
                    'id_vehicle' => $request->id,
                    'id_image' => $imageId
                ]);
                if ($imagesArray[3]->imageId !== '') {
                    imagenes::where('id', $imagesArray[3]->imageId)->delete();
                    Imagenes_vehiculo::where('id_image', $imagesArray[3]->imageId)->delete();
                }
            }
            if ($request->hasFile('image5')) {
                $image = $request->image5;
                $name = uniqid();

                $imageConvert = (string) Image::make($image)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $name . '.' . 'webp', $imageConvert, 'public');

                $imageThumb = Image::make($image);

                $imageThumb->encode('webp', 100);

                Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.webp', $imageThumb, 'public');

                $imageId = imagenes::insertGetId([
                    'nombre' => $name,
                    'path' => 'vendetunave/images/vehiculos/',
                    'extension' => 'webp',
                    'order' => 5,
                    'id_vehicle' => $request->id,
                    'new_image' => 2
                ]);
                Imagenes_vehiculo::insert([
                    'id_vehicle' => $request->id,
                    'id_image' => $imageId
                ]);
                if ($imagesArray[4]->imageId !== '') {
                    imagenes::where('id', $imagesArray[4]->imageId)->delete();
                    Imagenes_vehiculo::where('id_image', $imagesArray[4]->imageId)->delete();
                }
            }
            if ($request->hasFile('image6')) {
                $image = $request->image6;
                $name = uniqid();

                $imageConvert = (string) Image::make($image)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $name . '.' . 'webp', $imageConvert, 'public');

                $imageThumb = Image::make($image);

                $imageThumb->encode('webp', 100);

                Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.webp', $imageThumb, 'public');

                $imageId = imagenes::insertGetId([
                    'nombre' => $name,
                    'path' => 'vendetunave/images/vehiculos/',
                    'extension' => 'webp',
                    'order' => 6,
                    'id_vehicle' => $request->id,
                    'new_image' => 2
                ]);
                Imagenes_vehiculo::insert([
                    'id_vehicle' => $request->id,
                    'id_image' => $imageId
                ]);
                if ($imagesArray[5]->imageId !== '') {
                    imagenes::where('id', $imagesArray[5]->imageId)->delete();
                    Imagenes_vehiculo::where('id_image', $imagesArray[5]->imageId)->delete();
                }
            }
            if ($request->hasFile('image7')) {
                $image = $request->image7;
                $name = uniqid();

                $imageConvert = (string) Image::make($image)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $name . '.' . 'webp', $imageConvert, 'public');

                $imageThumb = Image::make($image);

                $imageThumb->encode('webp', 100);

                Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.webp', $imageThumb, 'public');

                $imageId = imagenes::insertGetId([
                    'nombre' => $name,
                    'path' => 'vendetunave/images/vehiculos/',
                    'extension' => 'webp',
                    'order' => 7,
                    'id_vehicle' => $request->id,
                    'new_image' => 2
                ]);
                Imagenes_vehiculo::insert([
                    'id_vehicle' => $request->id,
                    'id_image' => $imageId
                ]);
                if ($imagesArray[6]->imageId !== '') {
                    imagenes::where('id', $imagesArray[6]->imageId)->delete();
                    Imagenes_vehiculo::where('id_image', $imagesArray[6]->imageId)->delete();
                }
            }
            if ($request->hasFile('image8')) {
                $image = $request->image8;
                $name = uniqid();

                $imageConvert = (string) Image::make($image)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $name . '.' . 'webp', $imageConvert, 'public');

                $imageThumb = Image::make($image);

                $imageThumb->encode('webp', 100);

                Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.webp', $imageThumb, 'public');

                $imageId = imagenes::insertGetId([
                    'nombre' => $name,
                    'path' => 'vendetunave/images/vehiculos/',
                    'extension' => 'webp',
                    'order' => 8,
                    'id_vehicle' => $request->id,
                    'new_image' => 2
                ]);
                Imagenes_vehiculo::insert([
                    'id_vehicle' => $request->id,
                    'id_image' => $imageId
                ]);
                if ($imagesArray[7]->imageId !== '') {
                    imagenes::where('id', $imagesArray[7]->imageId)->delete();
                    Imagenes_vehiculo::where('id_image', $imagesArray[7]->imageId)->delete();
                }
            }
            if ($request->hasFile('image9')) {
                $image = $request->image9;
                $name = uniqid();

                $imageConvert = (string) Image::make($image)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $name . '.' . 'webp', $imageConvert, 'public');

                $imageThumb = Image::make($image);

                $imageThumb->encode('webp', 100);

                Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.webp', $imageThumb, 'public');

                $imageId = imagenes::insertGetId([
                    'nombre' => $name,
                    'path' => 'vendetunave/images/vehiculos/',
                    'extension' => 'webp',
                    'order' => 9,
                    'id_vehicle' => $request->id,
                    'new_image' => 2
                ]);
                Imagenes_vehiculo::insert([
                    'id_vehicle' => $request->id,
                    'id_image' => $imageId
                ]);
                if ($imagesArray[8]->imageId !== '') {
                    imagenes::where('id', $imagesArray[8]->imageId)->delete();
                    Imagenes_vehiculo::where('id_image', $imagesArray[8]->imageId)->delete();
                }
            }
            if ($request->hasFile('image10')) {
                $image = $request->image10;
                $name = uniqid();

                $imageConvert = (string) Image::make($image)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/vehiculos/' . $name . '.' . 'webp', $imageConvert, 'public');

                $imageThumb = Image::make($image);

                $imageThumb->encode('webp', 100);

                Storage::disk('s3')->put('vendetunave/images/thumbnails/' . $name . '300x300.webp', $imageThumb, 'public');

                $imageId = imagenes::insertGetId([
                    'nombre' => $name,
                    'path' => 'vendetunave/images/vehiculos/',
                    'extension' => 'webp',
                    'order' => 10,
                    'id_vehicle' => $request->id,
                    'new_image' => 2
                ]);
                Imagenes_vehiculo::insert([
                    'id_vehicle' => $request->id,
                    'id_image' => $imageId
                ]);
                if ($imagesArray[9]->imageId !== '') {
                    imagenes::where('id', $imagesArray[9]->imageId)->delete();
                    Imagenes_vehiculo::where('id_image', $imagesArray[9]->imageId)->delete();
                }
            }

            if ($request->hasFile('peritaje')) {
                $pdfName = uniqid() . '.' . 'pdf';
                Storage::disk('s3')->put('vendetunave/pdf/peritaje/' . $pdfName, file_get_contents($request->peritaje), 'public');

                \DB::table('vehicles')->where('id', $request->id)->update([
                    'peritaje' => $pdfName,
                ]);
            }

            $response = [
                'status' => true,
                'message' => 'Datos actualizados correctamente!'
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => strval($th)
            ];

            return $response;
        }
    }

    public function dependable_vehicle(Request $request)
    {
        $vehiculo = Vehicles::where('id', $request->id)->first();

        \DB::table('vehicles')->where('id', $request->id)
            ->update(['confiable' => ($vehiculo->confiable) ? 0 : 1]);

        $result = [
            'status' => true,
            'dependable' => ($vehiculo->confiable) ? false : true
        ];

        return $result;
    }

    public function approve_vehicle(Request $request)
    {
        if ($request->approve) {
            $validVehicle = Vehicles::select('republicar', 'fecha_publicacion')->where('id', $request->id)->first();

            \DB::table('vehicles')->where('id', $request->id)->update([
                'activo' => 1,
                'fecha_publicacion' => ($validVehicle->republicar === 1) ? date("Y-m-d H:i:s") : $validVehicle->fecha_publicacion
            ]);
        } else {
            \DB::table('vehicles')
                ->where('id', $request->id)
                ->update(['activo' => 2]);
        }

        $vehicles = Vehicles::select('vehicles.id', 'vehicles.kilometraje', 'vehicles.ano', 'vehicles.confiable', 'vehicles.title', 'vehicles.precio', 'I.nombre AS nameImage', 'MA.nombre AS nombreMarca', 'M.nombre AS nombreModelo', 'premium')
            ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
            ->join('imagenes AS I', 'I.id', 'IV.id_image')
            ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->where('activo', 1)
            ->where('vendido', 0)
            ->groupBy('vehicles.id')
            ->orderBy('vehicles.fecha_creacion', 'DESC')
            ->get();

        $vehiclesApprove = Vehicles::select('vehicles.id', 'vehicles.kilometraje', 'vehicles.ano', 'vehicles.confiable', 'vehicles.title', 'vehicles.precio', 'I.nombre AS nameImage', 'MA.nombre AS nombreMarca', 'M.nombre AS nombreModelo', 'premium')
            ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
            ->join('imagenes AS I', 'I.id', 'IV.id_image')
            ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->where('activo', 0)
            ->where('vendido', 0)
            ->groupBy('vehicles.id')
            ->orderBy('vehicles.fecha_creacion', 'DESC')
            ->get();

        $vehiclesPromotional = Vehicles::select('vehicles.id', 'vehicles.kilometraje', 'vehicles.ano', 'vehicles.confiable', 'vehicles.title', 'vehicles.precio', 'I.nombre AS nameImage', 'MA.nombre AS nombreMarca', 'M.nombre AS nombreModelo', 'premium')
            ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
            ->join('imagenes AS I', 'I.id', 'IV.id_image')
            ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->where('promocion', 1)
            ->where('aprobado_promocion', 0)
            ->where('activo', 1)
            ->groupBy('vehicles.id')
            ->orderBy('vehicles.fecha_creacion', 'DESC')
            ->get();

        $response = [
            'status' => true,
            'message' => 'Datos actualizados correctamente!',
            'vehicles' => $vehicles,
            'vehiclesApprove' => $vehiclesApprove,
            'vehiclesPromotional' => $vehiclesPromotional,
        ];

        return $response;
    }

    public function approve_promotion(Request $request)
    {
        if ($request->approve) {
            \DB::table('vehicles')->where('id', $request->id)
                ->update(['aprobado_promocion' => 1]);
        } else {
            \DB::table('vehicles')->where('id', $request->id)
                ->update(['aprobado_promocion' => 2, 'promocion' => 0]);
        }

        $vehicles = Vehicles::select('vehicles.id', 'vehicles.kilometraje', 'vehicles.ano', 'vehicles.confiable', 'vehicles.title', 'vehicles.precio', 'I.nombre AS nameImage', 'MA.nombre AS nombreMarca', 'M.nombre AS nombreModelo', 'premium')
            ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
            ->join('imagenes AS I', 'I.id', 'IV.id_image')
            ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->where('activo', 1)
            ->where('vendido', 0)
            ->groupBy('vehicles.id')
            ->orderBy('vehicles.fecha_creacion', 'DESC')
            ->get();

        $vehiclesApprove = Vehicles::select('vehicles.id', 'vehicles.kilometraje', 'vehicles.ano', 'vehicles.confiable', 'vehicles.title', 'vehicles.precio', 'I.nombre AS nameImage', 'MA.nombre AS nombreMarca', 'M.nombre AS nombreModelo', 'premium')
            ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
            ->join('imagenes AS I', 'I.id', 'IV.id_image')
            ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->where('activo', 0)
            ->where('vendido', 0)
            ->groupBy('vehicles.id')
            ->orderBy('vehicles.fecha_creacion', 'DESC')
            ->get();

        $vehiclesPromotional = Vehicles::select('vehicles.id', 'vehicles.kilometraje', 'vehicles.ano', 'vehicles.confiable', 'vehicles.title', 'vehicles.precio', 'I.nombre AS nameImage', 'MA.nombre AS nombreMarca', 'M.nombre AS nombreModelo', 'premium')
            ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
            ->join('imagenes AS I', 'I.id', 'IV.id_image')
            ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->where('promocion', 1)
            ->where('aprobado_promocion', 0)
            ->where('activo', 1)
            ->groupBy('vehicles.id')
            ->orderBy('vehicles.fecha_creacion', 'DESC')
            ->get();

        $response = [
            'status' => true,
            'message' => 'Datos actualizados correctamente!',
            'vehicles' => $vehicles,
            'vehiclesApprove' => $vehiclesApprove,
            'vehiclesPromotional' => $vehiclesPromotional,
        ];

        return $response;
    }

    public function remove_vehicle_admin(Request $request)
    {
        try {
            $images = Imagenes_vehiculo::select('id_image')->where('id_vehicle', $request->id)->get();
            Busquedas::where('vehiculo_id', $request->id)->delete();
            Favoritos::where('vehiculo_id', $request->id)->delete();
            Vehicles::where('id', $request->id)->delete();
            Imagenes_vehiculo::where('id_vehicle', $request->id)->delete();

            foreach ($images as $image) {
                imagenes::where('id', $image->id_image)->delete();
            }

            $vehicles = Vehicles::select('vehicles.id', 'vehicles.kilometraje', 'vehicles.ano', 'vehicles.confiable', 'vehicles.title', 'vehicles.precio', 'I.nombre AS nameImage', 'MA.nombre AS nombreMarca', 'M.nombre AS nombreModelo')
                ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
                ->join('imagenes AS I', 'I.id', 'IV.id_image')
                ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
                ->join('marcas AS MA', 'MA.id', 'M.marca_id')
                ->where('activo', 1)
                ->where('vendido', 0)
                ->groupBy('vehicles.id')
                ->orderBy('vehicles.fecha_creacion', 'DESC')
                ->get();

            $vehiclesApprove = Vehicles::select('vehicles.id', 'vehicles.kilometraje', 'vehicles.ano', 'vehicles.confiable', 'vehicles.title', 'vehicles.precio', 'I.nombre AS nameImage', 'MA.nombre AS nombreMarca', 'M.nombre AS nombreModelo')
                ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
                ->join('imagenes AS I', 'I.id', 'IV.id_image')
                ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
                ->join('marcas AS MA', 'MA.id', 'M.marca_id')
                ->where('activo', 0)
                ->where('vendido', 0)
                ->groupBy('vehicles.id')
                ->orderBy('vehicles.fecha_creacion', 'DESC')
                ->get();

            $vehiclesPromotional = Vehicles::select('vehicles.id', 'vehicles.kilometraje', 'vehicles.ano', 'vehicles.confiable', 'vehicles.title', 'vehicles.precio', 'I.nombre AS nameImage', 'MA.nombre AS nombreMarca', 'M.nombre AS nombreModelo')
                ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
                ->join('imagenes AS I', 'I.id', 'IV.id_image')
                ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
                ->join('marcas AS MA', 'MA.id', 'M.marca_id')
                ->where('promocion', 1)
                ->where('aprobado_promocion', 0)
                ->where('activo', 1)
                ->groupBy('vehicles.id')
                ->orderBy('vehicles.fecha_creacion', 'DESC')
                ->get();

            $response = [
                'status' => true,
                'message' => 'Datos actualizados correctamente!',
                'vehicles' => $vehicles,
                'vehiclesApprove' => $vehiclesApprove,
                'vehiclesPromotional' => $vehiclesPromotional,
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => strval($th)
            ];

            return $response;
        }
    }

    public function upload_vehicle_peritaje(Request $request)
    {
        try {
            if ($request->hasFile('peritaje')) {
                $pdfName = uniqid() . '.' . 'pdf';
                Storage::disk('s3')->put('vendetunave/pdf/peritaje/' . $pdfName, file_get_contents($request->peritaje), 'public');

                $response = [
                    'status' => true,
                    'file_name' => $pdfName,
                ];

                return $response;
            }

            return ['status' => false];
        } catch (\Throwable $th) {
            return ['status' => $th];
        }
    }

    public function images_vehicle(Request $request)
    {
        try {
            $imagenes = imagenes::select(
                \DB::raw('CONCAT("https://vendetunave.s3.amazonaws.com/", imagenes.path, imagenes.nombre, ".", imagenes.extension) AS download'),
                \DB::raw('CONCAT(imagenes.nombre, ".", imagenes.extension) AS filename')
            )
                ->join('imagenes_vehiculo AS IV', 'IV.id_image', 'imagenes.id')
                ->where('IV.id_vehicle', $request->id)
                ->orderBy('imagenes.order', 'ASC')
                ->get();

            $response = [
                'status' => true,
                'imagenes' => $imagenes,
            ];

            return $response;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
