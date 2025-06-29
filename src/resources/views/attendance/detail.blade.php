@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pages/detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail-title-container">
    <div class="attendance-detail-title-row">
        <span class="title-bar"></span>
        <span class="attendance-detail-title-text">勤怠詳細</span>
    </div>
</div>

<div class="attendance-detail-form-container">
    @if ($isPending)
        <div class="attendance-detail-table">
            <div class="attendance-detail-row">
                <span class="attendance-detail-label">名前</span>
                <div class="attendance-detail-inputs">
                    <span class="attendance-detail-value">{{ $attendance->user->name }}</span>
                </div>
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
                        {{ $correctionRequest->new_clock_in ? \Carbon\Carbon::parse($correctionRequest->new_clock_in)->format('H:i') : '' }}
                    </span>
                    <span class="attendance-detail-separator">〜</span>
                    <span class="attendance-detail-value">
                        {{ $correctionRequest->new_clock_out ? \Carbon\Carbon::parse($correctionRequest->new_clock_out)->format('H:i') : '' }}
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
                            {{ $break->new_break_start ? \Carbon\Carbon::parse($break->new_break_start)->format('H:i') : '' }}
                        </span>
                        <span class="attendance-detail-separator">〜</span>
                        <span class="attendance-detail-value">
                            {{ $break->new_break_end ? \Carbon\Carbon::parse($break->new_break_end)->format('H:i') : '' }}
                        </span>
                    </div>
                </div>
            @endforeach

            <div class="attendance-detail-row note-row">
                <span class="attendance-detail-label">備考</span>
                <span class="attendance-detail-note-view">{{ $correctionRequest->remarks ?? '' }}</span>
            </div>
        </div>

    @else
        <form id="correction-form" method="POST" action="{{ !empty($isAdmin) ? route('admin.attendance.update', ['id' => $attendance->id]) : route('attendance.correction', ['id' => $attendance->id]) }}" novalidate>
            @csrf
            <div class="attendance-detail-table">
                <div class="attendance-detail-row">
                    <span class="attendance-detail-label">名前</span>
                    <div class="attendance-detail-inputs value">
                        <span class="attendance-detail-value">{{ $attendance->user->name }}</span>
                    </div>
                </div>

                <div class="attendance-detail-row">
                    <span class="attendance-detail-label">日付</span>
                    <div class="attendance-detail-inputs value">
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
                        <div>
                            <input class="attendance-detail-input" type="time" name="new_clock_in"
                                value="{{ old('new_clock_in',
                                    (isset($correctionRequest) && $correctionRequest->new_clock_in)
                                        ? \Carbon\Carbon::parse($correctionRequest->new_clock_in)->format('H:i')
                                        : ($attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '')
                                ) }}">
                            @error('new_clock_in')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <span class="attendance-detail-separator">〜</span>
                        <div>
                            <input class="attendance-detail-input" type="time" name="new_clock_out"
                                value="{{ old('new_clock_out',
                                    (isset($correctionRequest) && $correctionRequest->new_clock_out)
                                        ? \Carbon\Carbon::parse($correctionRequest->new_clock_out)->format('H:i')
                                        : ($attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '')
                                ) }}">
                            @error('new_clock_out')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                @foreach ($breaks as $i => $break)
                    <div class="attendance-detail-row">
                        <span class="attendance-detail-label">
                            {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}
                        </span>
                        <div class="attendance-detail-inputs">
                            <div>
                                <input class="attendance-detail-input" type="time" name="new_breaks[{{ $i }}][new_break_start]"
                                    value="{{ old("new_breaks.$i.new_break_start", $break->new_break_start ? \Carbon\Carbon::parse($break->new_break_start)->format('H:i') : ($break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '')) }}">
                                @error("new_breaks.$i.new_break_start")
                                    <div class="error">{{ $message }}</div>
                                @enderror
                            </div>
                            <span class="attendance-detail-separator">〜</span>
                            <div>
                                <input class="attendance-detail-input" type="time" name="new_breaks[{{ $i }}][new_break_end]"
                                    value="{{ old("new_breaks.$i.new_break_end", $break->new_break_end ? \Carbon\Carbon::parse($break->new_break_end)->format('H:i') : ($break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '')) }}">
                                @error("new_breaks.$i.new_break_end")
                                    <div class="error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="attendance-detail-row">
                    <span class="attendance-detail-label">
                        {{ $breaks->count() === 0 ? '休憩' : '休憩' . ($breaks->count() + 1) }}
                    </span>
                    <div class="attendance-detail-inputs">
                        <div>
                            <input class="attendance-detail-input" type="time" name="new_breaks[{{ $breaks->count() }}][new_break_start]"
                                value="{{ old('new_breaks.' . $breaks->count() . '.new_break_start') }}">
                            @error("new_breaks." . $breaks->count() . ".new_break_start")
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <span class="attendance-detail-separator">〜</span>
                        <div>
                            <input class="attendance-detail-input" type="time" name="new_breaks[{{ $breaks->count() }}][new_break_end]"
                                value="{{ old('new_breaks.' . $breaks->count() . '.new_break_end') }}">
                            @error("new_breaks." . $breaks->count() . ".new_break_end")
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="attendance-detail-row note-row">
                    <span class="attendance-detail-label">備考</span>
                    <div class="attendance-detail-inputs">
                        <textarea class="attendance-detail-note" name="remarks">{{ old('remarks', $correctionRequest->remarks ?? '') }}</textarea>
                        @error('remarks')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

        </form>
    @endif
</div>

<div class="attendance-detail-footer-container">
    @if ($isPending)
        <div class="pending-message">*承認待ちのため修正はできません。</div>
    @else
        <button class="submit-btn" type="submit" form="correction-form">修正</button>
    @endif
</div>
@endsection