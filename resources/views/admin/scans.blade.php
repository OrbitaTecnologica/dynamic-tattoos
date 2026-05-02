@extends('layouts.admin')

@section('title', 'Monitor de Escaneos')
@section('page-title', 'Monitor de Escaneos')
@section('page-subtitle', 'Feed en vivo — actualización automática cada 5 s')

@section('content')
    @livewire('admin.scan-feed')
@endsection
