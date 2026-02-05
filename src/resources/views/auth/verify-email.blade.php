@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css')}}">
@endsection

@section('link')
@endsection

@section('content')
<div class="mail-certification-frame">
    <div class="mail-certification-frame-top">
        <div class="mail-certification">
            <p class="mail-certification-request">登録していただいたメールアドレスに認証\
                メールを送付しました。
                <br>メール認証を完了してください。
            </p>
        </div>
    </div>
    <div class="mail-certification-frame-top">
        <div class="mail-certification-direction">
            <a href="http://localhost:8025">
                認証はこちらから
            </a>
        </div>
    </div>
    <div class="mail-certification-frame-top">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <div class="mail-form">
                <button class="mail-form-btn" type="submit">認証メールを再送する</button\>
            </div>
        </form>
    </div>
</div>
@endsection('content')