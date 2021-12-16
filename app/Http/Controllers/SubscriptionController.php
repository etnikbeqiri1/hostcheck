<?php

namespace App\Http\Controllers;


use App\Models\SubscriptionTier;
use App\Models\User;
use App\Models\UserDetails;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use TelegramBot\Api\BotApi;


class SubscriptionController extends Controller
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

    public function getSubscriptionHistory(){
        //return response()->json(Auth::user()->subscription_history()->get());
        return response()->json(array(
            'status' => true,
            'message' => 'All Subscriptions for user '.Auth::user()->name,
            'error' => '',
            'sub_history' => Auth::user()->subscription_history()->get(),
        ), 200);
    }

    public function addSub(Request $request){
        try {
            $tier = \App\Models\SubscriptionTier::find($request->sub_tier);
            $user = Auth::user()->id;
            $activeUntil = time()+$tier->time;

            $hist = new \App\Models\SubscriptionHistory();
            $hist->user_id = $user;
            $hist->paid_time = time();
            $hist->subscription_tiers_id = $tier->id;
            $hist->active_until = $activeUntil;
            $hist->paid = true;
            $hist->method = $request->p_method;
            $hist->push();


            $subOld = Auth::user()->subscripton()->get();

            if(!$subOld->isEmpty()){
                foreach ($subOld as $subat){
                    \App\Models\Subscription::find($subat->id)->delete();
                }
            }
            $sub = new \App\Models\Subscription();
            $sub->user_id = $user;
            $sub->subscription_history_id = $hist->id;
            $sub->time = $activeUntil;
            $sub->active = true;
            $sub->push();




            return response()->json(array(
                'status' => true,
                'message' => 'Added New Subscription for user '.Auth::user()->name . ' and with tier '.$tier->name,
                'error' => 200,
                'sub_history' => $hist,
            ), 200);

        } catch (\Exception $exception) {
            return response()->json(array(
                'status' => false,
                'message' => 'Something Wrong Happened Please Check Again Later!',
                'error' => 404,
            ), 404);
        }

    }



}
