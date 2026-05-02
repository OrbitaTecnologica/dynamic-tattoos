@extends('layouts.admin')

@section('title', 'Mi Cuenta')
@section('page-title', 'Gestión de Cuenta')
@section('page-subtitle', 'Actualiza tu perfil y credenciales de acceso')

@section('content')
    @livewire('admin.account-settings')
@endsection
