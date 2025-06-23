@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pages/request_list.css') }}">
@endsection

@section('content')
<div class="request-list-title-container">
    <div class="request-list-title-row">
        <span class="title-bar"></span>
        <span class="request-list-title-text">申請一覧</span>
    </div>
</div>

<div class="request-list-tab-container">
    @php
        $tab = request()->get('tab', 'pending');
    @endphp
    <a class="tab-btn{{ $tab === 'pending' ? ' active' : '' }}" href="{{ route('correction_request.list', ['tab' => 'pending']) }}">承認待ち</a>
    <a class="tab-btn{{ $tab === 'approved' ? ' active' : '' }}" href="{{ route('correction_request.list', ['tab' => 'approved']) }}">承認済み</a>
</div>
<hr class="tab-underline">

<div class="request-list-table-container">
    <table class="request-list-table">
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
            @php
                $list = $tab === 'pending' ? $pendingList : $approvedList;
            @endphp
            @forelse ($list as $req)
            <tr>
                <td>{{ $req->approval_status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                <td>{{ $req->user->name ?? '' }}</td>
                <td class="date-cell">
                    {{ $req->attendance->work_date ? \Carbon\Carbon::parse($req->attendance->work_date)->format('Y/m/d') : '' }}
                </td>
                <td>
                    {{ \Illuminate\Support\Str::limit($req->remarks ?? '', 20, '…') }}
                </td>
                <td class="date-cell">{{ $req->created_at ? \Carbon\Carbon::parse($req->created_at)->format('Y/m/d') : '' }}</td>
                <td>
                    <a class="detail-link" href="{{ route('attendance.detail', ['id' => $req->attendance->id]) }}">詳細</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">データがありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection