<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pregunta;
use App\Models\Respuestas;
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
    public function parse_id($string){
        $id = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $string
        );
        $id = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $id
        );
        $id = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $id
        );
        $id = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $id
        );
        $id = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $id
        );
        $id = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'),
            array('n', 'N', 'c', 'C',),
            $id
        );
        //Esta parte se encarga de eliminar cualquier caracter extraño
        $id = str_replace(
            array("¨", "º", "~",
                 "#", "@", "|", "!",
                 "·", "$", "%", "&", "/",
                 "(", ")", "?", "¡",
                 "¿", "[", "^", "<code>", "]",
                 "+", "}", "{", "¨", "´",
                 ">", "< ", ";", ",", ":",
                 ".", " "),
            '',
            $id
        );
        return $id;
    }
    public function detalle($slug){
        $id_pregunta = $this->parse_id($slug);

        $arrayUrl = explode('-', $id_pregunta);
        $id = $arrayUrl[COUNT($arrayUrl) - 1];

        $pregunta = Pregunta::select('pregunta.*', 'U.nombre')->leftJoin('users AS U', 'U.id', 'pregunta.user_id')->where('pregunta.id', $id)->first();
        $respuestas = Respuestas::select('respuestas.*', 'U.nombre', 'U.image')->leftJoin('users AS U', 'U.id', 'respuestas.user_id')->where('pregunta_id', $id)->orderBy('fecha', 'DESC')->get();
        $tags = Tags::leftJoin('preguntas_tags  AS PT', 'PT.tag_id', 'tags.id')->where('PT.pregunta_id', $id)->get();

        $result = [
            'pregunta' => $pregunta,
            'respuestas' => $respuestas,
            'tags' => $tags
        ];
        return $result;
    }
}
