<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class cars extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        "type",
        "image",
    ];

    public function description(){
        return $this->hasMany(descriptions::class);
    }    
}
