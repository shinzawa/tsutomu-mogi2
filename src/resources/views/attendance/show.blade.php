@extends('layouts.default')

<!-- タイトル -->
@section('title','勤怠詳細')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/authentication.css')  }}">
<link rel="stylesheet" href="{{ asset('/css/detail.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')
<div class="main-box">
    <div class="title-box">
        <div class="title-bar"></div>
        <h1>勤怠詳細</h1>
    </div>
    @php
    $breaks = $attendance->breaks;
    @endphp
    <div class="index-table">
        <table>
            <tr>
                <th>名前</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年 n月 j日') }}
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    {{ \Carbon\Carbon::parse($attendance->clock_in )->format('H:i') }}～
                    {{ \Carbon\Carbon::parse($attendance->clock_out )->format('H:i') }}
                </td>
            </tr>
            {{-- ▼ 休憩時間の詳細行を動的に生成 --}}
            @php
            $breaks = $attendance->breaks;
            $breakCount = $breaks->count();
            $rows = $breakCount + 1; // 休憩n+1 の空行を追加
            @endphp
            @for ($i = 0; $i < $rows; $i++)
                @php
                $break=$breaks[$i] ?? null;
                @endphp
                @if ($i==0)
                <tr>
                <th>休憩</th>
                <td>
                    {{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}
                    〜
                    {{ \Carbon\Carbon::parse($break->break_end)->format('H:i') }}
                </td>
                </tr>
                @else
                <tr>
                    <th>休憩{{ $i + 1 }}</th>
                    <td>
                        @if ($break)
                        {{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}
                        〜
                        {{ \Carbon\Carbon::parse($break->break_end)->format('H:i') }}
                        @else
                        {{-- 空欄 --}}
                        @endif
                    </td>
                </tr>
                @endif
                @endfor
                <tr>
                    <th>備考</th>
                    <td>
                        {{ $attendance->note }}
                    </td>
                </tr>
        </table>
        @endsection