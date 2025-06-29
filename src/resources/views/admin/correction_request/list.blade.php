@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pages/correction-request-list.css') }}">
@endsection

@section('content')
<div class="correction-request-title-container">
    <div class="correction-request-title-row">
        <span class="title-bar"></span>
        <span class="correction-request-title-text">申請一覧</span>
    </div>
</div>

<div class="correction-request-tab-container">
    <a class="correction-request-tab{{ $status === 'pending' ? ' active' : '' }}" href="{{ route('correction_request.list', ['status' => 'pending']) }}">
        承認待ち
    </a>
    <a class="correction-request-tab{{ $status === 'approved' ? ' active' : '' }}" href="{{ route('correction_request.list', ['status' => 'approved']) }}">
        承認済み
    </a>
</div>

<hr class="correction-request-tab-divider">

<div class="correction-request-table-container">
    <table class="correction-request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requests as $request)
                <tr>
                    <td>
                        @if($request->approval_status === 'pending')
                            承認待ち
                        @elseif($request->approval_status === 'approved')
                            承認済み
                        @else
                            {{ $request->approval_status }}
                        @endif
                    </td>
                    <td>{{ $request->user->name ?? '-' }}</td>
                    <td class="no-letter-spacing">
                        {{ $request->attendance->work_date ? \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') : '-' }}
                    </td>
                    <td>
                        {{ \Illuminate\Support\Str::limit($request->remarks ?? '-', 20, '…') }}
                    </td>
                    <td class="no-letter-spacing">{{ $request->created_at ? \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') : '-' }}</td>
                    <td>
                        <a href="{{ route('admin.correction_request.approve_form', ['attendance_correct_request' => $request->id]) }}">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">該当する申請はありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection