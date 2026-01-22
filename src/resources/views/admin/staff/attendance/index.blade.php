@extends('layouts.default')

<!-- タイトル -->
@section('title','スタッフ別勤怠一覧')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/authentication.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')
スタッフ別勤怠一覧（管理者）
@endsection