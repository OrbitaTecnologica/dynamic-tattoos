@extends('layouts.admin')

@section('title', 'Retiros')
@section('page-title', 'Retiros de Comisiones')
@section('page-subtitle', 'Solicitudes de cash-out del programa de referidos')

@section('content')
    @livewire('admin.commission-withdrawals')
@endsection
