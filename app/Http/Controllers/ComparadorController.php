<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Vehicles;
use Barryvdh\DomPDF\Facade;

class ComparadorController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }
    public function generate_pdf(Request $request)
    {
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
            'I.nombre AS nameImage',
            'I.extension',
            'I.new_image'
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
            ->join('tipo_precio AS TP', 'TP.id', 'vehicles.tipo_precio');

        $arrayTest = array(4590, 5192);
        
        foreach ($arrayTest as $value) {
            $vehiculo->orWhere('vehicles.id', $value);
        }
        $compare = $vehiculo->groupBy('vehicles.id')->get();
        $response = [
            'vehiculo' => $compare,
        ];
        $pdf = Facade::loadView('comparePDF', $response);
        return $pdf->download('archivo.pdf');
    }

}
