{{--
    Componente SSO App Launcher (waffle estilo Google).

    Lista las apps a las que el usuario tiene acceso, SEGREGADAS en tres
    secciones (mismo criterio que HMSrvAuth/home: loadUserAppsIntoSession):

      - Aplicaciones Web      (HInt=0, HMov=0)
      - Aplicaciones Internas (HInt=1, HMov=0)
      - Aplicaciones Moviles  (HInt=0, HMov=1)

    Lee de session('SsoApps'). Ver skill hm-sso-ecosystem.

    Schema esperado por item:
      NApp, Url, Img, CPri, HInt, HMov, State (ya filtrado a 'A' por el endpoint).
      ImgUrl  - URL absoluta del logo (variante white) - construida en el server.
      ImgShop - URL absoluta del logo (variante color-shop) - usada por el waffle.
--}}
@php
    use Illuminate\Support\Facades\Session;
    $launcherApps = Session::get('SsoApps', []);

    $launcherWeb = $launcherInternas = $launcherMoviles = [];
    foreach ($launcherApps as $launcherA) {
        $hint = (int)($launcherA['HInt'] ?? 0);
        $hmov = (int)($launcherA['HMov'] ?? 0);
        if ($hint === 0 && $hmov === 0) {
            $launcherWeb[] = $launcherA;
        } elseif ($hint === 1 && $hmov === 0) {
            $launcherInternas[] = $launcherA;
        } elseif ($hint === 0 && $hmov === 1) {
            $launcherMoviles[] = $launcherA;
        }
    }
    $launcherTotal = count($launcherWeb) + count($launcherInternas) + count($launcherMoviles);

    // Renderizador inline de un tile (evita repetir markup 3 veces).
    // Prioridad de imagen: ImgShop (full URL, variante color-shop, ya trae su
    // propio diseno con fondo) > ImgUrl > Img raw > inicial del nombre.
    // Cuando hay imagen NO usamos background-color: el icono color-shop ya
    // viene con su identidad visual completa. El CPri solo se usa de fondo
    // como fallback cuando no hay imagen.
    $renderTile = function (array $a): string {
        $name  = $a['NApp']    ?? '';
        $url   = $a['Url']     ?? '#';
        $img   = $a['ImgShop'] ?? $a['ImgUrl'] ?? $a['Img'] ?? '';
        $color = $a['CPri']    ?? '#556ee6';
        if ($img) {
            return
                '<a href="'.e($url).'" class="sso-app-tile" target="_blank" rel="noopener">'.
                    '<div class="sso-app-tile-icon sso-app-tile-icon--img">'.
                        '<img src="'.e($img).'" alt="'.e($name).'">'.
                    '</div>'.
                    '<span class="sso-app-tile-name">'.e($name).'</span>'.
                '</a>';
        }
        return
            '<a href="'.e($url).'" class="sso-app-tile" target="_blank" rel="noopener">'.
                '<div class="sso-app-tile-icon" style="background-color: '.e($color).';">'.
                    '<span>'.e(mb_strtoupper(mb_substr($name, 0, 1))).'</span>'.
                '</div>'.
                '<span class="sso-app-tile-name">'.e($name).'</span>'.
            '</a>';
    };
@endphp

@once
<style>
    .sso-app-launcher-menu{width:340px;max-height:480px;overflow-y:auto;}
    .sso-app-section-title{font-size:11px;letter-spacing:.5px;color:#74788d;font-weight:600;text-transform:uppercase;margin:.25rem 0 .35rem .35rem;}
    .sso-app-section + .sso-app-section{margin-top:.85rem;padding-top:.85rem;border-top:1px solid rgba(0,0,0,.06);}
    .sso-app-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.25rem;}
    .sso-app-tile{display:flex;flex-direction:column;align-items:center;padding:.7rem .25rem;text-align:center;color:inherit;text-decoration:none;border-radius:8px;transition:background-color .15s ease;}
    .sso-app-tile:hover{background-color:rgba(0,0,0,.04);color:inherit;text-decoration:none;}
    .sso-app-tile-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:.4rem;color:#fff;overflow:hidden;}
    .sso-app-tile-icon--img{background:transparent;border-radius:0;}
    .sso-app-tile-icon--img img{width:100%;height:100%;object-fit:contain;}
    .sso-app-tile-icon span{font-size:20px;font-weight:600;}
    .sso-app-tile-name{font-size:11px;line-height:1.2;color:#495057;word-break:break-word;}
    .sso-app-launcher-empty{padding:1.5rem .5rem;text-align:center;color:#9ea4ab;font-size:.85rem;}
</style>
@endonce

<div class="dropdown d-none d-lg-inline-block ms-1 sso-app-launcher">
    <button type="button" class="btn header-item noti-icon waves-effect"
        data-bs-toggle="dropdown" data-bs-auto-close="outside"
        aria-haspopup="true" aria-expanded="false"
        title="Mis aplicaciones">
        <i class="mdi mdi-apps" style="font-size: 24px;"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end sso-app-launcher-menu p-3">
        @if($launcherTotal === 0)
            <div class="sso-app-launcher-empty">Sin aplicaciones disponibles</div>
        @else
            @if(!empty($launcherWeb))
                <div class="sso-app-section">
                    <div class="sso-app-section-title">Aplicaciones Web</div>
                    <div class="sso-app-grid">
                        @foreach($launcherWeb as $launcherApp)
                            {!! $renderTile($launcherApp) !!}
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($launcherInternas))
                <div class="sso-app-section">
                    <div class="sso-app-section-title">Aplicaciones Internas</div>
                    <div class="sso-app-grid">
                        @foreach($launcherInternas as $launcherApp)
                            {!! $renderTile($launcherApp) !!}
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($launcherMoviles))
                <div class="sso-app-section">
                    <div class="sso-app-section-title">Aplicaciones Moviles</div>
                    <div class="sso-app-grid">
                        @foreach($launcherMoviles as $launcherApp)
                            {!! $renderTile($launcherApp) !!}
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
