@extends('layouts.admin')

@section('title', 'Suscripciones')
@section('page-title', 'Suscripciones')
@section('page-subtitle', 'Estado de las suscripciones de Stripe')

@section('content')
    @livewire('admin.subscriptions')
@endsection
