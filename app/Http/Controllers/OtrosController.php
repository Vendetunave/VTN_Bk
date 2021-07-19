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
        $page = 1;
        $servicios = Servicios::select('servicios.*', 'TS.nombre AS servicio')
        ->leftJoin('tipos_servicio AS TS', 'TS.id', 'servicios.tipo_servicio_id');
        $servicios = $servicios->offset(($page - 1) * 10)->limit(10)->get();

        $result = [
            'servicios' => $servicios
        ];

        return $result;
    }
    public function concesionarios()
    {
        $page = 1;
        $servicios = Dealerships::select('dealerships.*');
        $servicios = $servicios->offset(($page - 1) * 10)->limit(10)->get();

        $result = [
            'servicios' => $servicios
        ];

        return $result;
    }
}
