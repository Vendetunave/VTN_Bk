<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

use App\Models\TipoVehiculos;
use App\Models\Vehicles;
use App\Models\imagenes;
use App\Models\Banners;
use App\Models\Marcas;
use App\Models\Noticias;
use App\Models\Config;
use App\Models\Users;
use App\Models\newsletter;

class HomeController extends Controller
{

    public function show()
    {
        $categories = TipoVehiculos::all();
        $marcas = Marcas::where('visibility', 1)->get();
        $vehiculosPromocion = Vehicles::select('vehicles.id', 'vehicles.url', 'vehicles.title', 'vehicles.precio', 'vehicles.ano', 'vehicles.kilometraje', 'I.nombre AS nameImage', 'I.extension', 'I.new_image', 'UC.nombre AS labelCiudad')
            ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
            ->join('imagenes AS I', 'I.id', 'IV.id_image')
            ->join('ubicacion_ciudades AS UC', 'UC.id', 'vehicles.ciudad_id')
            ->where('promocion', 1)
            ->where('aprobado_promocion', 1)
            ->where('activo', 1)
            ->orderBy('vehicles.fecha_creacion', 'DESC')
            ->groupBy('vehicles.id')
            ->limit(15)
            ->get();

        $banners = Banners::select('id', 'url')->where('type', 1)->orderBy('posicion')->get();
        $bannersMobile = Banners::select('id', 'url')->where('type', 2)->orderBy('posicion')->get();
        $noticias = Noticias::all();
        $config = Config::select('link_video')->first();

        $anios = Vehicles::select('vehicles.ano')
            ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
            ->join('marcas AS MA', 'MA.id', 'M.marca_id')
            ->where('vehicles.activo', 1)
            ->groupBy('vehicles.ano')
            ->orderBy('vehicles.ano', 'DESC')
            ->get();

        $marcasFil = Marcas::select('id', 'nombre', 'categoria_id')->where('categoria_id', 1)->get();

        $response = [
            'categories' => $categories,
            'vehiculos_promocion' => $vehiculosPromocion,
            'banners' => $banners,
            'bannersMobile' => $bannersMobile,
            'marcas' => $marcas,
            'marcasFil' => $marcasFil,
            'noticias' => $noticias,
            'config' => $config,
            'anios' => $anios,
        ];
        return $response;
    }
    public function config()
    {

        $configuraciones = Config::select('correo_contacto', 'telefono_contacto', 'tyc')->first();

        $response = [
            'configuraciones' => $configuraciones,
        ];

        return $response;
    }
    public function newsletter(Request $request)
    {

        $user = newsletter::where('email', $request->emailNewsletter)->first();
        if ($user) {
            $response = 'Ya te encuentras registrado a nuestro newsletter.';
        } else {
            $insertNewsletter = newsletter::insert(['nombre' => $request->nombreNewsletter, 'email' => $request->emailNewsletter]);
            $response = 'Te has registrado al newsletter con éxito.';
        }

        $result = [
            'state' => true,
            'message' => $response
        ];

        return $result;
    }

    public function get_all_banners()
    {
        $banners = Banners::all();

        $response = [
            'banners' => $banners,
        ];

        return $response;
    }

    public function get_by_banners(Request $request)
    {
        $banners = Banners::where('id', $request->id)->first();

        $response = [
            'banners' => $banners,
        ];

        return $response;
    }

    public function create_banners(Request $request)
    {
        try {
            if ($request->hasFile('image1')) {
                $name = uniqid();
                $filenametostore = $name . '.webp';

                $imageConvert = (string) Image::make($request->file('image1'))->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/banners/' . $name . '.' . 'webp', $imageConvert, 'public');
                $bannerCount = Banners::select('posicion')->where('type', $request->type)->orderBy('posicion', 'desc')->first();
                $count = $request->position <= $bannerCount->posicion ? ($bannerCount->posicion + 1) : $request->position;
                Banners::insert([
                    'nombre' => $request->title,
                    'url' => 'https://vendetunave.s3.amazonaws.com/vendetunave/images/banners/' . $filenametostore,
                    'posicion' => $count,
                    'type' => $request->type
                ]);

                $response = [
                    'status' => true,
                    'message' => 'Datos creados correctamente!'
                ];
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Imagen no valida!'
                ];
            }

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => strval($th)
            ];
            return $response;
        }
    }

    public function update_banners(Request $request)
    {
        try {
            $bannerCount = Banners::select('posicion')->where('id', '<>', $request->id)->where('type', $request->type)->orderBy('posicion', 'desc')->first();
            $count = 1;
            if ($bannerCount) {
                $count = $request->posicion <= $bannerCount->posicion ? ($bannerCount->posicion + 1) : $request->posicion;
            }

            Banners::where('id', $request->id)->update([
                'nombre' => $request->nombre,
                'posicion' => $count,
                'type' => $request->type
            ]);

            if ($request->hasFile('image1')) {
                $name = uniqid();
                $filenametostore = $name . '.webp';

                $imageConvert = (string) Image::make($request->file('image1'))->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/banners/' . $name . '.' . 'webp', $imageConvert, 'public');
                Banners::where('id', $request->id)->update([
                    'url' => 'https://vendetunave.s3.amazonaws.com/vendetunave/images/banners/' . $filenametostore,
                ]);
            }

            $response = [
                'status' => true,
                'message' => 'Datos creados correctamente!'
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

    public function delete_banners(Request $request)
    {
        Banners::where('id', $request->id)->delete();
        $banners = Banners::all();

        $response = [
            'status' => true,
            'message' => 'Datos actualizados correctamente!',
            'banners' => $banners,
        ];

        return $response;
    }
}
