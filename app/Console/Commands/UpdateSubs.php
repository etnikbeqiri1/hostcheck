<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;

class UpdateSubs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subs:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Users Table With Sub Info';

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
        $subs = \App\Models\Subscription::all();
        foreach ($subs as $sub){
            if($sub->time < time()){
                $sub->active = 0;
                $sub->save();
                $user = \App\Models\User::find($sub->user_id);
                $user->premium = 0;
                $user->save();

            }elseif ($sub->time > time()){
                $user = \App\Models\User::find($sub->user_id);
                $user->premium = 1;
                $sub->active = 1;
                $sub->save();
                $user->save();
            }
        }
        $this->info('Successfully all subs rechecked.');

    }
}
