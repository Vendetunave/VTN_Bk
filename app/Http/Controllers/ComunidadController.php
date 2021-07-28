<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pregunta;
use App\Models\Tags;

use Illuminate\Support\Facades\Auth;

class ComunidadController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function show()
    {
        $page = 1;
        $preguntas = Pregunta::select( 'pregunta.*', \DB::raw('COUNT(R.id) AS repuestas'), \DB::raw('MAX(R.fecha) AS ult_respuesta') )
            ->leftJoin('respuestas AS R', 'R.pregunta_id', 'pregunta.id')
            ->where('pregunta.aprobado', 1);
        $preguntas = $preguntas->groupBy('pregunta.id')
            ->orderBy('pregunta.fecha', 'DESC')
            ->offset(($page - 1) * 10)->limit(10)->get();
        $tags = Tags::select( 'tags.id', 'tags.tag', 'PT.pregunta_id' )
            ->leftJoin('preguntas_tags  AS PT', 'PT.tag_id', 'tags.id')
            ->get();
        $result = [
            'preguntas' => $preguntas,
            'tags' => $tags,
        ];
        return $result;
        
    }
}
