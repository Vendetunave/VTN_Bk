<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use App\Models\Vehicles;
use DateTime

class InactivePremium
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

        $vehicle = Vehicles::select('id')
            ->where('active_premium', '<', $fechaCaducada)
            ->get();
        
        Vehicles::whereIn('id', $vehicle)->update([
            'premium' => 0,
            'active_premium' => "0001-01-01 01:01:01",
            'order_premium' => "0001-01-01 01:01:01"
        ]);

        $fechaRelanzamiento = date("Y-m-d", strtotime($fecha_actual."- 7 days")); 

        $vehicle = Vehicles::select('id')
            ->where('order_premium', '<', $fechaRelanzamiento)
            ->where('premium', 1)
            ->get();
        
        Vehicles::whereIn('id', $vehicle)->update([
            'order_premium' => new DateTime()
        ]);
    }
}
