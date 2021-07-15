@extends('layouts.main')

@section('title', 'HDC Events')

@section('content')

<h1>Some title</h1>
<p>{{$nome}}</p>
@if($nome === 'Pedro')
<p>O nome é Pedro</p>
@else
<p>O nome não é Pedro</p>
@endif;

@endsection
