@extends('layouts.default')

<!-- タイトル -->
@section('title','修正申請承認')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/authentication.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')
修正申請承認画面（管理者）
@endsection