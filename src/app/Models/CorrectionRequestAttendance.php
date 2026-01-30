<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CorrectionRequestAttendance extends Model
{
    use HasFactory;
    
    protected $table = 'correction_request_attendances';

    protected $fillable = [
        'attendances_id',
        'user_id',
        'requested_clock_in',
        'requested_clock_out',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $dates = [
        'requested_clock_in',
        'requested_clock_out',
        'reviewed_at',
    ];

    // ▼ Attendance（元データ）との紐づけ
    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendances_id');
    }

    // ▼ 申請者
    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ▼ 承認者
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ▼ 修正申請の休憩（複数）
    public function breaks()
    {
        return $this->hasMany(CorrectionRequestBreakTime::class, 'request_id');
    }
}
