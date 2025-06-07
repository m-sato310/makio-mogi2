@extends('layouts.app')

@section('title', $pageTitle ?? 'ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pages/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <h1 class="auth-title">{{ $heading ?? 'ログイン' }}</h1>

    <form method="POST" action="{{ $loginRoute ?? route('login.post') }}" novalidate>
        @csrf

        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}">
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password">
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <button class="btn-primary" type="submit">{{ $buttonLabel ?? 'ログインする' }}</button>
        </div>
    </form>

    @if (!($hideRegisterLink ?? false))
    <div class="button-link">
        <a href="{{ url('/register') }}">会員登録はこちら</a>
    </div>
    @endif
</div>
@endsection