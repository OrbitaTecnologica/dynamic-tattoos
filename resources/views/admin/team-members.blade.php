@extends('layouts.admin')

@section('title', 'Equipos')
@section('page-title', 'Miembros de Equipo')
@section('page-subtitle', 'Invitaciones y miembros de equipo de los usuarios')

@section('content')
    @livewire('admin.team-members')
@endsection
