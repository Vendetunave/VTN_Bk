<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

use App\Models\Vehicles;
use App\Models\DataSheet;
use App\Models\Modelos;
use App\Models\ImagesDataSheet;
use App\Models\TipoVehiculos;
use App\Models\Combustibles;
use App\Models\Marcas;
use App\Models\Transmisiones;

class FichaTecnicaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function fichas_tecnicas(Request $request)
    {
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

        if ($filtros['q']) {
            $result->where('data_sheet.title', 'LIKE', '%' . $filtros['q'] . '%');
        }
        if ($filtros['tipo']) {
            $result->where('data_sheet.type', $filtros['tipo']);
        }

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
            $result->where('T.nombre', $filtros['transmision']);
        }

        if ($filtros['combustible']) {
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

        if (isset($total_modelos)) {
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

    public function ficha_tecnica($slug)
    {
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

    public function get_all_technical_sheets()
    {
        $technicalSheets = DataSheet::select(
            'data_sheet.id',
            'data_sheet.title',
            'data_sheet.active',
            'I.name AS nameImage',
            'M.nombre AS modeloLabel',
            'MA.nombre AS marcaLabel'
        )
            ->join('images_data_sheet AS I', 'I.datasheet_id', \DB::raw('data_sheet.id AND I.order = 1'))
            ->join('modelos AS M', 'M.id', 'data_sheet.model_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->orderBy('id', 'DESC')
            ->get();

        $response = [
            'technical_sheets' => $technicalSheets,
        ];

        return $response;
    }

    public function get_by_technical_sheets(Request $request)
    {
        $technical_sheets = DataSheet::select(
            'data_sheet.*',
            'MA.id AS mark_id',
        )
            ->join('modelos AS M', 'M.id', 'data_sheet.model_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->where('data_sheet.id', $request->id)
            ->first();

        $imagenes = ImagesDataSheet::select(
            \DB::raw('CONCAT("https://vendetunave.s3.amazonaws.com/", path, name, ".webp") AS url, id AS imageId'),
            'order'
        )
            ->where('datasheet_id', $request->id)
            ->orderBy('order', 'ASC')
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

        $categories = TipoVehiculos::where('id', 1)->get();
        $combustibles = Combustibles::all();
        $transmisiones = Transmisiones::all();
        $marcas = Marcas::where('categoria_id', 1)->get();
        $modelos = Modelos::select('modelos.*')->join('marcas as M', 'M.id', 'modelos.marca_id')->where('M.categoria_id', 1)->get();

        $response = [
            'technical_sheets' => $technical_sheets,
            'imagenes' => $imagesArray,
            'categories' => $categories,
            'combustibles' => $combustibles,
            'transmisiones' => $transmisiones,
            'marcas' => $marcas,
            'modelos' => $modelos,
        ];
        return $response;
    }

    public function form_technical_sheets()
    {

        $categories = TipoVehiculos::where('id', 1)->get();
        $combustibles = Combustibles::all();
        $transmisiones = Transmisiones::all();
        $marcas = Marcas::where('categoria_id', 1)->get();
        $modelos = Modelos::all();

        $result = [
            'categories' => $categories,
            'combustibles' => $combustibles,
            'transmisiones' => $transmisiones,
            'marcas' => $marcas,
            'modelos' => $modelos,
        ];
        return $result;
    }

    public function create_technical_sheets(Request $request)
    {

        try {
            $security = ($request->security > 5) ? 5 : $request->security;
            $security = ($security < 0) ? 0 : $security;

            $dataSheet = \DB::table('data_sheet')->insertGetId([
                'title' => $request->title,
                'description' => $request->description,
                'model_id' => $request->model_id,
                'vehicle_type_id' => $request->vehicle_type_id,
                'fuel_id' => $request->fuel_id,
                'transmission_id' => $request->transmission_id,
                'price' => $request->price,
                'year' => $request->year,
                'engine' => $request->engine,
                'power' => $request->power,
                'torque' => $request->torque,
                'traction' => $request->traction,
                'fuel_type' => $request->input('fuel_type', ""),
                'autonomy' => $request->autonomy,
                'performance' => $request->performance,
                'security' => $security,
                'airbags' => $request->airbags,
                'wheels' => $request->wheels,
                'trunk' => $request->trunk,
                'weight' => $request->weight,
                'cushions' => $request->cushions,
                'type' => $request->type
            ]);

            if ($request->hasFile('image1')) {
                $file = $request->file('image1');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $dataSheet,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 1
                ]);
            }

            if ($request->hasFile('image2')) {
                $file = $request->file('image2');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $dataSheet,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 2
                ]);
            }

            if ($request->hasFile('image3')) {
                $file = $request->file('image3');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $dataSheet,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 3
                ]);
            }

            if ($request->hasFile('image4')) {
                $file = $request->file('image4');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $dataSheet,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 4
                ]);
            }

            if ($request->hasFile('image5')) {
                $file = $request->file('image5');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $dataSheet,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 5
                ]);
            }

            if ($request->hasFile('image6')) {
                $file = $request->file('image6');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $dataSheet,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 6
                ]);
            }

            if ($request->hasFile('image7')) {
                $file = $request->file('image7');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $dataSheet,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 7
                ]);
            }

            if ($request->hasFile('image8')) {
                $file = $request->file('image8');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $dataSheet,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 8
                ]);
            }

            if ($request->hasFile('image9')) {
                $file = $request->file('image9');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $dataSheet,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 9
                ]);
            }

            if ($request->hasFile('image10')) {
                $file = $request->file('image10');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $dataSheet,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 10
                ]);
            }

            $response = [
                'status' => true,
                'message' => 'Datos creados correctamente'
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

    public function update_technical_sheets(Request $request)
    {
        try {
            $imagenes = ImagesDataSheet::select(
                \DB::raw('CONCAT("https://vendetunave.s3.amazonaws.com/", path, name, ".webp") AS url, id AS imageId'),
                'order'
            )
                ->where('datasheet_id', $request->id)
                ->orderBy('order', 'ASC')
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

            $security = ($request->security > 5) ? 5 : $request->security;
            $security = ($security < 0) ? 0 : $security;

            \DB::table('data_sheet')->where('id', $request->id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'model_id' => $request->model_id,
                'vehicle_type_id' => $request->vehicle_type_id,
                'fuel_id' => $request->fuel_id,
                'transmission_id' => $request->transmission_id,
                'price' => $request->price,
                'year' => $request->year,
                'engine' => $request->engine,
                'power' => $request->power,
                'torque' => $request->torque,
                'traction' => $request->traction,
                'fuel_type' => $request->input('fuel_type', ""),
                'autonomy' => $request->autonomy,
                'performance' => $request->performance,
                'security' => $security,
                'airbags' => $request->airbags,
                'wheels' => $request->wheels,
                'trunk' => $request->trunk,
                'weight' => $request->weight,
                'cushions' => $request->cushions,
                'type' => $request->type
            ]);

            if ($request->hasFile('image1')) {
                $file = $request->file('image1');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $request->id,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 1
                ]);

                if ($imagesArray[0]->imageId !== '') ImagesDataSheet::where('id', $imagesArray[0]->imageId)->delete();
            }

            if ($request->hasFile('image2')) {
                $file = $request->file('image2');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $request->id,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 2
                ]);

                if ($imagesArray[1]->imageId !== '') ImagesDataSheet::where('id', $imagesArray[1]->imageId)->delete();
            }

            if ($request->hasFile('image3')) {
                $file = $request->file('image3');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $request->id,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 3
                ]);

                if ($imagesArray[2]->imageId !== '') ImagesDataSheet::where('id', $imagesArray[2]->imageId)->delete();
            }

            if ($request->hasFile('image4')) {
                $file = $request->file('image4');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $request->id,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 4
                ]);

                if ($imagesArray[3]->imageId !== '') ImagesDataSheet::where('id', $imagesArray[3]->imageId)->delete();
            }

            if ($request->hasFile('image5')) {
                $file = $request->file('image5');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $request->id,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 5
                ]);

                if ($imagesArray[4]->imageId !== '') ImagesDataSheet::where('id', $imagesArray[4]->imageId)->delete();
            }

            if ($request->hasFile('image6')) {
                $file = $request->file('image6');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $request->id,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 6
                ]);

                if ($imagesArray[5]->imageId !== '') ImagesDataSheet::where('id', $imagesArray[5]->imageId)->delete();
            }

            if ($request->hasFile('image7')) {
                $file = $request->file('image7');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $request->id,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 7
                ]);

                if ($imagesArray[6]->imageId !== '') ImagesDataSheet::where('id', $imagesArray[6]->imageId)->delete();
            }

            if ($request->hasFile('image8')) {
                $file = $request->file('image8');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $request->id,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 8
                ]);

                if ($imagesArray[7]->imageId !== '') ImagesDataSheet::where('id', $imagesArray[7]->imageId)->delete();
            }
            if ($request->hasFile('image9')) {
                $file = $request->file('image9');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $request->id,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 9
                ]);

                if ($imagesArray[8]->imageId !== '') ImagesDataSheet::where('id', $imagesArray[8]->imageId)->delete();
            }

            if ($request->hasFile('image10')) {
                $file = $request->file('image10');
                $extension = 'webp';
                $name = uniqid();

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/ficha-tecnica/' . $name . '.webp', $imageConvert, 'public');
                ImagesDataSheet::insert([
                    'datasheet_id' => $request->id,
                    'path' => 'vendetunave/images/ficha-tecnica/',
                    'name' => $name,
                    'ext' => $extension,
                    'order' => 10
                ]);

                if ($imagesArray[9]->imageId !== '') ImagesDataSheet::where('id', $imagesArray[9]->imageId)->delete();
            }

            $response = [
                'status' => true,
                'message' => 'Datos creados correctamente'
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

    public function inactivate(Request $request)
    {
        $dataSheet = DataSheet::select('active')->where('id', $request->id)->first();
        \DB::table('data_sheet')->where('id', $request->id)->update([
            'active' => ($dataSheet->active) ? 0 : 1
        ]);

        $response = [
            'status' => true,
            'active' => ($dataSheet->active) ? false : true
        ];

        return $response;
    }

    public function delete(Request $request)
    {
        ImagesDataSheet::where('datasheet_id', $request->id)->delete();
        \DB::table('data_sheet')->where('id', $request->id)->delete();

        $technicalSheets = DataSheet::select(
            'data_sheet.id',
            'data_sheet.title',
            'data_sheet.active',
            'I.name AS nameImage',
            'M.nombre AS modeloLabel',
            'MA.nombre AS marcaLabel'
        )
            ->join('images_data_sheet AS I', 'I.datasheet_id', \DB::raw('data_sheet.id AND I.order = 1'))
            ->join('modelos AS M', 'M.id', 'data_sheet.model_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->orderBy('id', 'DESC')
            ->get();

        $response = [
            'status' => true,
            'message' => 'Se elimino la ficha técnica!',
            'technical_sheets' => $technicalSheets,
        ];

        return $response;
    }
}
