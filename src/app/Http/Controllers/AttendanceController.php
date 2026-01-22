<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function show()
    {
        return view('/attendance/record');
    }

    public function index()
    {
        return view('/attendance/index');
    }

    public function detail($id)
    {
    return view('/attendance/show');
    }
}
