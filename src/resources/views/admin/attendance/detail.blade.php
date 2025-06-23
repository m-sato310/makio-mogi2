@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pages/admin-attendance-detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail-title-container">
    <div class="attendance-detail-title-row">
        <span class="title-bar"></span>
        <span class="attendance-detail-title-text">勤怠詳細</span>
    </div>
</div>

<div class="attendance-detail-form-container">
    <div class="attendance-detail-table">
        <div class="attendance-detail-row">
            <span class="attendance-detail-label">名前</span>
            <span class="attendance-detail-value">{{ $request->user->name ?? '-' }}</span>
        </div>

        <div class="attendance-detail-row">
            <span class="attendance-detail-label">日付</span>
            <div class="attendance-detail-inputs">
                <span class="attendance-detail-value">
                    {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}
                </span>
                <span class="attendance-detail-value">
                    {{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}
                </span>
            </div>
        </div>

        <div class="attendance-detail-row">
            <span class="attendance-detail-label">出勤・退勤</span>
            <div class="attendance-detail-inputs">
                <span class="attendance-detail-value">
                    {{ $request->new_clock_in
                        ? \Carbon\Carbon::parse($request->new_clock_in)->format('H:i')
                        : ($attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}
                </span>
                <span class="attendance-detail-separator">〜</span>
                <span class="attendance-detail-value">
                    {{ $request->new_clock_out
                        ? \Carbon\Carbon::parse($request->new_clock_out)->format('H:i')
                        : ($attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}
                </span>
            </div>
        </div>

        @foreach ($breaks as $i => $break)
        <div class="attendance-detail-row">
            <span class="attendance-detail-label">
                {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}
            </span>
            <div class="attendance-detail-inputs">
                <span class="attendance-detail-value">
                    {{ $break->new_break_start
                        ? \Carbon\Carbon::parse($break->new_break_start)->format('H:i')
                        : ($break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}
                </span>
                <span class="attendance-detail-separator">〜</span>
                <span class="attendance-detail-value">
                    {{ $break->new_break_end
                        ? \Carbon\Carbon::parse($break->new_break_end)->format('H:i')
                        : ($break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}
                </span>
            </div>
        </div>
        @endforeach

        <div class="attendance-detail-row note-row">
            <span class="attendance-detail-label">備考</span>
            <span class="attendance-detail-note-view">{{ $request->remarks ?? '' }}</span>
        </div>
    </div>
</div>

<div class="attendance-detail-footer-container">
    @if(!$isApproved)
        <form method="POST" action="{{ route('admin.correction_request.approve', ['attendance_correct_request' => $request->id]) }}">
            @csrf
            <button class="approve-btn" type="submit">承認</button>
        </form>
    @else
        <button class="approve-btn" type="button" disabled>承認済み</button>
    @endif
</div>
@endsection