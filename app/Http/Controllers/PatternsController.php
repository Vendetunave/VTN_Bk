<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Patterns;

class PatternsController extends Controller
{
    public function getPatterns(Request $request)
    {
        try {
            $patterns = Patterns::where('active', 1)->where('slug', $request->slug)->inRandomOrder()->first();

            $response = [
                'status' => true,
                'patterns' => $patterns
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = ['status' => false];

            return $response;
        }
    }

    public function onClick(Request $request)
    {
        try {
            $pattern = Patterns::select('id', 'clicks')->where('id', $request->id)->first();

            \DB::table('patterns')->where('id', $pattern->id)
                ->update(['clicks' => ($pattern->clicks + 1)]);

            $response = ['status' => true];

            return $response;
        } catch (\Throwable $th) {
            echo $th;
            $response = ['status' => false];

            return $response;
        }
    }
}
