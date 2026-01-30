@extends('layouts.default')

<!-- タイトル -->
@section('title','スタッフ一覧')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/stafftable.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')
<div class="main-box">
    <div class="title-box">
        <div class="title-bar"></div>
        <h1>スタッフ一覧</h1>
    </div>
    <div class="staff-table">
        <table>
            <tr>
                <th>名前 </th>
                <th>メールアドレス </th>
                <th>月次勤怠 </th>
            </tr>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->name }} </td>
                <td>{{ $user->email }} </td>
                <td>
                    <a href="/admin/attendance/staff/{{ $user->id }}">
                        詳細
                    </a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection