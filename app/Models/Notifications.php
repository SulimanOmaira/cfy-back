<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;

    protected $fillable = [
        'users_id', 
        'title', 
        'body', 
        'view_users_id', 
    ];

    protected $casts = [
        'users_id' => 'integer',
        'view_users_id' => 'integer',
    ];

    public function users() {
        return $this->belongsTo(User::class);
    }
}
