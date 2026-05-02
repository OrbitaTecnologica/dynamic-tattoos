@extends('layouts.admin')

@section('title', 'Usuarios')
@section('page-title', 'Gestión de Usuarios')
@section('page-subtitle', 'Administra roles, planes y cuentas de usuario')

@section('content')
    @livewire('admin.user-list')
@endsection
