<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

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
        $users = User::all();
        return view('/admin/staff/index',compact('users'));
    }

    public function index($id)
    {
        return view('/admin/staff/attendance/index');
    }
}
