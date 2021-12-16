<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\UserDetails;


use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class UserDetailsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getUserDetails(){
        return response()->json(Auth::user());
    }


    public function store(Request $request)
    {
        try {
            $dreni = Auth::user()->userdetails()->get();

            if ($dreni->isEmpty()){
                $usrDetails = new UserDetails();
                $usrDetails->user_id = Auth::user()->id;
                $usrDetails->telegram_id = $request->telegram_id;
                $usrDetails->telegram_token = $this->generateRandomString();
                $usrDetails->phone = $request->phone;
                $usrDetails->country = $request->country;
                $usrDetails->saveOrFail();
                return response()->json(array(
                    'status' => 'true',
                    'message' => 'You have Successfully added userdata',
                    'error' => 200,
                ), 200);
            }
            return response()->json(array(
                'status' => 'false',
                'message' => 'You have already added your userdata please edit them!',
                'error' => 201,
            ), 201);
        } catch (\Exception $exception) {
            return response()->json(array(
                'status' => 'false',
                'message' => 'Something went wrong try again later!',
                'error' => 404,
            ), 404);
        }

    }

    public function edit(Request $request){
        try {
            $dreni = Auth::user()->userdetails()->first();

            if(isset($request->phone)){
                $dreni->phone = $request->phone;
            }
            if(isset($request->country)){
                $dreni->country = $request->country;
            }
            if(isset($request->telegram_id)){
                $dreni->telegram_id = $request->telegram_id;
            }
//            if(isset($request->telegram_token)){
//                $dreni->telegram_token = $request->telegram_token;
//            }

            $dreni->saveOrFail();
            return response()->json(array(
                'status' => 'true',
                'message' => 'userdetails saved successfully!',
                'error' => 200,
                'userdetails' => $dreni,
                'user' => $dreni->user,
            ), 200);
        } catch (\Exception $exception) {
            return response()->json(array(
                'status' => 'false',
                'message' => 'Something went wrong try again later!',
                'error' => 404,
            ), 404);
        }
    }

    public function delete()
    {
        try {

            $dreni = Auth::user()->userdetails()->firstOrFail();
            $dreni->delete();
            return response()->json(array(
                'status' => 'true',
                'message' => 'User deleted user details',
                'error' => 200,
            ), 200);

        } catch (\Exception $exception) {
            return response()->json(array(
                'status' => 'false',
                'message' => 'User dosent have any user details saved!',
                'error' => 404,
            ), 404);
        }

    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    public function storeTelegramUserID(){

        $dreni = Auth::user()->userdetails()->first();

        if ($dreni == null){
            $usrDetails = new UserDetails();
            $usrDetails->user_id = Auth::user()->id;
            $usrDetails->telegram_id = '';
            $usrDetails->telegram_token = $this->generateRandomString();
            $usrDetails->phone = '';
            $usrDetails->country = '';
            $usrDetails->saveOrFail();
            return response()->json(array(
                'status' => 'true',
                'message' => 'ok',
                'verification_code' => $usrDetails->telegram_token,
                'bot_username' => env('TELEGRAM_BOT_URL'),
                'error' => 200,
            ), 200);
        }else{
            $dreni->telegram_token = $this->generateRandomString();
            $dreni->save();
            return response()->json(array(
                'status' => 'true',
                'message' => 'ok',
                'verification_code' => $dreni->telegram_token,
                'bot_username' => env('TELEGRAM_BOT_URL'),
                'error' => 200,
            ), 200);
        }


    }


    public function checkCode(){
        $telegram_bot_token = env('TELEGRAM_TOKEN_BOT');

        $bot = new \TelegramBot\Api\BotApi($telegram_bot_token);

        foreach($bot->getUpdates() as $update){
            $text = $update->getMessage()->getText();
            $dreni = Auth::user()->userdetails()->firstOrFail();
            //Log::info($text);
            if($update->getMessage()->getText() == $dreni->telegram_token){
                $user_telegram_id = $update->getMessage()->getFrom()->getId();
                $dreni->telegram_id = $user_telegram_id;
                $dreni->save();

                $bot->sendMessage($dreni->telegram_id, "Your Account has be linked. Have a nice day ".Auth::user()->name." .");

                return response()->json(array(
                    'status' => 'true',
                    'message' => 'your profile got successfully linked',
                    'error' => 200,
                ), 200);
            }

        }
        return response()->json(array(
            'status' => 'false',
            'message' => 'please send the correct code to the bot',
            'error' => 200,
        ), 200);
    }



}
