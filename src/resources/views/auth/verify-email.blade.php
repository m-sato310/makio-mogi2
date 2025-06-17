@extends('layouts.app')

@section('title', 'メール認証')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pages/verify-email.css') }}">
@endsection

@section('hide-nav', '1')

@section('content')
<div class="verify-email-container">
    <div class="verify-email-message">
        <p>登録していただいたメールアドレスに認証メールを送付しました。 <br>
        メール認証を完了してください。</p>
    </div>

    <div class="verify-email-action">
        <a class="btn btn-primary" href="https://mailtrap.io/" target="_blank" rel="noopener noreferrer">認証はこちらから</a>
    </div>

    <div class="verify-email-resend">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="btn btn-link" type="submit">認証メールを再送する</button>
        </form>
    </div>
    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success mt-2">
            新しい認証メールを送信しました。
        </div>
    @endif
</div>
@endsection