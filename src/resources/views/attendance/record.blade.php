@extends('layouts.default')

<!-- タイトル -->
@section('title','勤怠打刻')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/authentication.css')  }}">
<link rel="stylesheet" href="{{ asset('/css/record.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')
<div class="attendance-container">
    <div class="status-badge">
        {{ $status }}
    </div>
    <div class="date-display" id="date-display">
        {{ \Carbon\Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日(ddd)') }}
    </div>
    <div class="time-display" id="time-display">
        {{ \Carbon\Carbon::now()->format('H:i') }}
    </div>
    <form action="/attendance/updateStatus" method="POST">
        @csrf
        @if ($status === '勤務外')
        <div class="button-group clock_in-btn">
            <button type="submit" name="action" value="clock_in" class="clock_in">出勤</button>
        </div>
        @elseif ($status === '出勤中')
        <div class="buttons-group">
            <div class="button-group clock_out-btn">
                <button type="submit" name="action" value="clock_out" class="clock_out">退勤</button>
            </div>
            <div class="button-group break_in-btn">
                <button type="submit" name="action" value="break_in" class="break_in">休憩入</button>
            </div>
        </div>
        @elseif ($status === '休憩中')
        <div class="button-group break_out-btn">
            <button type="submit" name="action" value="break_out" class="break_out">休憩戻</button>
        </div>
        @elseif ($status === '退勤済')
        <div class="finished-message">お疲れ様でした。</div>
        @endif
</div>
</form>
</div>
<script src="{{ asset('/js/record.js') }}"></script>
@endsection