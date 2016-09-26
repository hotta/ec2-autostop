@extends('layouts.default')

@section('title', 'サーバー起動／停止制御')

@section('content')
<h1>サーバー起動／停止制御</h1>
<table class='table table-striped custom-va'>
<thead>
  <tr>
    <th>サーバー名</th>
    <th>役割</th>
    <th colspan="2">稼働状況</th>
    <th colspan="2">本日の停止予定</th>
  </tr>
</thead>
<tbody>
@foreach ($servers as $s)
  @if ($s['state'] == 'running')
  <tr class="success">
  @elseif ($s['state'] == 'stopped')
  <tr class="danger">
  @else
  <tr class="warning">
  @endif
{{-- サーバー名 --}}
    <td scope="row">{{ $s['nickname'] }}</td>
{{-- 役割 --}}
    <td>{{ $s['description'] }}</td>
{{-- 稼働状況 --}}
    <td>{{ $s['state_j']}}</td>
{{-- 起動／停止ボタン --}}
  @if ($s['state'] == 'running')
    <td>
      <form method="post" id="form_{{ $s['instance_id'] }}" class="form-group"
        style="display:inline" action="/manual/stop/{{ $s['instance_id'] }}">
        {{ csrf_field() }}
        <a href="#" data-id="{{ $s['instance_id'] }}" onclick="Stop_Run(this)"
          class="btn btn-danger">停止</a>
      </form>
    </td>
  @elseif ($s['state'] == 'stopped')
    <td>
      <form method="post" id="form_{{ $s['instance_id'] }}" class="form-group"
        style="display:inline" action="/manual/start/{{ $s['instance_id'] }}">
        {{ csrf_field() }}
        <a href="#" data-id="{{ $s['instance_id'] }}" onclick="Start(this)"
          class="btn btn-success">起動</a>
      </form>
    </td>
  @else
    <td></td>
  @endif

{{-- 本日の停止予定 --}}
  @if ($s['stop_at'] == '')
    <td></td>
  @elseif ($s['stop_at'] == 'manual')
    <td>手動モード</td>
  @else
    <td>{{ $s['stop_at'] }}</td>
  @endif

{{-- ボタン --}}
  @if (preg_match('/^\d+:\d+$/', $s['stop_at']))
    <td>
      <form method="post" id="form_{{ $s['instance_id'] }}" class="form-group"
        style="display:inline" action="/manual/{{ $s['instance_id'] }}">
        {{ csrf_field() }}
        <a href="#" data-id="{{ $s['instance_id'] }}" onclick="toManual(this)"
          class="btn btn-warning">手動モードへ</a>
      </form>
    </td>
  @else
    <td></td>
  @endif
  </tr>
@endforeach
</tbody>
</table>
<p>{{ env('GUI_REMARKS', '') }}</p>
@endsection
