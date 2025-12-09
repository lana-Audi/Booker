<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    public function Apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
    public function Profile()
    {
        return $this ->belongsTo(Profile::class);
    }
}
