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
        'start',
        'end',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function getDurationMinutesAttribute()
    {
        if (!$this->start || !$this->end) {
            return 0;
        }

        return \Carbon\Carbon::parse($this->end)
            ->diffInMinutes(\Carbon\Carbon::parse($this->start));
    }
}
