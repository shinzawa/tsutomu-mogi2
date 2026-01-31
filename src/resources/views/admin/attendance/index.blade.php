@extends('layouts.default')

<!-- タイトル -->
@section('title','勤怠一覧')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/authentication.css')  }}">
<link rel="stylesheet" href="{{ asset('/css/staff-daily-attendance.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')

<div class="main-box">
    <div class="title-box">
        <div class="title-bar"></div>
        <h1>勤怠一覧</h1>
    </div>
    <div class="day-link">
        <a class="icon-left" href="{{ route('attendance.index', ['year' => $prevYear, 'month' => $prevMonth, 'day' => $prevDay], ) }}">前日</a>
        <h2 class="icon-left">{{ $year }}/{{ $month }}/{{ $day }}</h2>

        <a class="icon-right" href="{{ route('attendance.index', ['year' => $nextYear, 'month' => $nextMonth, 'day'=> $nextDay]) }}">翌日</a>
    </div>
    <div class="staff-daily-attendances-table">
        <table>
            <tr>
                <th>名前 </th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
            @foreach ($users as $user)
            @php
            $att = $user->attendances->first();
            @endphp

            <tr>
                <td>{{ $user->name }} </td>
                <td>{{ \Carbon\Carbon::parse($att->clock_in)->format('H:i') }} </td>
                <td>{{ \Carbon\Carbon::parse($att->clock_out)->format('H:i') }} </td>
                <td>{{ sprintf('%d:%02d', floor($att->total_break_minutes / 60), $att->total_break_minutes % 60) }}
                </td>
                <td>{{ floor($att->work_minutes / 60) }}:{{ $att->work_minutes % 60 }}</td>
                <td>
                    <a href="{{ route('admin.attendance.detail', $att->id) }}">
                        詳細
                    </a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection