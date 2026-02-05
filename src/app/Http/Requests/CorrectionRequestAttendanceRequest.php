<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class CorrectionRequestAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true; // 認可はコントローラで行う
    }

    public function rules()
    {
        return [
            'clock_in'  => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'break_start.*' => ['nullable', 'date_format:H:i'],
            'break_end.*'   => ['nullable', 'date_format:H:i'],
            'note' => ['required', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $attendance = $this->route('id')
                ? \App\Models\Attendance::find($this->route('id'))
                : null;

            if (!$attendance) {
                return;
            }

            $workDate = $attendance->work_date->format('Y-m-d');

            // 出勤・退勤の比較
            $clockIn  = Carbon::parse($workDate . ' ' . $this->clock_in);
            $clockOut = Carbon::parse($workDate . ' ' . $this->clock_out);

            if ($clockIn->gt($clockOut)) {
                $validator->errors()->add('clock_in', '出勤時間が不適切な値です');
            }

            // 休憩開始・終了の比較
            $startList = $this->break_start ?? [];
            $endList   = $this->break_end ?? [];

            foreach ($startList as $i => $start) {

                if (!$start) continue;

                $breakStart = Carbon::parse($workDate . ' ' . $start);

                if ($breakStart->gt($clockOut)) {
                    $validator->errors()->add("break_start.$i", '休憩時間が不適切な値です');
                }
            }

            foreach ($endList as $i => $end) {

                if (!$end) continue;

                $breakEnd = Carbon::parse($workDate . ' ' . $end);

                if ($breakEnd->gt($clockOut)) {
                    $validator->errors()->add("break_end.$i", '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'note.required' => '備考を記入してください',
        ];
    }
}
