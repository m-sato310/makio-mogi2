@extends('layouts.app')

@section('title', '勤怠登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pages/form.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <div class="attendance-status">
        <span class="status-label">{{ $status }}</span>
    </div>

    @php
    \Carbon\Carbon::setLocale('ja');
    @endphp
    <div class="attendance-date">
        {{ \Carbon\Carbon::now()->isoFormat('YYYY年MM月DD日(ddd)') }}
    </div>

    <span id="server-time" style="display:none;">
        {{ now()->format('Y/m/d H:i') }}
    </span>

    <div class="attendance-clock" id="current-time"></div>

    <div class="attendance-actions">
        @if ($status === '勤務外')
        <form action="{{ route('attendance.start') }}" method="POST">
            @csrf
            <button class="btn btn-primary js-confirm-clock-in" type="submit">出勤</button>
        </form>
        @elseif ($status === '出勤中')
        <form action="{{ route('attendance.finish') }}" method="POST">
            @csrf
            <button class="btn btn-primary js-confirm-clock-out" type="submit">退勤</button>
        </form>
        <form action="{{ route('attendance.break_start') }}" method="POST">
            @csrf
            <button class="btn btn-secondary js-confirm-break-start" type="submit">休憩入</button>
        </form>
        @elseif ($status === '休憩中')
        <form action="{{ route('attendance.break_end') }}" method="POST">
            @csrf
            <button class="btn btn-secondary js-confirm-break-end" type="submit">休憩戻</button>
        </form>
        @elseif ($status === '退勤済')
        <div class="attendance-message">お疲れ様でした。</div>
        @endif
    </div>

    @if ($errors->any())
    <div class="attendance-errors">
        <ul>
            @foreach ($errors->all() as $error)
            <div class="error">{{ $error }}</div>
            @endforeach
        </ul>
    </div>
    @endif

    @if (session('status'))
    <div class="attendance-flash">
        {{ session('status' )}}
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function updateCurrentTime() {
        const now = new Date();
        const h = now.getHours().toString().padStart(2, '0');
        const m = now.getMinutes().toString().padStart(2, '0');
        const elem = document.getElementById('current-time');
        if (elem) {
            elem.innerHTML =
                `${h}<span class="colon-dots"><span class="dot"></span><span class="dot"></span></span>${m}`;
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        setInterval(updateCurrentTime, 1000);
        updateCurrentTime();

        document.querySelectorAll('.js-confirm-clock-in').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('出勤を記録します。よろしいですか？')) {
                    e.preventDefault();
                }
            });
        });
        document.querySelectorAll('.js-confirm-clock-out').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('退勤を記録します。よろしいですか？')) {
                    e.preventDefault();
                }
            });
        });
        document.querySelectorAll('.js-confirm-break-start').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('休憩開始を記録します。よろしいですか？')) {
                    e.preventDefault();
                }
            });
        });
        document.querySelectorAll('.js-confirm-break-end').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('休憩終了を記録します。よろしいですか？')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endsection