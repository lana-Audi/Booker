<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{

    
        protected $fillable = [
            'user_id',       
            'first_name',
            'last_name',
            'date_of_birth',
            'personal_image',
            'id_image',
        ];
    
    public function user()
    {
        return $this ->belongsTo(User::class);
    }

  

}
