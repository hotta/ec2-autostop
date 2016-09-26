@extends('layouts.default')

@section('title', 'Add New')

@section('content')
<h1>
<a href="{{ url('/') }}" class="pull-right fs12">Back</a>
Add New
</h1>
<form method="post" action="{{ url('/posts') }}">
  {{ csrf_field() }}
<p>
  <input type="text" name="title" placeholder="title" value={{ old('title') }}>
  @if ($errors->has('title'))
  <span class="error">{{ $errors->first('title') }}</span>
  @endif
</p>
<p>
  <textarea name="body" placeholder="body">{{ old('body') }}</textarea>
  @if ($errors->has('body'))
  <span class="error">{{ $errors->first('body') }}</span>
  @endif
</p>
<p>
  <input type="submit" value="Add New">
</p>
</form>
@endsection
