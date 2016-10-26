@extends('layouts.default')

@section('title', 'サーバー起動／停止制御')

@section('content')
<h1>サーバー起動／停止制御</h1>
@if ($stub_mode)
  <button class="btn btn-danger" type="button">{{ $stub_mode}}</button>
@endif
<h5 class='current_timestamp'>{{ $timestamp}} 現在</h5>
<table class='table table-striped custom-va'>
<thead>
  <tr>
    <th class="server_name" scope="row">サーバー名</th>
    <th class="server_role">役割</th>
    <th class="server_status" colspan="2">稼働状況</th>
    <th class="server_stop"   colspan="2">本日の停止予定</th>
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
    <td>{{ $s['nickname'] }}</td>
{{-- 役割 --}}
    <td>{{ $s['description'] }}</td>
{{-- 稼働状況 --}}
    <td>{{ $s['state_j']}}</td>
{{-- 起動／停止ボタン --}}
  @if ($s['state'] == 'running')
    <td>
      <form method="post" class="form-group" style="display:inline" 
        id="stop_{{ $s['instance_id'] }}" 
        action="/manual/stop/{{ $s['instance_id'] }}/{{ $s['nickname'] }}">
        {{ csrf_field() }}
        <button data-id="{{ $s['instance_id'] }}" 
          class="btn btn-danger" onclick="Stop_Run(this)">停止</button>
      </form>
    </td>
  @elseif ($s['state'] == 'stopped')
    <td>
      <form method="post" class="form-group" style="display:inline" 
        id="start_{{ $s['instance_id'] }}" 
        action="/manual/start/{{ $s['instance_id'] }}/{{ $s['nickname'] }}">
        {{ csrf_field() }}
        <button data-id="{{ $s['instance_id'] }}" 
          class="btn btn-success" onclick="Start(this)">起動</a>
      </form>
    </td>
  @else
    <td></td>
  @endif

{{-- 本日の停止予定 --}}
  @if ($s['stop_at'] == '')
    <td>&nbsp;</td>
  @elseif ($s['stop_at'] == 'manual')
    <td>手動モード</td>
  @else
    <td>{{ $s['stop_at'] }}</td>
  @endif

{{-- ボタン --}}
  @if (preg_match('/^\d+:\d+(:\d+)?$/', $s['stop_at']))
    <td>
      <form method="post" class="form-group" style="display:inline" 
        id="manual_{{ $s['instance_id'] }}" 
        action="/manual/to_manual/{{ $s['instance_id'] }}/{{ $s['nickname'] }}">
        {{ csrf_field() }}
        <button data-id="{{ $s['instance_id'] }}" 
          class="btn btn-warning" onclick="toManual(this)">手動モードへ</a>
      </form>
    </td>
  @else
    <td>&nbsp;</td>
  @endif
  </tr>
@endforeach
</tbody>
</table>
<p>{{ env('GUI_REMARKS', '') }}</p>
@endsection
