<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Vehicles;
use App\Models\imagenes;

use DateTime;

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
    public function find(Request $request)
    {
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
            'orden' => $request->query('orden') ? $request->query('orden') : null
        );
        $page = 1;
        $result = Vehicles::select(
                'vehicles.id', 'vehicles.title', 'vehicles.precio','vehicles.condicion',
                'vehicles.ano', 'vehicles.kilometraje', 'UC.nombre AS labelCiudad',
                'I.nombre AS nameImage', 'I.extension', 'I.new_image', 'M.nombre AS modelo', 'MA.nombre AS marca', 
                'MO.nombre AS combustible', 'TA.nombre AS transmision'
            )
            ->join('imagenes AS I', 'I.id_vehicle', \DB::raw('vehicles.id AND I.order = 1'))
            ->join('ubicacion_ciudades AS UC', 'UC.id', 'vehicles.ciudad_id')
            ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->join('combustibles AS MO', 'MO.id', 'vehicles.combustible')
            ->join('transmisiones AS TA', 'TA.id', 'vehicles.transmision')
            ->where('vehicles.activo', 1);
        //Ubicacion Filter
        if( $filtros['ubicacion'] ){
            $result->where('UD.id', $filtros['ubicacion']);
        }
        //Cateoria Filter
        if ($filtros['categoria']) {
            $result->where('vehicles.tipo_vehiculo', $this->parse_slug_id($filtros['categoria']));
        } else {
            $result->where('vehicles.tipo_vehiculo', 1);
        }
        if ($filtros['marca']) {
            $result->where('MA.id', $filtros['marca']);
        }
        if ($filtros['modelo']) {
            $result->where('vehicles.modelo_id', $filtros['modelo']);
        }
        if ($filtros['transmision']) {
            $result->where('vehicles.transmision', $filtros['transmision']);
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
            $estado = ($estado == 2) ? 'Usado' : 'Nuevo';
            $result->where('vehicles.condicion', $filtros['estado']);
        }
        $result = $result->groupBy('vehicles.id')->offset(($page - 1) * 20)->limit(20)->get();
        
        $collection = collect($result);
        $filteredMarcas = $collection;

        $contadores = array(
            'marcas' => $filteredMarcas->countBy('marca'),
            'modelos' => $filteredMarcas->countBy('modelo'),
            'anios' => $filteredMarcas->countBy('ano'),
            'caja' => $filteredMarcas->countBy('transmision'),
            'combustible' => $filteredMarcas->countBy('combustible'),
            'ubicacion' => $filteredMarcas->countBy('labelCiudad')
        );

        $response = [
            'page' => $page,
            'vehicles' => $result,
            'filtros' => $filtros,
            'contadores' => $contadores
        ];

        return $response;
    }
    public function detalle($slug){

            $arrayUrl = explode('-', $slug);
            $id = $arrayUrl[COUNT($arrayUrl) - 1];

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
                'TP.nombre AS tipoPrecioLabel'
            )
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

            $response = [
                'status' => true,
                'vehiculo' => $vehiculo,
                'imagenes' => $imagenes,
                'vehiculosRelacionados' => $vehiculosRelacionados,
                'vehiculosRelacionadosCount' => $vehiculosRelacionadosCount,
                'diasPublicado' => $diasPublicado,
                
                'urlCategory' => $urlCategory,
                'urlTypeMoto' => $urlTypeMoto,
                'urlMarca' => $urlMarca,
                'urlModelo' => $urlModelo
            ];

            return $response;
    }
}
