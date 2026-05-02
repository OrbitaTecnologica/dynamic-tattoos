@extends('layouts.admin')

@section('title', 'Planes')
@section('page-title', 'Gestión de Precios y Planes')
@section('page-subtitle', 'Configura los planes de suscripción disponibles')

@section('content')
    @livewire('admin.pricing-plans')
@endsection
