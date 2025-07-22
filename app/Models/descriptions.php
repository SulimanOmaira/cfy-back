<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class descriptions extends BaseModel
{
    use HasFactory;
    protected $fillable = [
        'name',
        'model',
        'eng_capacity',
        'dis_travel',
        'price',
        'type_car',
        'fuel_consumption',
        'drive_system',
        'number_Seats',
        'cruise_control_system',
        'cars_id',
        'posts_id',
        
    ];

    protected $casts = [
        'price' => 'double',
        'posts_id' => 'integer',
        'cars_id' => 'integer',
        'number_Seats' => 'integer',
        'cruise_control_system' => 'boolean',
    ];
    
    
    public function posts(){
    return $this->belongsTo(posts::class);
    }

    public function cars(){
    return $this->belongsTo(cars::class);
    }
}
