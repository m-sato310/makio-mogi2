@extends('layouts.app')

@section('title', 'スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pages/staff-list.css') }}">
@endsection

@section('content')
<div class="staff-list-title-container">
    <div class="staff-list-title-row">
        <span class="title-bar"></span>
        <span class="staff-list-title-text">スタッフ一覧</span>
    </div>
</div>

<div class="staff-table-container">
    <table class="staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($staffs as $staff)
                <tr>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.staff',['id' => $staff->id]) }}">詳細</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection