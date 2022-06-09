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
            ->where('fecha_publicacion', '<', $fechaCaducada)
            ->get();

        $usuarios = Vehicles::select('vendedor_id')
            ->where('fecha_publicacion', '<', $fechaCaducada)
            ->groupBy('vendedor_id')
            ->get();

        \DB::table('vehicles')->whereIn('id', $vehiculos)->update([
            'activo' => 3,
        ]);

        \DB::table('users')->whereIn('id', $usuarios)->update([
            'notification' => 1,
        ]);

        $vehiculosIcativos = Vehicles::select('id')
            ->where('fecha_publicacion', '<', $fechaCaducadaInactivo)
            ->where('activo', 1)
            ->get();
        
        Busquedas::whereIn('vehiculo_id', $vehiculosIcativos)->delete();
        Favoritos::whereIn('vehiculo_id', $vehiculosIcativos)->delete();
        imagenes::whereIn('id_vehicle', $vehiculosIcativos)->delete();
        Imagenes_vehiculo::whereIn('id_vehicle', $vehiculosIcativos)->delete();
        Vehicles::whereIn('id', $vehiculosIcativos)->delete();
    }
}
