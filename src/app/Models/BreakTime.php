<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;
    
    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function getDurationMinutesAttribute()
    {
        if (!$this->break_start || !$this->break_end) {
            return 0;
        }

        return \Carbon\Carbon::parse($this->break_end)
            ->diffInMinutes(\Carbon\Carbon::parse($this->break_start));
    }
}
