<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use App\Models\Vehicles;
use App\Models\Busquedas;
use App\Models\Favoritos;
use App\Models\imagenes;
use App\Models\Imagenes_vehiculo;
use DateTime;

class RemoveVehicles
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fecha_actual = date("Y-m-d");
        $fechaCaducada = date("Y-m-d", strtotime($fecha_actual."- 60 days")); 
        $fechaCaducadaInactivo = date("Y-m-d", strtotime($fecha_actual."- 90 days")); 

        $vehiculos = Vehicles::select('id')
            ->where('fecha_creacion', '<', $fechaCaducada)
            ->get();

        $usuarios = Vehicles::select('vendedor_id')
            ->where('fecha_creacion', '<', $fechaCaducada)
            ->groupBy('vendedor_id')
            ->get();

        \DB::table('vehicles')->whereIn('id', $vehiculos)->update([
            'activo' => 3,
        ]);

        \DB::table('users')->whereIn('id', $usuarios)->update([
            'notification' => 1,
        ]);

        $vehiculosInactivos = Vehicles::select('id')
            ->where('fecha_creacion', '<', $fechaCaducadaInactivo)
            ->where('activo', 1)
            ->get();
        
        Busquedas::whereIn('vehiculo_id', $vehiculosInactivos)->delete();
        Favoritos::whereIn('vehiculo_id', $vehiculosInactivos)->delete();
        imagenes::whereIn('id_vehicle', $vehiculosInactivos)->delete();
        Imagenes_vehiculo::whereIn('id_vehicle', $vehiculosInactivos)->delete();
        Vehicles::whereIn('id', $vehiculosInactivos)->delete();

        $fechaRelanzamiento = date("Y-m-d", strtotime($fecha_actual."- 7 days")); 
        $vehicleRelanzamiento = Vehicles::select('id')
            ->where('fecha_publicacion', '<', $fechaRelanzamiento)
            ->where('activo', 1)
            ->get();

        \DB::table('vehicles')->whereIn('id', $vehicleRelanzamiento)->update([
            'fecha_publicacion' => new DateTime()
        ]);
    }
}
