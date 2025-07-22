<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class posts extends BaseModel
{
    use HasFactory;
    protected $fillable = [
        "image_in",
        "image_out",
        "title",
        "users_id",
        "id",
    ];

    protected $casts = [
        'id' => 'integer',
        'users_id' => 'integer',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'users_id', 'id');
    }
    public function descriptions(){
        return $this->hasOne(descriptions::class);
    }
    public function postsSaved(){
        return $this->hasMany(posts_saved::class, 'posts_id');
    }
}