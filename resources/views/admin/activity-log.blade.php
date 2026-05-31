@extends('layouts.admin')

@section('title', 'Actividad')
@section('page-title', 'Registro de Actividad')
@section('page-subtitle', 'Auditoría de acciones del sistema')

@section('content')
    @livewire('admin.activity-log')
@endsection
