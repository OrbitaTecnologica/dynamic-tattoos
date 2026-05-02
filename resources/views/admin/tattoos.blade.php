@extends('layouts.admin')

@section('title', 'Tatuajes')
@section('page-title', 'Gestión de Tatuajes Dinámicos')
@section('page-subtitle', 'Administra códigos, contenidos e historial de evolución')

@section('content')
    @livewire('admin.tattoo-list')
@endsection
