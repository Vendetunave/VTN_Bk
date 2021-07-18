<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Vehicles;

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
}
