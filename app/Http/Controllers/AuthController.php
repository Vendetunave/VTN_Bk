<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function register(Request $request)
    {
        //validate incoming request 
        /**$this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);
        try {
            $user = new User;
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $plainPassword = $request->input('password');
            $user->password = app('hash')->make($plainPassword);
            $user->save();
            //return successful response
            return response()->json(['user' => $user, 'message' => 'CREATED'], 201);
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Registration Failed!'], 409);
        }**/
    }
    public function login(Request $request){
        $user = User::where('email', $request->email)->first();
        if($user->password_encrypt){
            if(md5($request->password) === $user->password){
                //Update with new encription
                $user->password = Hash::make($request->password);
                $user->password_encrypt = false;
                $user->save();
                //
                $credentials = array(
                    "email" => $user->email,
                    "password" => $user->password
                );
                if (! $token = Auth::attempt($credentials)) {
                    return response()->json(['message' => 'Unauthorized'], 401);
                }else{
                    return $this->respondWithToken($token);
                    return response()->json([
                        'token_server' => array(
                            'access_token' => $token,
                            'token_type' => 'bearer',
                            'expires_in' => Auth::guard()->factory()->getTTL() * 60
                        ),
                        'user' => $user
                    ], 200);
                }
            }else{
                return response()->json(['message' => 'User Password Old Failed!'], 409);
            }
        }else{
            $credentials = array(
                "email" => $request->email,
                "password" => $request->password
            );
            if (! $token = Auth::attempt($credentials)) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }else{

                return response()->json([
                    'token_server' => array(
                        'access_token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => Auth::guard()->factory()->getTTL() * 60
                    ),
                    'user' => $user
                ], 200);
            }
        }
        
    }
    
    public function me(Request $request){

    }

}