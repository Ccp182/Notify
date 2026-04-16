@extends('layouts.generalAuth')
@section('css')
@endsection
@section('content')
<div class="authentication-page-content p-4 d-flex align-items-center min-vh-100">
    <div class="w-100">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div>
                    <div class="text-center">
                        <div>
                            <a href="index.html" class="">
                                <img src="{{ env('RES_HMMOVIL') }}logo-color.png" alt="" height="50" class="auth-logo logo-dark mx-auto">
                                <img src="{{ env('RES_HMMOVIL') }}logo-color.png" alt="" height="50" class="auth-logo logo-light mx-auto">
                            </a>
                        </div>

                        <h4 class="font-size-18 mt-4">Bienvenido!</h4>
                        <p class="text-muted">Inicia sesión en Hunter Notify.</p>
                    </div>

                    <div class="p-2 mt-5">
                        <form id="loginfrm" method="POST" class="form-horizontal" action="{{ route('loginex') }}">
                            @csrf
                            @include("auth.errors")  
                            <div class="mb-3 auth-form-group-custom mb-4">
                                <i class="ri-user-2-line auti-custom-input-icon"></i>
                                <label for="username">{{ __('Usuario') }}</label>
                                <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" placeholder="Ingresa usuario" required autocomplete="username" autofocus value="">
                               <!-- @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror-->
                            </div>
                           <input type='hidden' name="app" id="app" value='59'/>
                            <div class="mb-3 auth-form-group-custom mb-4">
                                <i class="ri-lock-2-line auti-custom-input-icon"></i>
                                <label for="password">{{ __('Password') }}</label>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Ingresa password" required autocomplete="current-password" value="">
                                <i class="ri-eye-line auti-custom-input-icon toggle-password" onclick="togglePasswordVisibility()" style="cursor:pointer; position:absolute; right:10px!important; top: 30px!important; left:auto; font-size:20px; color:gray;"></i>
                               <!-- @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror   -->
                            </div>
                            <div class="mb-3 mb-4">
                                <div class="row mb-3">
                                    <!--<label class="col-sm-4 col-form-label" for="aplicacion">{{ __('Aplicacion') }}</label>-->
                                    <div class="col-sm-12">
                                        <select class="form-select @error('appNotify') is-invalid @enderror" name="appNotify" id="appNotify" style="font-size: 1.2rem;">
                                            <option value="1" selected="">HMMóvil</option>
                                            <option value="25">AndorLink</option>
                                            <option value="3">Maresa Satelital</option>
                                            <option value="2">Hunter GPS</option>
                                            <option value="24">Alivo Connect+</option>
                                            <option value="27">INKA Motors</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-check">
                                        <!--<input type="checkbox" class="form-check-input" id="remember" name="remember">-->
                                        <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me" value="1" {{ old('remember_me') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember_me">Recuérdame</label>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3 auth-form-group-custom mb-4 text-right">
                                        <div id="opt_country" data-input-name="country2" data-selected-country="{{ $countryCode }}"></div>
                                        <input type="hidden" name="country" id="country" value="{{ $countryCode }}">
                                    </div>
                                </div>
                            </div>
    
                            

                            <div class="mt-4 text-center">
                                <button class="btn btn-primary w-md waves-effect waves-light" type="submit"><i id="loading" class="fas fa-spinner fa-spin" style="display: none;"></i></i> Iniciar Sesión</button>
                            </div>

                            <div class="mt-4 text-center">
                                <a href="https://am.24hm.net/account/9318b543cb49c3d9daab128667a6c55b" class="text-muted"><i class="mdi mdi-lock me-1"></i> ¿Necesita Ayuda?</a>
                            </div>
                        </form>
                    </div>

                    <div class="mt-5 text-center">
                        <p>© <script>document.write(new Date().getFullYear())</script> Hunter Carsegsa. </p>
                        <script>
                            function togglePasswordVisibility() {
                                var passwordInput = document.getElementById('password');
                                var toggleIcon = document.querySelector('.toggle-password');
                                if (passwordInput.type === 'password') {
                                    passwordInput.type = 'text';
                                    toggleIcon.classList.remove('ri-eye-line');
                                    toggleIcon.classList.add('ri-eye-off-line');
                                } else {
                                    passwordInput.type = 'password';
                                    toggleIcon.classList.remove('ri-eye-off-line');
                                    toggleIcon.classList.add('ri-eye-line');
                                }
                            }
                        </script> 
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection     