<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{

    protected $fillable = [
        'location' ,
        'rent_price',
        'sale_price' ,
        'apartment_space',
        'rooms' ,
        'floor',
        'bathrooms' ,
        'apartment_space'
    ];

    public function reservation()
    {
        return $this->hasMany(Reservation::class);
    }
    public function users(){
        return $this->belongsToMany(User::class,'user_apartment');
    }
}
