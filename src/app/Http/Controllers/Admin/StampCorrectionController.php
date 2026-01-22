<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StampCorrectionController extends Controller
{
    public function index()
    {
        return view('/admin/stamp_correction/index');
    }

    public function approve($attendance_correct_request_id)
    {
        return view('/admin/stamp_correction/show');
    }
}
