<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class UserUrl extends Model
{

    protected $table = 'user_urls';
    //protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        '',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        //
    ];


//    public function subscription_history()
//    {
//        return $this->hasOne('App\Models\SubscriptionHistory');
//    }
//
    public function users()
    {
        return $this->hasMany('App\Models\User');
    }
    public function urlSettings()
    {
        return $this->hasMany('App\Models\UrlSettings');
    }

}
