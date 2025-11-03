<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class studentController extends Controller
{
    //
    public function index(){
        $data = Facility::all();

        return view('layouts.ControlAdmin.Listas.students.index', compact('data'));
    }
}
