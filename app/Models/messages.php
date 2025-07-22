<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class messages extends BaseModel
{
    use HasFactory;
    protected $fillable = [
    "text",
    "state",
    "sender_users_id",
    "resiver_users_id",
    "room_id",
    ];

    protected $casts = [
        'sender_users_id' => 'integer',
        'resiver_users_id' => 'integer',
        'room_id' => 'integer',
    ];
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function rooms(){
        return $this->belongsTo(room::class);
    }
}
