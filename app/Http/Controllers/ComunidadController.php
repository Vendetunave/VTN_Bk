<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pregunta;

class ComunidadController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function show()
    {
        $page = 1;
        $preguntas = Pregunta::select( 'pregunta.*', \DB::raw('COUNT(R.id) AS repuestas'), \DB::raw('MAX(R.fecha) AS ult_respuesta') )
            ->leftJoin('respuestas AS R', 'R.pregunta_id', 'pregunta.id')
            ->where('pregunta.aprobado', 1);
        $preguntas = $preguntas->groupBy('pregunta.id')
            ->orderBy('pregunta.fecha', 'DESC')
            ->offset(($page - 1) * 10)->limit(10)->get();
        $result = [
            'preguntas' => $preguntas,
        ];
        return $result;
        
    }
}
