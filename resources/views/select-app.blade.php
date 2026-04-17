@extends('layouts.auth')

@section('title', 'Seleccion de app')
@section('heading', 'Bienvenido, {{ session("FName", session("User")) }}')
@section('subtitle', 'Selecciona la app para enviar notificaciones')

@section('card-body')
    @if ($error)
        <div class="alert alert-danger" role="alert">
            <i class="ri-error-warning-line align-middle me-2"></i>
            {{ $error }}
        </div>

        <div class="d-grid">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="ri-shut-down-line me-1"></i> Cerrar sesion
                </button>
            </form>
        </div>
    @else
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        <p class="text-muted text-center mb-4">
            Tienes {{ count($apps) }} {{ count($apps) === 1 ? 'app disponible' : 'apps disponibles' }}.
        </p>

        <form method="POST" action="{{ route('select-app.select') }}">
            @csrf

            <div class="row g-3">
                @foreach ($apps as $app)
                    <div class="col-md-6">
                        <label class="card app-card h-100 mb-0 cursor-pointer" style="cursor: pointer; border: 2px solid transparent;">
                            <input type="radio" name="appCore" value="{{ $app['idCore'] }}" class="d-none" required>
                            <div class="card-body text-center">
                                <div class="avatar-md mx-auto mb-3">
                                    <span class="avatar-title rounded-circle font-size-24"
                                          style="background-color: {{ $app['color'] }}; color: #fff;">
                                        {{ strtoupper(substr($app['name'], 0, 1)) }}
                                    </span>
                                </div>
                                <h5 class="font-size-15 mb-1">{{ $app['name'] }}</h5>
                                <p class="text-muted mb-0 font-size-12">App local {{ $app['idLocal'] }}</p>
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-grid">
                <button type="submit" class="btn btn-primary waves-effect waves-light">
                    <i class="ri-arrow-right-line me-1"></i> Continuar
                </button>
            </div>
        </form>

        <div class="mt-3 text-center">
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-link btn-sm text-muted">
                    <i class="ri-shut-down-line me-1"></i> Cerrar sesion
                </button>
            </form>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    // Marcar visualmente el card seleccionado
    document.querySelectorAll('.app-card input[type="radio"]').forEach(function(input) {
        input.addEventListener('change', function() {
            document.querySelectorAll('.app-card').forEach(function(c) {
                c.style.borderColor = 'transparent';
                c.style.boxShadow = '';
            });
            const card = input.closest('.app-card');
            card.style.borderColor = 'var(--bs-primary, #556ee6)';
            card.style.boxShadow = '0 0 0 0.15rem rgba(var(--bs-primary-rgb, 85,110,230), 0.25)';
        });
    });
</script>
@endpush
