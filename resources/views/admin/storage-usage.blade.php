@extends('layouts.admin')

@section('title', 'Almacenamiento')
@section('page-title', 'Almacenamiento')
@section('page-subtitle', 'Uso de almacenamiento y archivos de los usuarios')

@section('content')
    @livewire('admin.storage-usage')
@endsection
