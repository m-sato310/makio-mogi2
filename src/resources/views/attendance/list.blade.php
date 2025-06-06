@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pages/list.css') }}">
@endsection

@section('content')
<div class="attendance-title-container">
    <div class="attendance-list-title">
        <span class="title-bar"></span>
        勤怠一覧
    </div>
</div>

<!-- 月切替行（左端「←前月」、中央年月、右端「翌月→」） -->
{{-- 月切り替え用の白コンテナ --}}
<div class="month-switcher-container">
    <div class="month-switcher-row">
        <a class="month-btn left"
            href="{{ route('attendance.list', ['year' => $month == 1 ? $year - 1 : $year, 'month' => $month == 1 ? 12 : $month - 1]) }}">
            &#8592; 前月
        </a>
        <span class="month-label">
            <img class="month-icon" src="{{ asset('image/calendar.png') }}" alt="カレンダー">
            {{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}
        </span>
        <a class="month-btn right"
            href="{{ route('attendance.list', ['year' => $month == 12 ? $year + 1 : $year, 'month' => $month == 12 ? 1 : $month + 1]) }}">
            翌月 &#8594;
        </a>
    </div>
</div>

<!-- 勤怠テーブル -->
<div class="attendance-table-container">
    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($daysInMonth as $date)
            @php
            $workDate = $date->format('Y-m-d');
            $summary = $attendanceSummary[$workDate] ?? null;
            $attendance = $summary['attendance'] ?? null;
            $breakMinutes = $summary['breakMinutes'] ?? 0;
            $workMinutes = $summary['workMinutes'];
            @endphp
            <tr>
                <td>
                    {{ $date->format('m/d') }}({{ $date->locale('ja')->isoFormat('dd') }})
                </td>
                <td>
                    {{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                </td>
                <td>
                    {{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                </td>
                <td>
                    {{ $breakMinutes ? floor($breakMinutes/60).':'.str_pad($breakMinutes%60,2,'0',STR_PAD_LEFT) : '' }}
                </td>
                <td>
                    {{ $workMinutes !== null ? floor($workMinutes/60).':'.str_pad($workMinutes%60,2,'0',STR_PAD_LEFT) : '' }}
                </td>
                <td>
                    @if ($attendance)
                    <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection