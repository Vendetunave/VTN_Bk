<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Servicios;
use App\Models\Dealerships;
use App\Models\TiposServicios;
use App\Models\ubicacion_ciudades;
use App\Models\Marcas;

use Illuminate\Support\Facades\Mail;
use App\Models\Config;
use App\Models\financiacion;


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
    public function get_id_tipo($titulo){
        switch ($titulo) {
            case 'NUEVO':
                return 1;
            case 'USADO':
                return 2;
        }
    }
    public function get_id_marca($titulo){
        $marca = Marcas::where('nombre', $titulo)->first();
        return $marca->id;
    }
    public function concesionarios(Request $request)
    {
        $filtros = array(
            'ciudad' => $request->query('ciudad') ? $request->query('ciudad') : null,
            'tipo' => $request->query('tipo') ? $request->query('tipo') : null,
            'marca' => $request->query('marca') ? $request->query('marca') : null,
            'page' => $request->query('page') ? $request->query('page') : 1,
        );
        $servicios = Dealerships::select('dealerships.*', 'CD.nombre AS ciudadLabel')
            ->join('ubicacion_ciudades AS CD', 'CD.id', 'dealerships.city_id');
        
        if ($filtros['ciudad']) {
            $servicios->where('CD.nombre', $filtros['ciudad']);
        }
        if ($filtros['tipo']) {
            $servicios->where('dealerships.type_vehicle', $this->get_id_tipo($filtros['tipo']));
        }
        if ($filtros['marca']) {
            $servicios->join('dealerships_brands AS DB', 'DB.dealership_id', 'dealerships.id')
                ->where('DB.brand_id', $this->get_id_marca($filtros['marca']))
                ->groupBy('dealerships.id');
        }
        $servicios = $servicios->offset(($filtros['page'] - 1) * 10)->limit(10)->get();
        $tiposServicios = Marcas::where('categoria_id', 1)->orderBy('nombre')->get();
        $ciudades = ubicacion_ciudades::orderBy('nombre')->where('indicativo', 1)->get();

        $result = [
            'servicios' => $servicios,
            'tiposServicios' => $tiposServicios,
            'ciudades' => $ciudades,
            'filtros' => $filtros
        ];

        return $result;
    }
    public function financiacion(Request $request){
        try {
            $finacicacion = financiacion::insert([ 
                'nombre' => $request->nombre, 
                'apellido' => $request->apellido, 
                'fecha_nacimiento' => $request->fecha_nacimiento, 
                'cuanto_cuesta' => str_replace('.', '', $request->cuanto_cuesta), 
                'cuota_inicial' => str_replace('.', '', $request->cuota_inicial), 
                'numero_cuotas' => $request->cuotas, 
                'datacredito' => $request->datacredito, 
                'rango_salarial' => $request->salario, 
                'whatsapp' => $request->whatsapp, 
                'email' => $request->email, 
                'creado' => date('Y-m-d H:i:s')
            ]);
    
            $config = Config::select('correo_financiacion')->first();
    
            $subject = "Alguien está interesado en el servicio de financiación";
            $for = $config->correo_financiacion;
    
            Mail::send('mailFinanciacion', $request->all(), function ($msj) use ($subject, $for) {
                $msj->from("no-reply@vendetunave.co", "VendeTuNave");
                $msj->subject($subject);
                $msj->to($for);
            });
    
            $result = [
                'status' => true
            ];
    
            return $result;
        } catch (\Throwable $th) {
            $result = [
                'status' => false
            ];

            return $result;
        }
        
    }
}
