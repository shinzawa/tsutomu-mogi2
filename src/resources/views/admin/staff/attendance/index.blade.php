@extends('layouts.default')

<!-- タイトル -->
@section('title','スタッフ別勤怠一覧')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/authentication.css')  }}">
<link rel="stylesheet" href="{{ asset('/css/indextable.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')

<div class="main-box">
    <div class="title-box">
        <div class="title-bar"></div>
        <h1>{{$user->name}}さんの勤怠一覧</h1>
    </div>
    <div class="month-link">
        <a class="icon-left" href="{{ route('admin.staff.attendance.index', ['id' =>$user->id, 'year' => $prevYear, 'month' => $prevMonth]) }}">前月</a>
        <h2 class="icon-left">{{ $year }}/{{ $month }}</h2>

        <a class="icon-right" href="{{ route('admin.staff.attendance.index', ['id' =>$user->id, 'year' => $nextYear, 'month' => $nextMonth]) }}">翌月</a>
    </div>
    <div class="index-table">
        <table>
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($dates as $date)
                @php
                $dateStr = $date->format('Y-m-d');
                $attendance = $attendanceMap[$dateStr] ?? null;
                @endphp
                <tr>
                    <td>
                        {{ $date->format('m/d') }}
                        ({{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }})
                    </td>
                    <td>
                        {{ $attendance?->clock_in
                            ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                            : '' }}
                    </td>
                    <td>
                        {{ $attendance?->clock_out
                            ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                            : '' }}
                    </td>
                    <td>
                        {{ $attendance?->total_break_minutes ? sprintf('%d:%02d', floor($attendance->total_break_minutes / 60), $attendance->total_break_minutes % 60) : '' }}
                    </td>
                    <td>
                        @if ($attendance)
                        {{ floor($attendance->work_minutes / 60) }}:
                        {{ $attendance->work_minutes % 60 }}
                        @endif
                    </td>
                    <td>
                        @if ($attendance)
                        <a href="{{ route('admin.attendance.show', ['attendance' => $attendance->id, 'user' => $user]) }}">
                            詳細
                        </a>
                        @else
                        <a href="{{ route('admin.attendance.show', ['attendance' => -1, 'user' => $user]) }}">
                            詳細
                        </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{-- ▼ CSV 出力ボタン --}}
    <div class="csv-btn">
        <a class="csv-button"
            href="{{ route('admin.attendance.exportCsv', ['user' => $user->id, 'year' => $year, 'month' => $month]) }}">
            CSV出力
        </a>
    </div>
</div>
@endsection