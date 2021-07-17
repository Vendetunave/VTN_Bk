<?php

namespace App\Http\Controllers;

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
        $vehiculosPromocion = Vehicles::select(
            'vehicles.id',
            'vehicles.url',
            'vehicles.title',
            'vehicles.precio',
            'vehicles.ano',
            'vehicles.kilometraje',
            'I.nombre AS nameImage',
            'I.extension',
            'I.new_image',
            'UC.nombre AS labelCiudad'
        )
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
    public function config(){
        
        $configuraciones = Config::select('correo_contacto', 'telefono_contacto', 'tyc')->first();

        $response = [
            'configuraciones' => $configuraciones,
        ];

        return $response;
    }
}