@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Métricas de impacto en tiempo real')

@section('content')
    @livewire('admin.metrics-dashboard')
@endsection
