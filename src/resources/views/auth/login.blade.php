@extends('layouts.app')

@section('title', 'ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pages/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <h1 class="auth-title">ログイン</h1>

    <form method="POST" action="{{ route('login.post') }}" novalidate>
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
            <button class="btn-primary" type="submit">ログインする</button>
        </div>
    </form>

    <div class="button-link">
        <a href="{{ url('/register') }}">会員登録はこちら</a>
    </div>
</div>
@endsection