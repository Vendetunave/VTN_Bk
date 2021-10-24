<?php

namespace App\Http\Controllers;

use App\Models\Bodywork;
use App\Models\Combustibles;
use App\Models\Documents;
use App\Models\Marcas;
use App\Models\Modelos;
use App\Models\VehicleClass;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade;

class DocumentsController extends Controller
{
    public function informationDocuments()
    {
        $marcas = Marcas::all();
        $modelos = Modelos::all();
        $clase_vehiculo = VehicleClass::all();
        $carroceria = Bodywork::all();
        $combustibles = Combustibles::all();

        $response = [
            'marcas' => $marcas,
            'modelos' => $modelos,
            'combustibles' => $combustibles,
            'clase_vehiculo' => $clase_vehiculo,
            'carroceria' => $carroceria
        ];

        return $response;
    }

    public function salesPurchaseDocument(Request $request)
    {
        try {
            $userId = $request->user_id;
            $requiredInformation = [
                'nombre_vendedor' => $request->get('nombre_vendedor', null),
                'documento_vendedor' => $request->get('documento_vendedor', null),
                'direccion_vendedor' => $request->get('direccion_vendedor', null),
                'tel_vendedor' => $request->get('tel_vendedor', null),
                'clase_vehiculo' => $request->get('clase_vehiculo', null),
                'marca' => $request->get('marca', null),
                'modelo' => $request->get('modelo', null),
                'ano' => $request->get('ano', null),
                'tipo_carroceria' => $request->get('tipo_carroceria', null),
                'color' => $request->get('color', null),
                'numero_motor' => $request->get('numero_motor', null),
                'numero_chasis' => $request->get('numero_chasis', null),
                'placa' => $request->get('placa', null),
                'precio' => $request->get('precio', null),
            ];
    
            $errors = [];
            foreach ($requiredInformation as $key => $item) {
                if (!$item) array_push($errors, $key . ' is required');
            }

            if (COUNT($errors) > 0 && COUNT($requiredInformation) !== COUNT($errors)) return [ 'status' => false, 'errors' => $errors ];
    
            $unrequiredInformation = [
                'nombre_comprador' => $request->get('nombre_comprador', null),
                'documento_comprador' => $request->get('documento_comprador', null),
                'direccion_comprador' => $request->get('direccion_comprador', null),
                'tel_comprador' => $request->get('tel_comprador', null),
                'numero_serie' => $request->get('numero_serie', null),
                'numero_puertas' => $request->get('numero_puertas', null),
                'capacidad' => $request->get('capacidad', null),
                'servicio' => $request->get('servicio', null),
                'clausulas' => $request->get('clausulas', null),
            ];

            $information = array_merge($requiredInformation, $unrequiredInformation);

            Documents::insert([
                'user_id' => $userId,
                'information' => json_encode($information),
                'type' => 'compra-venta',
                'created_at' => new \DateTime()
            ]);

            $response = [
                'information' => $information,
            ];
    
            $pdf = Facade::loadView('salesPurchaseDocument', $response);
            return $pdf->download('archivo.pdf');
        } catch (\Throwable $th) {
            return [ 'status' => false, 'message' => $th ];
        }
    }

    public function mandateDocument(Request $request)
    {
        try {
            $userId = $request->user_id;
            $requiredInformation = [
                'ciudad' => $request->get('ciudad', null),
                'fecha' => $request->get('fecha', null),
                'nombre_mandate' => $request->get('nombre_mandate', null),
                'documento_mandate' => $request->get('documento_mandate', null),
                'tramite' => $request->get('tramite', null),
                'placa' => $request->get('placa', null),
                'marca' => $request->get('marca', null),
                'modelo' => $request->get('modelo', null),
                'ano' => $request->get('ano', null),
                'cilindraje' => $request->get('cilindraje', null),
                'motor' => $request->get('motor', null),
                'chasis' => $request->get('chasis', null),
            ];
    
            $errors = [];
            foreach ($requiredInformation as $key => $item) {
                if (!$item) array_push($errors, $key . ' is required');
            }

            if (COUNT($errors) > 0 && COUNT($requiredInformation) !== COUNT($errors)) return [ 'status' => false, 'errors' => $errors ];
    
            $unrequiredInformation = [
                'nombre_mandatario' => $request->get('nombre_mandatario', null),
                'documento_mandatario' => $request->get('documento_mandatario', null),
            ];

            $information = array_merge($requiredInformation, $unrequiredInformation);

            Documents::insert([
                'user_id' => $userId,
                'information' => json_encode($information),
                'type' => 'mandato',
                'created_at' => new \DateTime()
            ]);

            $response = [
                'information' => $information,
            ];
    
            $pdf = Facade::loadView('mandateDocument', $response);
            return $pdf->download('archivo.pdf');
        } catch (\Throwable $th) {
            return [ 'status' => false, 'error' => $th ];
        }
    }
}
