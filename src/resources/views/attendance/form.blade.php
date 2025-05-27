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

    <div class="attendance-clock" id="current-time"></div>

    <div class="attendance-actions">
        @if ($status === '勤務外')
        <form action="{{ route('attendance.start') }}" method="POST">
            @csrf
            <button class="btn btn-primary" type="submit">出勤</button>
        </form>
        @elseif ($status === '出勤中')
        <form action="{{ route('attendance.finish') }}" method="POST">
            @csrf
            <button class="btn btn-primary" type="submit">退勤</button>
        </form>
        <form action="{{ route('attendance.break_start') }}" method="POST">
            @csrf
            <button class="btn btn-secondary" type="submit">休憩入</button>
        </form>
        @elseif ($status === '休憩中')
        <form action="{{ route('attendance.break_end') }}" method="POST">
            @csrf
            <button class="btn btn-secondary" type="submit">休憩戻</button>
        </form>
        @elseif ($status === '退勤済')
        <div class="attendance-message">お疲れ様でした。</div>
        @endif
    </div>

    @if ($errors->any())
    <div class="attendance-errors">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- @if (session('status'))
    <div class="attendance-flash">
        {{ session('status' )}}
    </div>
    @endif -->
</div>
@endsection

@section('scripts')
<script>
    function updateCurrentTime() {
        const now = new Date();
        const h = now.getHours().toString().padStart(2, '0');
        const m = now.getMinutes().toString().padStart(2, '0');
        // document.getElementById('current-time').textContent = `${h}:${m}`;
        document.getElementById('current-time').innerHTML =
            `${h}<span class="colon-dots"><span class="dot"></span><span class="dot"></span></span>${m}`;
    }
    setInterval(updateCurrentTime, 1000);
    updateCurrentTime();
</script>
@endsection