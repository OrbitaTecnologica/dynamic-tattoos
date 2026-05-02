@extends('layouts.admin')

@section('title', 'Generador de QRs')
@section('page-title', 'Generador de QR Masivo')
@section('page-subtitle', 'Crea y asigna nuevos códigos base con corrección de errores H')

@section('content')
    @livewire('admin.qr-generator')
@endsection
