@extends('layouts.auth')

@section('title', '| Error')
@section('heading', 'No se pudo iniciar sesion')
@section('subtitle', 'Hubo un problema con el servidor de autenticacion')

@section('card-body')
    @if (!empty($errors))
        <div class="alert alert-danger">
            @foreach ((array) $errors as $key => $err)
                <div>{{ is_array($err) ? implode(', ', $err) : $err }}</div>
            @endforeach
        </div>
    @endif

    <div class="d-grid">
        <a href="{{ route('login') }}" class="btn btn-primary waves-effect waves-light">
            <i class="ri-refresh-line me-1"></i> Reintentar
        </a>
    </div>
@endsection
