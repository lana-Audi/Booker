<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{

    protected $fillable = [
        'location' ,
        'rent_price',
        'sale_price' ,
        'apartment_area'
    ];

    public function reservation()
    {
        return $this->hasMany(Reservation::class);
    }
}
