<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
    "name",
    "email",
    "password",
    "city",
    "image",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'users_password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'users_password' => 'hashed',
    ];

    public function getJWTIdentifier(){
        return $this->getKey();
    }
    public function getJWTCustomClaims(){
        return [];
    }
    protected function serializeDate(\DateTimeInterface $date){
        return $date->format('Y-m-d H:i:s');
    }
    public function messages(){
        return $this->hasMany(messages::class);
    }
    public function postsSaved(){
        return $this->belongsToMany(posts_saved::class, 'posts_saved', 'users_id', 'posts_id');
    }
    public function posts(){
        return $this->hasMany(posts::class, 'users_id');
    }
    public function notifications(){
        return $this->hasMany(notifications::class);
    }
    public function rooms(){
        return $this->belongsToMany(room::class, 'rooms_user', 'users_id', 'rooms_id');
    }
}
