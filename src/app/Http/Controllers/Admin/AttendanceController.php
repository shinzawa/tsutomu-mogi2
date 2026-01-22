<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function show()
    {
        return view('/admin/attendance/index');
    }

    public function detail($id)
    {
        return view('/admin/attendance/show');
    }

    public function staffIndex()
    {
        return view('/admin/staff/index');
    }

    public function index($id)
    {
        return view('/admin/staff/attendance/index');
    }
}
