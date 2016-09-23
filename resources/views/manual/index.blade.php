@extends('layouts.default')

@section('title', 'サーバー起動／停止制御')

@section('content')
<h1>サーバー起動／停止制御</h1>
<table class='table table-striped'>
<thead>
  <tr>
    <th>サーバー名</th>
    <th>役割</th>
    <th>稼働状況</th>
    <th>本日の停止予定</th>
  </tr>
</thead>
<tbody>
@foreach ($servers as $s)
  <tr>
    <td>{{ $s['nickname'] }}</td>
    <td>{{ $s['description'] }}</td>
@if ($s['state'] == 'running')
    <td class="warning">
      {{ $s['state_j']}}
      <form method="post" id="form_{{ $s['instance_id'] }}" class="form-group"
        style="display:inline" action="/manual/stop/{{ $s['instance_id'] }}">
        {{ csrf_field() }}
        <a href="#" data-id="{{ $s['instance_id'] }}" onclick="Stop_Run(this)"
          class="fs12">[停止]</a>
      </form>
@elseif ($s['state'] == 'stopped')
    <td class="danger">
      {{ $s['state_j'] }}
      <form method="post" id="form_{{ $s['instance_id'] }}" class="form-group"
        style="display:inline" action="/manual/start/{{ $s['instance_id'] }}">
        {{ csrf_field() }}
        <a href="#" data-id="{{ $s['instance_id'] }}" onclick="Start(this)"
          class="fs12">[起動]</a>
      </form>
@else
    <td class="info">
      <span class='state_pending'>{{ $s['state_j'] }}</span>
@endif
    </td>
    <td>
@if ($s['stop_at'] == 'already')
      <span class='stop_manual'></span>
@elseif ($s['stop_at'] == 'manual')
      <span class='stop_manual'>手動モード</span>
@else
      <span class='stop_manual'>{{ $s['stop_at'] }}</span>
      <form method="post" id="form_{{ $s['instance_id'] }}" class="form-group"
        style="display:inline" action="/manual/{{ $s['instance_id'] }}">
        {{ csrf_field() }}
        <a href="#" data-id="{{ $s['instance_id'] }}" onclick="toManual(this)"
          class="fs12">[手動モードへ]</a>
      </form>
@endif
    </td>
  </tr>
@endforeach
@endsection
