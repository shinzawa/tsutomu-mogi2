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
申請一覧
@endsection