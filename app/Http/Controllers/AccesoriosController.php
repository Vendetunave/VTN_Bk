<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

use App\Models\imagenes;
use App\Models\imagenes_accesorios;
use App\Models\tipo_accesorio;
use App\Models\TipoPrecio;
use App\Models\ubicacion_departamentos;
use App\Models\ubicacion_ciudades;

use App\Models\Accesorios;
use DateTime;

class AccesoriosController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function accesorios(Request $request){
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
            'orden' => $request->query('orden') ? $request->query('orden') : null,
            'page' => $request->query('page') ? $request->query('page') : 1
        );
        $result = Accesorios::select('accesorios.*', 'TP.nombre AS tipoAcc', 'I.nombre AS nameImage', 'I.extension', 'UC.nombre as ciudad')
            ->join('tipo_accesorio AS TP', 'TP.id', 'accesorios.tipo_accesorio')
            ->join('ubicacion_ciudades AS UC', 'UC.id', 'accesorios.ciudad_id')
            ->join('ubicacion_departamentos AS UD', 'UD.id', 'UC.id_departamento')
            ->join('imagenes_accesorios AS IA', 'IA.accesorio_id', 'accesorios.id')
            ->join('imagenes AS I', 'I.id', 'IA.image_id')
            ->where('activo', 1);
        
        switch ($filtros['orden']) {
            case 1:
                $result->orderBy('accesorios.condicion', 'ASC');
                break;
            case 2:
                $result->orderBy('accesorios.condicion', 'DESC');
                break;
            case 3:
                $result->orderBy('accesorios.precio', 'ASC');
                break;
            case 4:
                $result->orderBy('accesorios.precio', 'DESC');
                break;
            default:
                $result->orderBy('accesorios.id', 'DESC');
        }
        $total_records = count($result->groupBy('accesorios.id')->get());
        $total_all = $result->groupBy('accesorios.id')->get();
        $result = $result->groupBy('accesorios.id')->offset(($filtros['page'] - 1) * 20)->limit(20)->get();

        //Filtros complete
        $collection = collect($total_all);
        $filteredMarcas = $collection;

        $contadores = array(
            'tipo' => $filteredMarcas->countBy('tipoAcc'),
            'ciudad' => $filteredMarcas->countBy('ciudad'),
            'estado' => $filteredMarcas->countBy('condicion')
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

    public function accesorio($slug){
        $arrayUrl = explode('-', $slug);
        $id = $arrayUrl[COUNT($arrayUrl) - 1];
        try {
            $accesorio = Accesorios::select(
                'accesorios.*',
                'TA.nombre AS tipoLabel',
                'UC.nombre AS ciudadLabel',
                'UD.nombre AS departamentoLabel',
                'TP.nombre AS tipoPrecioLabel',
                'U.telefono'
            )
                ->leftJoin('users AS U', 'U.id', 'accesorios.vendedor_id')
                ->leftJoin('tipo_accesorio AS TA', 'TA.id', 'accesorios.tipo_accesorio')
                ->leftJoin('ubicacion_ciudades AS UC', 'UC.id', 'accesorios.ciudad_id')
                ->leftJoin('ubicacion_departamentos AS UD', 'UD.id', 'UC.id_departamento')
                ->leftJoin('tipo_precio AS TP', 'TP.id', 'accesorios.tipo_precio')
                ->where('accesorios.id', $id)
                ->first();
            $date1 = new DateTime($accesorio->fecha_creacion);
            $date2 = new DateTime();
            $diff = $date1->diff($date2);
            $diasPublicado = $diff->days;

            $imagenes = imagenes::select(
                \DB::raw('CONCAT("https://vendetunave.s3.amazonaws.com/", imagenes.path, imagenes.nombre, ".") AS url, imagenes.id AS imageId'),
                    'imagenes.extension',
                    'imagenes.new_image'
                )
                ->leftJoin('imagenes_accesorios AS IV', 'IV.image_id', 'imagenes.id')
                ->where('IV.accesorio_id', $id)
                ->get();

            $response = [
                'status' => true,
                'vehiculo' => $accesorio,
                'id' => $id,
                'imagenes' => $imagenes,
                'diasPublicado' => $diasPublicado,
                'vehiculosRelacionados' => [],
                'vehicleFav' => [],
                'vehicleFavRelacionados' => [],
            ];
            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
            ];
            return $response;
        }
    }

    public function insert_accessory(Request $request)
    {
        try {
            $precio = str_replace('.', '', $request->precio);

            $accesorios = Accesorios::insertGetId([
                'title' => $request->titulo,
                'descripcion' => $request->descripcion,
                'precio' => (int) $precio,
                'tipo_precio' => $request->tipo_precio,
                'ciudad_id' => $request->ciudad,
                'condicion' => $request->estado,
                'vendedor_id' => $request->user_id,
                'activo' => 0,
                'tipo_accesorio' => $request->categoria,
                'fecha_creacion' => new DateTime(),
            ]);


            $images = $request->images;
            foreach ($images as $keyImage => $itemImage) {
                $image = $itemImage;
                $image = str_replace('data:image/jpeg;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $name = uniqid();
                $imageName = $name . '.' . 'jpeg';

                Storage::disk('s3')->put('vendetunave/images/accesorios/' . $imageName, base64_decode($image), 'public');
                $imageConvert = (string) Image::make(base64_decode($image))->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/accesorios/' . $name . '.' . 'webp', $imageConvert, 'public');

                $imagenId = imagenes::insertGetId([
                    'nombre' => $name,
                    'path' => 'vendetunave/images/accesorios/',
                    'extension' => 'jpeg',
                    'order' => ($keyImage + 1),
                    'new_image' => 1
                ]);

                $imageAcce = imagenes_accesorios::insert([
                    'accesorio_id' => $accesorios,
                    'image_id' => $imagenId
                ]);
            }

            $response = [
                'accessory' => $accesorios,
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

    public function edit_accessory(Request $request)
    {
        try {
            $accesorio = Accesorios::select('id', 'vendedor_id')->where('id', $request->id)->first();

            if($accesorio->vendedor_id !== $request->user_id){
                $response = [
                    'status' => true,
                    'intruder' => true,
                    'msj' => 'No deberías estas aquí :)'
                ];
    
                return $response;
            }

            $accesorio = Accesorios::select('accesorios.*', 'UD.id AS departamento')
                ->join('ubicacion_ciudades AS UC', 'UC.id', 'accesorios.ciudad_id')
                ->join('ubicacion_departamentos AS UD', 'UD.id', 'UC.id_departamento')
                ->where('accesorios.id', $accesorio->id)
                ->first();

            $tipoAccesorio = tipo_accesorio::all();
            $tipoPrecio = TipoPrecio::all();
            $departamentos = ubicacion_departamentos::select('*')->orderBy('nombre')->get();
            $ciudades = ubicacion_ciudades::select('*')->where('id_departamento', $accesorio->departamento)->orderBy('nombre')->get();
            $imagenes = imagenes::select(\DB::raw('CONCAT("https://vendetunave.s3.amazonaws.com/", imagenes.path, imagenes.nombre, ".", imagenes.extension) AS url, imagenes.id AS imageId'))
                ->join('imagenes_accesorios AS IV', 'IV.image_id', 'imagenes.id')
                ->where('IV.accesorio_id', $accesorio->id)
                ->get();

            $response = [
                'status' => true,
                'intruder' => false,
                'departamentos' => $departamentos,
                'ciudades' => $ciudades,
                'tipoAccesorio' => $tipoAccesorio,
                'tipoPrecio' => $tipoPrecio,
                'accesorio' => $accesorio,
                'imagenes' => $imagenes,
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
            ];

            return $response;
        }
    }

    public function update_accessory(Request $request)
    {
        try {
            $accesorio = Accesorios::select('vendedor_id')->where('id', $request->id)->first();

            if($accesorio->vendedor_id !== $request->user_id) {
                $response = [
                    'status' => true,
                    'intruder' => true,
                    'msj' => 'No deberías estas aquí :)'
                ];
    
                return $response;
            }

            $precioAcc = str_replace('.', '', $request->precio_acc);

            $accesorios = \DB::table('accesorios')->where('id', $request->id)->update([
                'title' => $request->title_acc,
                'descripcion' => $request->desc_acc,
                'precio' => (int) $precioAcc,
                'tipo_precio' => $request->tipoPrecioAcc,
                'ciudad_id' => $request->ciudad_acc,
                'condicion' => $request->condicionAcc,
                'activo' => 0,
                'tipo_accesorio' => $request->categoriaAccesorio,
            ]);

            if(count($request->image) > 0) {
                foreach ($request->image as $item) {
                    $imagevehiculo = imagenes_accesorios::insert([
                        'accesorio_id' => $request->id,
                        'image_id' => $item
                    ]);
                }
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

}
