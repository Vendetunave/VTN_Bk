<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use App\Models\Users;

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

        $user = Users::select('id')
            ->where('active_premium', '<', $fechaCaducada)
            ->get();
        
        Users::whereIn('id', $user)->update(['premium' => 0, 'active_premium' => null]);
    }
}
