@extends('layouts.default')

<!-- タイトル -->
@section('title','申請一覧')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/authentication.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')
<div class="main-box">
    <div class="title-box">
        <div class="title-bar"></div>
        <h1>申請一覧</h1>
    </div>
    //TODO 承認待ちと承認済のタグと境界の線を入れる
    <div class="staff-table">
        <table>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
            @foreach ($correction_request_attendances as $attendance)
            <tr>
                <td>{{ $attendance->status }} </td>
                <td>{{ $user->name }} </td>
                <td>{{ $workDate }} </td>
                <td>{{ $attencance->report }}</td>
                <td>{{ $attencance->created_at }}</td>
                <td>
                    <a href="/attendance/staff/{{ $user->id }}">
                        詳細
                    </a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection