<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'total_break_minutes',
        'note',
    ];
    
    public function getWorkMinutesAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($this->clock_in);
        $end   = \Carbon\Carbon::parse($this->clock_out);

        // 勤務時間（分）
        $total = $end->diffInMinutes($start);

        // 休憩時間を差し引く
        return max(0, $total - $this->total_break_minutes);
    }

    public function getWorkHoursAttribute()
    {
        // 分 → 時間（小数）
        return round($this->work_minutes / 60, 2);
    }
    
    public function getTotalBreakMinutesAttribute()
    {
        return $this->breaks->sum('duration_minutes');
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOnBreak()
    {
        $latest = $this->breaks()->orderBy('start', 'desc')->first();

        return $latest && is_null($latest->end);
    }

    protected $casts = [
        'work_date' => 'date:Y-m-d'
    ];
}
