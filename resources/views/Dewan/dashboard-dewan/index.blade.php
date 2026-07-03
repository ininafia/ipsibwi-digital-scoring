@extends('Dewan.Layout.app')

@section('title', 'Dashboard Dewan')

@section('sidebar')
    @include('Dewan.Layout.sidebar')
@endsection

@section('content')
    @include('Dewan.dashboard-dewan.content')
@endsection