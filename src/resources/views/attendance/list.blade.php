@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pages/list.css') }}">
@endsection

@section('content')
<div class="attendance-title-container">
    <div class="attendance-list-title">
        <span class="title-bar"></span>
        @if (!empty($isAdmin))
        {{ $targetDate->format('Y年n月j日') }}の勤怠
        @else
        勤怠一覧
        @endif
    </div>
</div>

{{-- 月切り替え用の白コンテナ --}}
<div class="month-switcher-container">
    @if (!empty($isAdmin))
    <div class="month-switcher-row">
        <a class="month-btn left" href="{{ route('admin.attendance.list', ['date' => $targetDate->copy()->subDay()->format('Y-m-d')]) }}">&#8592; 前日</a>
        <span class="month-label">
            <img
                class="month-icon day-icon"
                src="{{ asset('image/calendar.png') }}"
                alt="カレンダー"
                onclick="document.getElementById('date-picker').showPicker();"
            >
            {{ $targetDate->format('Y年n月j日') }}
            <form id="date-form" method="GET" action="{{ route('admin.attendance.list') }}" style="display:inline;">
                <input
                    class="date-picker"
                    type="date"
                    id="date-picker"
                    name="date"
                    value="{{ $targetDate->format('Y-m-d') }}"
                    onchange="document.getElementById('date-form').submit();"
                >
            </form>
        </span>
        <a class="month-btn right" href="{{ route('admin.attendance.list', ['date' => $targetDate->copy()->addDay()->format('Y-m-d')]) }}">翌日 &#8594;</a>
    </div>
    @else
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
    @endif
</div>

<div class="attendance-table-container">
    <table class="attendance-table">
        <thead>
            <tr>
                @if (!empty($isAdmin))
                <th>名前</th>
                @else
                <th>日付</th>
                @endif
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($isAdmin))
            @foreach ($attendanceSummary as $summary)
            <tr>
                <td>{{ $summary['user']->name }}</td>
                <td>{{ optional($summary['attendance'])->clock_in ? \Carbon\Carbon::parse($summary['attendance']->clock_in)->format('H:i') : '' }}</td>
                <td>{{ optional($summary['attendance'])->clock_out ? \Carbon\Carbon::parse($summary['attendance']->clock_out)->format('H:i') : '' }}</td>
                <td>{{ ($summary['hasBreak'] ?? false) ? (floor($summary['breakMinutes']/60).':'.str_pad($summary['breakMinutes']%60,2,'0',STR_PAD_LEFT)) : '' }}</td>
                <td>{{ !is_null($summary['workMinutes']) ? floor($summary['workMinutes']/60).':'.str_pad($summary['workMinutes']%60,2,'0',STR_PAD_LEFT) : '' }}</td>
                <td>
                    @if ($summary['attendance'])
                    <a href="{{ route('admin.attendance.detail', ['id' => $summary['attendance']->id]) }}">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
            @else
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
                    {{ ($summary['hasBreak'] ?? false) ? (floor($breakMinutes/60).':'.str_pad($breakMinutes%60,2,'0',STR_PAD_LEFT)) : '' }}
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
            @endif
        </tbody>
    </table>
</div>
@endsection