<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{

    protected $fillable = [
        'city',
        'Governorate',
        'rent_price',
        'apartment_space',
        'rooms',
        'floor',
        'bathrooms',
        'apartment_image',
    ];

    public function reservation()
    {
        return $this->hasMany(Reservation::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_apartment');
    }
}
