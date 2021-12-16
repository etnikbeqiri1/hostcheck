<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ]);

        try {

            if (! $token = $this->jwt->attempt($request->only('email', 'password'))) {
                return response()->json(array(
                    'status' => 'false',
                    'message' => 'Email Or Password Are Incorrect!',
                    'error' => 404,
                ), 404);
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(array(
                'status' => 'false',
                'message' => 'The Provided Token has been expired!',
                'error' => 404,
            ), 404);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(array(
                'status' => 'false',
                'message' => 'Token is Invalid!',
                'error' => 500,
            ), 500);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 500);

        }

        $user = Auth::user();
        return response()->json(array(
            'status' => 'true',
            'message' => 'Login Successfully',
            'token' => $token,
            'token_full' => 'Bearer '.$token,
            'welcome_msg' => 'Welcome '.$user->name,
        ), 200);

    }


    public function create(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'name'    => 'required',
            'password' => 'required',
        ]);

        try {

            $user = \App\Models\User::where('email' , $request->email)->get();
            if(sizeof($user) >= 1){
                return response()->json(array(
                    'status' => 'false',
                    'message' => 'Email Has Been Already Registered!',
                    'error' => 404,
                ), 404);
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(array(
                'status' => 'false',
                'message' => 'The Provided Token has been expired!',
                'error' => 404,
            ), 404);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(array(
                'status' => 'false',
                'message' => 'Token is Invalid!',
                'error' => 500,
            ), 500);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 500);

        }


        //creating new user and adding data with try
        try {
            $user = new \App\Models\User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->push();
            //dd($user);

            return response()->json(array(
                'status' => 'true',
                'message' => 'Registered Successfully',
                'error' => '',
                'userEmail' => $user->email,
                'userName' => $user->name,
            ), 200);
        } catch (\Exception $e) {

            return response()->json(array(
                'status' => 'false',
                'message' => 'Something Wrong happened during register please try again later!',
                'error' => 'Register Data Wrong',

            ), 200);
        }

    }
}
