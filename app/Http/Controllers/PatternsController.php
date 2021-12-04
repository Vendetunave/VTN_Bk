<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

use App\Models\Patterns;

class PatternsController extends Controller
{
    public function getPatterns()
    {
        try {
            $patterns = Patterns::where('active', 1)->inRandomOrder()->first();

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

    public function get_all_promotion()
    {
        $promotion = Patterns::select('id', 'name', 'image', 'link', 'clicks', 'active')->get();

        $response = [
            'promotion' => $promotion
        ];

        return $response;
    }

    public function get_by_promotion(Request $request)
    {
        $promotion = Patterns::select('id', 'name', 'image', 'link', 'image_mobile', 'button_name')->where('id', $request->id)->first();

        $response = [
            'promotion' => $promotion
        ];

        return $response;
    }

    public function create_promotion(Request $request)
    {
        try {
            if ($request->hasFile('image1') && $request->hasFile('image2')) {
                $nameFile = uniqid();
                $name = $nameFile . '.webp';
                $nameMobile = $nameFile . '_mobile.webp';

                $imageConvert = (string) Image::make($request->file('image1'))->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/patterns/' . $name, $imageConvert, 'public');

                $imageConvertMobile = (string) Image::make($request->file('image2'))->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/patterns/' . $nameMobile, $imageConvertMobile, 'public');

                Patterns::insert([
                    'name' => $request->title,
                    'link' => $request->link,
                    'button_name' => $request->nameButton,
                    'slug' => $request->slug,
                    'image' => 'https://vendetunave.s3.amazonaws.com/vendetunave/images/patterns/' . $name,
                    'image_mobile' => 'https://vendetunave.s3.amazonaws.com/vendetunave/images/patterns/' . $nameMobile,
                ]);

                $response = [
                    'status' => true,
                    'message' => 'Datos creados correctamente!'
                ];
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Imagen invalida!'
                ];
            }

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => strval($th)
            ];

            return $response;
        }
    }

    public function update_promotion(Request $request)
    {
        try {
            \DB::table('patterns')->where('id', $request->id)->update([
                'name' => $request->name,
                'link' => $request->link,
                'button_name' => $request->button_name,
            ]);

            if ($request->hasFile('image1')) {
                $nameFile = uniqid();
                $name = $nameFile . '.webp';

                $imageConvert = (string) Image::make($request->file('image1'))->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/patterns/' . $name, $imageConvert, 'public');

                \DB::table('patterns')->where('id', $request->id)->update([
                    'image' => 'https://vendetunave.s3.amazonaws.com/vendetunave/images/patterns/' . $name,
                ]);
            }

            if ($request->hasFile('image2')) {
                $nameFile = uniqid();
                $nameMobile = $nameFile . '_mobile.webp';

                $imageConvertMobile = (string) Image::make($request->file('image2'))->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/patterns/' . $nameMobile, $imageConvertMobile, 'public');

                \DB::table('patterns')->where('id', $request->id)->update([
                    'image_mobile' => 'https://vendetunave.s3.amazonaws.com/vendetunave/images/patterns/' . $nameMobile,
                ]);
            }

            $response = [
                'status' => true,
                'message' => 'Datos creados correctamente!'
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => strval($th)
            ];

            return $response;
        }
    }

    public function inactivate(Request $request)
    {
        $promotion = Patterns::select('active')->where('id', $request->id)->first();
        \DB::table('patterns')->where('id', $request->id)->update([
            'active' => ($promotion->active) ? 0 : 1
        ]);

        $response = [
            'status' => true,
            'active' => ($promotion->active) ? false : true
        ];

        return $response;
    }

    public function delete(Request $request)
    {
        Patterns::where('id', $request->id)->delete();
        $promotion = Patterns::select('id', 'name', 'image', 'link', 'clicks', 'active')->get();

        $response = [
            'status' => true,
            'message' => 'Se elimino la pauta!',
            'promotion' => $promotion
        ];

        return $response;
    }
}
