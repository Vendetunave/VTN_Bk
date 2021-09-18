<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Vehicles;
use App\Models\DataSheet;
use App\Models\Modelos;
use App\Models\ImagesDataSheet;


class FichaTecnicaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function fichas_tecnicas(Request $request){
        $filtros = array(
            'tipo' => $request->query('tipo') ? $request->query('tipo') : null,
            'marca' => $request->query('marca') ? $request->query('marca') : null,
            'modelo' => $request->query('modelo') ? $request->query('modelo') : null,
            'combustible' => $request->query('combustible') ? $request->query('combustible') : null,
            'transmision' => $request->query('transmision') ? $request->query('transmision') : null,
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
            'data_sheet.torque',
            'data_sheet.fuel_type',
            'data_sheet.traction',
            'data_sheet.trunk',
            'data_sheet.autonomy',
            'data_sheet.engine',
            'data_sheet.power',
            'data_sheet.performance',
            'data_sheet.security',
            'data_sheet.airbags',
            'data_sheet.wheels',
            'data_sheet.cushions',
            'data_sheet.weight',
            'C.nombre AS combustibleLabel',
            'T.nombre AS transmisionLabel',
            'I.name AS nameImage',
            'I.ext AS extension',
            'TP.nombre AS tipo',
            'MA.nombre AS marca',
            'M.nombre AS modelo',
            \DB::raw('2 AS new_image')
        )
            ->join('images_data_sheet AS I', 'I.datasheet_id', \DB::raw('data_sheet.id AND I.order = 1'))
            ->join('combustibles AS C', 'C.id', 'data_sheet.fuel_id')
            ->join('transmisiones AS T', 'T.id', 'data_sheet.transmission_id')
            ->join('tipo_vehiculos AS TP', 'TP.id', 'data_sheet.vehicle_type_id')
            ->join('modelos AS M', 'M.id', 'data_sheet.model_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->where('data_sheet.active', 1);

        
        $total_all = $result->groupBy('data_sheet.id')->get();

        if($filtros['q']){
            $result->where('data_sheet.title', 'LIKE', '%'.$filtros['q'].'%');
        }
        if($filtros['tipo']){
            $result->where('data_sheet.type', $filtros['tipo']);
        }

        if($filtros['marca']){
            $total_modelos = Modelos::select('modelos.id', 'modelos.nombre')
                ->join('marcas AS MA', 'MA.id', 'marca_id')
                ->where('MA.nombre', $filtros['marca'])
                ->get();
            $result->where('MA.nombre', $filtros['marca']);
        }

        if($filtros['modelo']){
            $result->where('M.nombre', $filtros['modelo']);
        }

        if($filtros['transmision']){
            $result->where('T.nombre', $filtros['transmision']);
        }

        if($filtros['combustible']){
            $result->where('C.nombre', $filtros['combustible']);
        }
        

        if ($filtros['precio']) {
            $decodeParam = $filtros['precio'];
            $arrayPrices = explode(":", $decodeParam);
            $result->whereBetween('price', $arrayPrices);
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
        $result = $result->groupBy('data_sheet.id')->offset(($filtros['page'] - 1) * 20)->limit(20)->get();

        //Filtros complete
        $collection = collect($total_all);
        $filteredMarcas = $collection;

        if(isset($total_modelos)){
            $collectionModelos = collect($total_modelos);
            $filteredModelos = $collectionModelos;
        }

        $contadores = array(
            'tipo' => $filteredMarcas->countBy('tipo'),
            'transmision' => $filteredMarcas->countBy('transmisionLabel'),
            'marca' => $filteredMarcas->countBy('marca'),
            'modelo' => (isset($filteredModelos)) ? $filteredModelos->countBy('nombre') : [],
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
}
