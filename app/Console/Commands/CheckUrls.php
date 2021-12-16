<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use TelegramBot\Api\BotApi;

class CheckUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'url:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check urls';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       $all_ActiveUrls = \App\Models\UrlSettings::where('active', true)->get();
       foreach ($all_ActiveUrls as $activeUrl){
           $url_data = \App\Models\Urls::find($activeUrl->url_id);
           if(($url_data->time_checked+$activeUrl->time_to_check) < time() ){
               try {
                   $this->info('checking website: '.$url_data->site);
                   $response = Http::timeout(5)->get($url_data->site);
                   $this->info($response->status());
                   $url_data->last_response = $response->status();
                   $url_data->time_checked = time();
                   $url_data->need_check = false;
                   $url_data->save();
                   $activeUrl->time_checked = time();
                   $activeUrl->save();


                   if(env('NOTIFY_ME_WHEN_URL_IS_WORKING')){
                       //send telegram or phone msg when active

                       $userToSend = \App\Models\UserUrl::where('url_settings_id',$activeUrl->id)->first();
                       $bot = new BotApi(env('TELEGRAM_TOKEN_BOT'));

                       $userDetailsForMsg = \App\Models\UserDetails::where('user_id',$userToSend->user_id)->first();

                       if ($userDetailsForMsg->telegram_id != '') {
                           $bot->sendMessage($userDetailsForMsg->telegram_id ,'Website is working! '.$url_data->site);
                       }elseif ($userDetailsForMsg->phone != ''){
                           //send text msg
                       }
                   }




               } catch (\Exception $exception) {
                   $this->info('website down: '.$url_data->site);
                   $url_data->last_response = 404;
                   $url_data->time_checked = time();
                   $url_data->need_check = false;
                   $url_data->save();
                   $activeUrl->time_checked = time();
                   $activeUrl->save();

                   //send telegram or phone msg when down
                   $userToSend = \App\Models\UserUrl::where('url_settings_id',$activeUrl->id)->first();
                   $bot = new BotApi(env('TELEGRAM_TOKEN_BOT'));

                   $userDetailsForMsg = \App\Models\UserDetails::where('user_id',$userToSend->user_id)->first();

                   if ($userDetailsForMsg->telegram_id != '') {
                       $bot->sendMessage($userDetailsForMsg->telegram_id ,'Website is down! '.$url_data->site);
                   }elseif ($userDetailsForMsg->phone != ''){
                       //send text msg
                   }
               }
           }else{
               $activeUrl->time_checked = $url_data->time_checked;
               $activeUrl->save();
               //send mesg when site is down and not chercked here
               $userToSend = \App\Models\UserUrl::where('url_settings_id',$activeUrl->id)->first();
               $bot = new BotApi(env('TELEGRAM_TOKEN_BOT'));

               $userDetailsForMsg = \App\Models\UserDetails::where('user_id',$userToSend->user_id)->first();
               $statusi = '';
               if ($url_data->last_response == '200') {
                    $statusi=1;
               }else{
                   $statusi=0;
               }

               if(env('NOTIFY_ME_WHEN_URL_IS_WORKING') && $statusi == 1){
                   if ($userDetailsForMsg->telegram_id != '') {
                       $bot->sendMessage($userDetailsForMsg->telegram_id ,'Website is Checked Before and its working! '.$url_data->site);
                   }elseif ($userDetailsForMsg->phone != ''){
                       //send text msg
                   }
               }elseif($statusi == 0){
                   if ($userDetailsForMsg->telegram_id != '') {
                       $bot->sendMessage($userDetailsForMsg->telegram_id ,'Website is Checked Before and its down! '.$url_data->site);
                   }elseif ($userDetailsForMsg->phone != ''){
                       //send text msg
                   }
               }




           }

       }

        $this->info('all urls are updated');
    }
}
