<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CorrectionRequestBreakTime extends Model
{
    use HasFactory;

    protected $table = 'correction_request_breakes'; // マイグレーションに合わせる

    protected $fillable = [
        'request_id',
        'start',
        'end',
    ];

    protected $dates = [
        'start',
        'end',
    ];

    // ▼ 親の修正申請
    public function request()
    {
        return $this->belongsTo(CorrectionRequestAttendance::class, 'request_id');
    }

    // ▼ 休憩時間（分）を自動計算
    public function getDurationMinutesAttribute()
    {
        if (!$this->start || !$this->end) {
            return 0;
        }

        return \Carbon\Carbon::parse($this->end)
            ->diffInMinutes(\Carbon\Carbon::parse($this->start));
    }
}
