<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class posts_saved extends BaseModel
{
    use HasFactory;
    protected $table = 'posts_saved';
    protected $fillable = [
        "posts_id",
        "users_id",
    ];
    protected $casts = [
        'posts_id' => 'integer',
        'users_id' => 'integer',
    ];

    public function post(){
        return $this->belongsTo(posts::class, 'posts_id');
    }
    public function users(){
        return $this->belongsToMany(User::class, 'posts_saved', 'posts_id', 'users_id');
    }

}

