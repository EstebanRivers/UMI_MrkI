<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users\Institution;


class Career extends Model
{
    public function institution() 
    { 
        return $this->belongsTo(Institution::class); 
    }

}
