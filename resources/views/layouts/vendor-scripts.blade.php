    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

    {{-- Single Logout: si cualquier llamada Ajax/fetch responde 401, el
         middleware del cliente ya invalido la sesion local. Redirigimos
         al login para que el navegador re-arranque el flujo OAuth. --}}
    <script>
        (function () {
            var loginUrl = @json(route('login'));
            function bounce(xhr) {
                try {
                    var data = xhr && xhr.responseJSON ? xhr.responseJSON : null;
                    if (data && data.login_url) { window.location.href = data.login_url; return; }
                } catch (e) {}
                window.location.href = loginUrl;
            }
            if (window.jQuery) {
                jQuery(document).ajaxError(function (event, xhr) {
                    if (xhr && xhr.status === 401) bounce(xhr);
                });
            }
            if (window.fetch) {
                var orig = window.fetch;
                window.fetch = function () {
                    return orig.apply(this, arguments).then(function (resp) {
                        if (resp && resp.status === 401) {
                            resp.clone().json().then(function (data) {
                                window.location.href = (data && data.login_url) ? data.login_url : loginUrl;
                            }).catch(function () { window.location.href = loginUrl; });
                        }
                        return resp;
                    });
                };
            }
        })();
    </script>

    @stack('scripts')
