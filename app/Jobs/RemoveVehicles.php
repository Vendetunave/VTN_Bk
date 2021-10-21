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
        $fechaCaducada = date("Y-m-d", strtotime($fecha_actual."- 90 days")); 

        $vehiculos = Vehicles::select('id')
            ->where('fecha_publicacion', '<', $fechaCaducada)
            ->get();
        
        Busquedas::whereIn('vehiculo_id', $vehiculos)->delete();
        Favoritos::whereIn('vehiculo_id', $vehiculos)->delete();
        imagenes::whereIn('id_vehicle', $vehiculos)->delete();
        Imagenes_vehiculo::whereIn('id_vehicle', $vehiculos)->delete();
        Vehicles::whereIn('id', $vehiculos)->delete();
    }
}
