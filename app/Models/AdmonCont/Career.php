<?php

namespace App\Models\AdmonCont;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    //
    use HasFactory;

    protected $table = 'carrers';

    protected $fillable = [
        'official_id',
        'name',
        'description1',
        'description2',
        'description3',
        'type',
        'semesters'
    ];
}
