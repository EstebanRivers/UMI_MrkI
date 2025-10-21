<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class Workstation extends Model
{
    public function department() { 
        return $this->belongsTo(Department::class); 
    }

}
