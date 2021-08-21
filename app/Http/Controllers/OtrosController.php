<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Servicios;
use App\Models\Dealerships;
use App\Models\TiposServicios;
use App\Models\ubicacion_ciudades;


class OtrosController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function getServicios(Request $request)
    {
        $page = $request->query('page') ? $request->query('page') : 1;
        
        $servicio = $request->query('servicio') ? $request->query('servicio') : null;
        $ciudad = $request->query('ciudad') ? $request->query('ciudad') : null;

        $servicios = Servicios::select('servicios.*', 'TS.nombre AS servicio', 'UC.nombre AS labelCiudad')
        ->leftJoin('tipos_servicio AS TS', 'TS.id', 'servicios.tipo_servicio_id')
        ->join('ubicacion_ciudades AS UC', 'UC.id', 'servicios.ciudad_id');
        $total_all = $servicios->get();
        if($servicio){
            $servicios->where('TS.nombre', $servicio);
        }

        if($ciudad){
            $servicios->where('UC.nombre', $ciudad);
        }

        $total_records = count($servicios->get());
        $servicios = $servicios->offset(($page - 1) * 10)->limit(10)->get();

        $collection = collect($total_all);
        $filteredMarcas = $collection;

        $contadores = array(
            'servicios' => $filteredMarcas->countBy('servicio'),
            'ciudades' => $filteredMarcas->countBy('labelCiudad')
        );

        $result = [
            'pagina' => $page,
            'servicios' => $servicios,
            'contadores' => $contadores,
            'total_records' => $total_records
        ];

        return $result;
    }
    public function concesionarios()
    {
        $page = 1;
        $servicios = Dealerships::select('dealerships.*', 'CD.nombre AS ciudadLabel', 'TV.nombre AS tipoLabel')
            ->join('ubicacion_ciudades AS CD', 'CD.id', 'dealerships.city_id')
            ->join('tipo_vehiculos AS TV', 'TV.id', 'dealerships.type_vehicle')
            ->join('dealerships_brands AS BR', 'BR.dealership_id', 'dealerships.id');
        $servicios = $servicios->offset(($page - 1) * 10)->limit(10)->get();

        $collection = collect($servicios);
        $filteredMarcas = $collection;

        $contadores = array(
            'tipo' => $filteredMarcas->countBy('tipoLabel'),
            'ciudades' => $filteredMarcas->countBy('ciudadLabel'),
            'marcas' => $filteredMarcas->countBy(''),
        );
        $result = [
            'servicios' => $servicios,
            'contadores' => $contadores,
        ];

        return $result;
    }
}
