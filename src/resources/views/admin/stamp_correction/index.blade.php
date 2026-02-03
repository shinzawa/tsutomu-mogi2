@extends('layouts.default')

<!-- タイトル -->
@section('title','申請一覧')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/authentication.css')  }}">
<link rel="stylesheet" href="{{ asset('/css/request.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')
<div class="main-box">
    <div class="title-box">
        <div class="title-bar"></div>
        <h1>申請一覧</h1>
    </div>
    {{-- タブ --}}
    <ul class="nav nav-tabs" id="correctionTabs">
        <li class="nav-item">
            <a class="nav-link active" data-target="#pending">承認待ち</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-target="#approved">承認済み</a>
        </li>
    </ul>
    <hr color="#000000" size="1px" width="100%">
    <div id="pending" class="tab-content mt-3">
        <div class="request-table">
            <table>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
                @forelse ($pending as $req)
                <tr>
                    <td>承認待ち</td>
                    <td>{{ $users[$req->attendance->user_id-1]->name }} </td>
                    <td>{{ \Carbon\Carbon::parse($req->attendance->work_date)->format('Y/n/j') }} </td>
                    <td>{{ $req->reason }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->attendance->created_at)->format('Y/n/j') }}</td>
                    <td>
                        <a href="/admin/stamp_correction_request/approve/{{ $req->id }}" class="a-link">
                            詳細
                        </a>
                    </td>
                </tr>
                @empty
                <p>承認待ちの申請はありません。</p>
                @endforelse
            </table>
        </div>
    </div>
    <div id="approved" class="tab-content mt-3" style="display:none;">
        <div class="request-table">
            <table>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
                @forelse ($approved as $req)
                <tr>
                    <td>承認済み</td>
                    <td>{{ $users[$req->attendance->user_id-1]->name }} </td>
                    <td>{{ \Carbon\Carbon::parse($req->attendance->work_date)->format('Y/n/j') }} </td>
                    <td>{{ $req->reason }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->attendance->created_at)->format('Y/n/j') }}</td>
                    <td>
                        <a href="/admin/stamp_correction_request/approve/{{ $req->id }}" class="a-link">
                            詳細
                        </a>
                    </td>
                </tr>
                @empty
                <p>承認済みの申請はありません。</p>
                @endforelse
            </table>
        </div>
    </div>
</div>
<script src="{{ asset('/js/index.js') }}"></script>
@endsection