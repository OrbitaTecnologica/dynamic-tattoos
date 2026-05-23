@extends('layouts.admin')

@section('title', 'Alertas Billing')
@section('page-title', 'Alertas e Incidentes Billing')
@section('page-subtitle', 'Visibilidad operativa de colas, webhooks y fallos')

@section('content')
    @livewire('admin.billing-alerts')
@endsection
