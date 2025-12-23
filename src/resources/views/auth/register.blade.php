@extends('layouts.default')

<!-- タイトル -->
@section('title','会員登録')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/authentication.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')
<form action="/register" method="post" class="authenticate center">
    @csrf
    <h1 class="page__title--register">会員登録</h1>
    <label for="name" class="entry__name--register">名前</label>
    <input name="name" id="name" type="text" class="input--register" value="{{ old('name') }}">
    <div class="form__error">
        @error('name')
        {{ $message }}
        @enderror
    </div>
    <label for="mail" class="entry__name--register">メールアドレス</label>
    <input name="email" id="mail" type="email" class="input--register" value="{{ old('email') }}">
    <div class="form__error">
        @error('email')
        {{ $message }}
        @enderror
    </div>
    <label for="password" class="entry__name--register">パスワード</label>
    <input name="password" id="password" type="password" class="input--register">
    <div class="form__error">
        @error('password')
        {{ $message }}
        @enderror
    </div>
    <label for="password_confirm" class="entry__name--register">パスワード確認</label>
    <input name="password_confirmation" id="password_confirm" type="password" class="input--register">
    <button class="btn btn--big">登録する</button>
    <div class="link--register">
        <a href="/login" class="link">ログインはこちら</a>
    </div>
</form>
@endsection