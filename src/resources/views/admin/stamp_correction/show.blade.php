@extends('layouts.default')

<!-- タイトル -->
@section('title','修正申請承認')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/authentication.css')  }}">
<link rel="stylesheet" href="{{ asset('/css/detail.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')
修正申請承認画面（管理者）
<div class="main-box">
    <div class="title-box">
        <div class="title-bar"></div>
        <h1>勤怠詳細</h1>
    </div>
    @php
    $breaks = $correction_require_attendance->breaks;
    @endphp
    <div class="index-table">
        <form action="{{ route('correction_require_correction_require_attendance.update', $correction_require_attendance->id) }}" method="POST">
            @csrf
            @method('PUT')
            <table>
                <tr>
                    <th>名前</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        {{ \Carbon\Carbon::parse($correction_require_correction_require_attendance->work_date)->format('Y年') }}
                    </td>
                    <td></td>
                    <td>
                        {{ \Carbon\Carbon::parse($correction_require_correction_require_attendance->work_date)->format('n月 j日') }}
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <input type="time"
                            name="clock_in"
                            class="form-control"
                            value="{{ $correction_require_correction_require_attendance->requested_clock_in ? \Carbon\Carbon::parse($correction_require_correction_require_attendance->requested_clock_in)->format('H:i') : '' }}">
                    </td>
                    <td>
                        ～
                    </td>
                    <td>
                        <input type="time"
                            name="clock_out"
                            class="form-control"
                            value="{{ $correction_require_correction_require_attendance->requested_clock_out ? \Carbon\Carbon::parse($correction_require_correction_require_attendance->requested_clock_out)->format('H:i') : '' }}">
                    </td>

                </tr>
                {{-- ▼ 休憩時間の詳細行を動的に生成 --}}
                @php
                $breaks = $correction_require_correction_require_attendance->breaks;
                $breakCount = $breaks->count();
                $rows = $breakCount + 1; // 休憩n+1 の空行を追加
                @endphp
                @for ($i = 0; $i < $rows; $i++)
                    @php
                    $break=$breaks[$i] ?? null;
                    @endphp
                    <tr>
                    @if ($i==0)
                    <th>休憩</th>
                    @else
                    <th>休憩{{ $i + 1 }}</th>
                    @endif
                    @if ($break)
                    <td>
                        <input type="time"
                            name="break_start[{{ $i }}]"
                            class="form-control"
                            value="{{ $break->start ? \Carbon\Carbon::parse($break->start)->format('H:i') : '' }}">
                    </td>
                    <td>
                        〜
                    </td>
                    <td>
                        <input type="time"
                            name="break_end[{{ $i }}]"
                            class=" form-control"
                            value="{{ $break->end ? \Carbon\Carbon::parse($break->end)->format('H:i') : '' }}">
                    </td>
                    @else
                    <td>
                        {{-- 空欄 --}}
                    </td>
                    <td>
                        ~
                    </td>
                    <td>
                        {{-- 空欄 --}}
                    </td>
                    @endif
                    </tr>
                    @endfor
                    <tr>
                        <th>備考</th>
                        <td>
                            <textarea name="note" class="form-control" rows="3">{{ $correction_require_correction_require_attendance->note }}</textarea>
                        </td>
                    </tr>
            </table>
            <button type="submit" class="btn btn-primary">承認</button>
        </form>
        @endsection