<?php

namespace App\Http\Controllers;


use App\Models\SubscriptionTier;
use App\Models\User;
use App\Models\UserDetails;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use TelegramBot\Api\BotApi;


class UrlController extends Controller
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

    public function getAllUserUrls(){

        return response()->json(array(
            'status' => true,
            'message' => 'All User Urls for user '.Auth::user()->name,
            'error' => '',
            'urls' => Auth::user()->userUrl()->get(),
        ), 200);
    }

    public function addUrl(Request $request)
    {

        $user = Auth::user();
        if($user->premium == 1){
            try {
                $this->validate($request, [
                    'url'    => 'required|url|max:255',
                    'time_to_check' => 'required',
                ]);

                $findurl = \App\Models\Urls::where('site', $request->url)->count();
                if($findurl>0){
                    $new_url = \App\Models\Urls::where('site', $request->url)->first();
                }else{
                    $new_url = new \App\Models\Urls();
                    $new_url->site = $request->url;
                    $new_url->last_response = 200;
                    $new_url->time_checked = 0;
                    $new_url->need_check = true;

                    $new_url->save();
                }

                //$getAllUserUrl = \App\Models\UserUrl::where('user_id', Auth::user()->id)->get();
                $dreni = DB::select(DB::raw('SELECT user_id, (SELECT url_id from url_settings where url_settings.id = user_urls.url_settings_id) as site_id from user_urls where user_id = '.Auth::user()->id.' having site_id = '.$new_url->id.';'));

                if(count($dreni) > 0){
                    return response()->json(array(
                        'status' => false,
                        'message' => 'You already added this site',
                        'error' => 'You already added this site',
                    ), 200);
                }


                $new_url_settings = new \App\Models\UrlSettings();
                $new_url_settings->time_to_check = $request->time_to_check;
                $new_url_settings->active = true;
                $new_url_settings->time_checked = 0;
                $new_url_settings->url_id = $new_url->id;

                $new_url_settings->save();
                $new_user_urls = new \App\Models\UserUrl();
                $new_user_urls->url_settings_id = $new_url_settings->id;
                $new_user_urls->user_id = Auth::user()->id;

                $new_user_urls->push();
                return response()->json(array(
                    'status' => true,
                    'message' => 'Added new url',
                    'error' => '',
                    'subscriber' => true,
                    'url' => $new_url,
                    'url_settings' => $new_url_settings,
                    'user_urls' => $new_user_urls,
                ), 200);
            } catch (\Exception $exception) {
                return response()->json(array(
                    'status' => false,
                    'subscriber' => true,
                    'message' => 'something wrong happened please try again later!',
                    'error' => 'prime error',
                ), 200);
            }
        }else{

            //when user is not subscriber


                $urls_of_user = $user->userUrl()->get();
                $active_urls = 0;
                foreach ($urls_of_user as $urluser){
                    $url_settings_from_db = \App\Models\UrlSettings::find($urluser->url_settings_id);
                    if($url_settings_from_db->active == true){
                        $active_urls = $active_urls +1;
                    }
                }
                if($active_urls>1){
                    return response()->json(array(
                        'status' => false,
                        'message' => 'You have to upgrate in order to use more urls',
                        'subscriber' => false,
                        'error' => 'non prime',
                    ), 200);
                }else{
                    try {
                        $this->validate($request, [
                            'url'    => 'required|url|max:255',
                            'time_to_check' => 'required',
                        ]);

                        $findurl = \App\Models\Urls::where('site', $request->url)->count();
                        if($findurl>0){
                            $new_url = \App\Models\Urls::where('site', $request->url)->first();
                        }else{
                            $new_url = new \App\Models\Urls();
                            $new_url->site = $request->url;
                            $new_url->last_response = 200;
                            $new_url->time_checked = 0;
                            $new_url->need_check = true;

                            $new_url->save();
                        }



                        $new_url_settings = new \App\Models\UrlSettings();
                        $new_url_settings->time_to_check = $request->time_to_check;
                        $new_url_settings->active = true;
                        $new_url_settings->time_checked = 0;
                        $new_url_settings->url_id = $new_url->id;

                        $new_url_settings->save();
                        $new_user_urls = new \App\Models\UserUrl();
                        $new_user_urls->url_settings_id = $new_url_settings->id;
                        $new_user_urls->user_id = Auth::user()->id;

                        $new_user_urls->push();
                        return response()->json(array(
                            'status' => true,
                            'message' => 'Added new url',
                            'error' => '',
                            'subscriber' => false,
                            'url' => $new_url,
                            'url_settings' => $new_url_settings,
                            'user_urls' => $new_user_urls,
                        ), 200);
                    } catch (\Exception $exception) {
                        return response()->json(array(
                            'status' => false,
                            'subscriber' => false,
                            'message' => 'something wrong happened please try again later!',
                            'error' => 'non prime error',
                        ), 200);
                    }

                }
        }
    }

    public function changeStateOfURL($id){
        try {
        $urlSettings = \App\Models\UrlSettings::where('url_id' , $id)->get();
        $changedState = [];
        foreach ($urlSettings as $urlS){
            $urlsUser = \App\Models\UserUrl::where('url_settings_id' , $urlS->id)->first();
            if($urlsUser->user_id == Auth::user()->id){

                if($urlS->active){
                    $urlS->active = false;
                    $urlS->save();
                    $changedState += ['disabled' => \App\Models\Urls::find($urlS->url_id)->site];

                }else{
                    $urlS->active = true;
                    $urlS->push();
                    $changedState += ['enabled' => \App\Models\Urls::find($urlS->url_id)->site];


                }
            }
        }
            return response()->json(array(
                'status' => true,
                'message' => 'Url disabled/enabled!',
                'error' => '',
                'changed' => $changedState,
            ), 200);

        } catch (\Exception $exception) {
            return response()->json(array(
                'status' => false,
                'message' => 'Url can not be disabled or enabled check back later!',
                'error' => 'url error',
            ), 200);
        }

    }

}
