@extends('layouts.admin')

@section('title', 'Tokens API')
@section('page-title', 'Tokens / Sesiones API')
@section('page-subtitle', 'Sesiones activas y tokens de acceso de los usuarios')

@section('content')
    @livewire('admin.api-tokens')
@endsection
