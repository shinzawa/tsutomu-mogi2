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
    $breaks = $correction->attendance->breaks;
    @endphp
    <div class="index-table">
        <form action="{{ route('admin.stamp_correction.approve', $correction->id) }}" method="POST">
            @csrf
            <table>
                <tr>
                    <th>名前</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        {{ \Carbon\Carbon::parse($correction->attendance->work_date)->format('Y年') }}
                    </td>
                    <td></td>
                    <td>
                        {{ \Carbon\Carbon::parse($correction->attendance->work_date)->format('n月 j日') }}
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td class="form-control">
                        {{ $correction->requested_clock_in ? \Carbon\Carbon::parse($correction->requested_clock_in)->format('H:i') : '' }}
                    </td>
                    <td>
                        ～
                    </td>
                    <td class="form-control">
                        {{ $correction->requested_clock_out ? \Carbon\Carbon::parse($correction->requested_clock_out)->format('H:i') : '' }}
                    </td>

                </tr>
                {{-- ▼ 休憩時間の詳細行を動的に生成 --}}
                @php
                $breaks = $correction->breaks;
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
                    <td class="form-control">
                        {{ $break->start ? \Carbon\Carbon::parse($break->start)->format('H:i') : '' }}
                    </td>
                    <td>
                        〜
                    </td>
                    <td class=" form-control">
                        {{ $break->end ? \Carbon\Carbon::parse($break->end)->format('H:i') : '' }}
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
                        <td class="form-control"> {{ $correction->reason }} </td>
                    </tr>
            </table>
            <div class="right-align">
                @if($correction->status == 'pending')
                <button type="submit" class="btn btn-primary">承認</button>
                @else
                <span class="btn btn-primary">承認済み</span>
                @endif
            </div>
        </form>
        @endsection