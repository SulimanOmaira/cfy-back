<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class room extends BaseModel
{
    use HasFactory;
    protected $fillable = [
        "name",
    ];

    public function users(){
        return $this->belongsToMany(User::class, 'rooms_user', 'rooms_id', 'users_id');
    }
    public function messages(){
        return $this->hasMany(messages::class);
    }
}
