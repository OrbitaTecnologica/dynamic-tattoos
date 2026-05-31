@extends('layouts.admin')

@section('title', 'Storage Packs')
@section('page-title', 'Packs de Almacenamiento')
@section('page-subtitle', 'Amplía la cuota de almacenamiento de los usuarios')

@section('content')
    @livewire('admin.storage-packs')
@endsection
