@extends('layouts.auth')

@section('title', '| Selección de app')
@section('heading', '')
@section('subtitle', '')

@php
    $hmsso = $hmsso ?? ['logo' => null, 'color' => '#556ee6', 'name' => 'HMSSO'];
    $primary = $hmsso['color'] ?: '#556ee6';
@endphp

@push('css')
<style>
    :root {
        --bs-primary: {{ $primary }};
    }
    /* Ocultar banner por defecto de auth — usamos header HMSSO custom */
    .bg-login { display: none; }
    .card-body.pt-5 { padding-top: 1.5rem !important; }
    .btn-primary,
    .btn-primary:active,
    .btn-primary:focus,
    .btn-primary:hover {
        background-color: {{ $primary }} !important;
        border-color: {{ $primary }} !important;
    }
    .btn-primary:hover { filter: brightness(0.92); }
    .btn-outline-secondary:hover { background-color: #f1f5f7; color: #495057; }

    .hmsso-header { text-align: center; margin-bottom: 1.25rem; }
    .hmsso-header img { max-height: 48px; max-width: 180px; object-fit: contain; }
    .hmsso-header p { color: #74788d; margin: .75rem 0 0; font-size: 14px; }

    .icon-type-app {
        border-radius: 35%;
        width: 80px;
        height: 80px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .a-app-icon {
        background-color: #f1f5f7;
        border: 2px solid transparent;
        transition: all .15s ease-in-out;
        text-decoration: none;
        color: inherit;
    }
    .a-app-icon:hover { background-color: #e4e9ed; color: inherit; }
    .a-app-icon.selected {
        border-color: {{ $primary }};
        box-shadow: 0 0 0 0.15rem rgba(0,0,0,0.05);
    }
    .a-app-icon .card-title { margin-bottom: 0; }
    .twitter-bs-wizard .twitter-bs-wizard-nav .nav-link .step-number {
        background-color: #ffffff !important;
        color: #74788d !important;
    }
</style>
@endpush

@section('card-body')
    <div class="hmsso-header">
        @if(!empty($hmsso['logo']))
            <img src="{{ $hmsso['logo'] }}" alt="{{ $hmsso['name'] }}">
        @else
            <h4 class="mb-0">{{ $hmsso['name'] }}</h4>
        @endif
        <p>Selecciona una aplicación para continuar</p>
    </div>

    @if ($error)
        <div class="alert alert-danger" role="alert">
            <i class="ri-error-warning-line align-middle me-2"></i>
            {{ $error }}
        </div>

        <div class="d-grid">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="ri-shut-down-line me-1"></i> Cerrar sesión
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

        <form method="POST" action="{{ route('select-app.select') }}" id="form-select-app">
            @csrf
            <input type="hidden" name="appCore" id="appCore" value="" required>

            <div class="row g-3">
                @foreach ($apps as $app)
                    @php $icon = $app['isotype_white'] ?? ($app['logo_white'] ?? ($app['logo'] ?? null)); @endphp
                    <div class="col-12 col-sm-6">
                        <a href="javascript:void(0)"
                           class="card a-app-icon h-100 mb-0 app-item"
                           data-core="{{ $app['idCore'] }}">
                            <div class="card-body row align-items-center g-0 py-2">
                                <div class="col-auto p-2">
                                    <div class="icon-type-app" style="background-color: {{ $app['color'] }};">
                                        @if($icon)
                                            <img src="{{ $icon }}" alt="{{ $app['name'] }}" style="max-width:70%;max-height:70%;object-fit:contain;">
                                        @else
                                            <span class="text-white font-size-20 fw-bold">{{ strtoupper(substr($app['name'], 0, 1)) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col ps-2">
                                    <h5 class="card-title font-size-15">{{ $app['name'] }}</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-grid">
                <button type="submit" class="btn btn-primary waves-effect waves-light" id="btn-continuar" disabled>
                    <i class="ri-arrow-right-line me-1"></i> Continuar
                </button>
            </div>
        </form>

        <div class="mt-2 d-grid">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary w-100">
                    <i class="ri-shut-down-line me-1"></i> Cerrar sesión
                </button>
            </form>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    (function(){
        const items = document.querySelectorAll('.app-item');
        const input = document.getElementById('appCore');
        const btn   = document.getElementById('btn-continuar');

        items.forEach(function(el){
            el.addEventListener('click', function(){
                items.forEach(function(x){ x.classList.remove('selected'); });
                el.classList.add('selected');
                if (input) input.value = el.dataset.core || '';
                if (btn)   btn.disabled = false;
            });
            el.addEventListener('dblclick', function(){
                if (input) input.value = el.dataset.core || '';
                document.getElementById('form-select-app').submit();
            });
        });
    })();
</script>
@endpush
