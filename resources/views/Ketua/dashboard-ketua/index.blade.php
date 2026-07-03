@extends('Ketua.Layout.app')

@section('title', 'Dashboard Ketua Pertandingan')

@section('sidebar')
    @include('Ketua.Layout.sidebar')
@endsection

@section('content')
    @include('Ketua.dashboard-ketua.content')
@endsection