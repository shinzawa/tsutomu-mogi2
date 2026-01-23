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
<h1 >スタッフ一覧</h1>
<div class="stafftable">
    <table>
        <tr>
            <th>名前 </th>
            <th>メールアドレス </th>
            <th>詳細 </th>
        </tr>
        @foreach ($users as $user)
        <tr>
            <td>{{ $user->name }} </td>
            <td>{{ $user->email }} </td>
            <td>detail</td>
        </tr>
        @endforeach
    </table>
</div>

@endsection