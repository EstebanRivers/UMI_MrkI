<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users\Institution;
use App\Models\Users\Workstation;


class Department extends Model
{
    public function institution() 
    { 
        return $this->belongsTo(Institution::class); 
    }

    public function workstations() { 
        return $this->hasMany(Workstation::class); 
    }

}
