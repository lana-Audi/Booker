<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'apartment_id',
        'end_date'  ,
        'start_date',
        'user_id',
        'deleted_at'
    ];


    public function Apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
    
    public function user()
    {
        return $this ->belongsTo(User::class);
    }

    public function review()
    {
        return $this->hasOne(Rating::class);
    }
    
}
