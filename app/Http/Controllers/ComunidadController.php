<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Users;
use App\Models\Pregunta;
use App\Models\Respuestas;
use App\Models\Tags;
use App\Models\preguntas_tags;

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

    public function show(Request $request)
    {
        $filtros = array(
            'page' => $request->query('page') ? $request->query('page') : 1,
            'q' => $request->query('q') ? $request->query('q') : null
        );

        $preguntas = Pregunta::select( 'pregunta.*', \DB::raw('COUNT(R.id) AS repuestas'), \DB::raw('MAX(R.fecha) AS ult_respuesta') )
            ->leftJoin('respuestas AS R', 'R.pregunta_id', 'pregunta.id')
            ->where('pregunta.aprobado', 1);

            if($filtros['q']){
                $search = $filtros['q'];
                $preguntas->Where(function ($query) use ($search) {
                    $query->orWhere('pregunta.titulo', 'LIKE', '%' . $search . '%');
                    $query->orWhere('pregunta.descripcion', 'LIKE', '%' . $search . '%');
                    $query->orWhere('R.respuesta', 'LIKE', '%' . $search . '%');
                });

            }

        $total_records = count($preguntas->groupBy('pregunta.id')->get());
        $preguntas = $preguntas->groupBy('pregunta.id')
            ->orderBy('pregunta.fecha', 'DESC')
            ->offset(($filtros['page'] - 1) * 10)->limit(10)->get();

        $tags = Tags::select( 'tags.id', 'tags.tag', 'PT.pregunta_id' )
            ->leftJoin('preguntas_tags  AS PT', 'PT.tag_id', 'tags.id');
            foreach ($preguntas as $pregunta) {
                $tags->orWhere('PT.pregunta_id', $pregunta->id);
            }
            $tags = $tags->get();

        $result = [
            'page' => $filtros['page'],
            'q' => $filtros['q'],
            'preguntas' => $preguntas,
            'tags' => $tags,
            'total_records' => $total_records
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

    public function allTags()
    {
        $tags = Tags::all();

        $result = [
            'tags' => $tags,
        ];

        return $result;
    }

    public function createQuestion(Request $request)
    {
        try {
            $preguntaInsert = Pregunta::insertGetId(['titulo' => $request->title, 'descripcion' => $request->description, 'user_id' => $request->user_id, 'fecha' => date('Y.m.d H:i:s')]);

            if ($request->get('tags')) {
                foreach ($request->tags as $item) {
                    $tags = Tags::where('tag', $item)->first();
                    if (!$tags) {
                        $tagsInsert = Tags::insertGetId(['tag' => $item]);
                    }
                    preguntas_tags::insert(['pregunta_id' => $preguntaInsert, 'tag_id' => (!$tags)? $tagsInsert: $tags->id]);
                }
            }

            $result = [
                'status' => true
            ];

            return $result;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
            ];

            return $response;
        }
    }

    public function createComent(Request $request)
    {
        try {
            $user = Users::where('id', $request->user_id)->first();

            if ($user->locked == 0) {
                $respuesta = Respuestas::insert(['pregunta_id' => $request->idPregunta, 'respuesta' => $request->comentario, 'user_id' => $user->id, 'fecha' => date('Y-m-d H:i:s')]);
            }

            $result = [
                'status' => $user->locked ? false: true,
                'locked' => $user->locked
            ];

            return $result;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
            ];

            return $response;
        }
    }
}
