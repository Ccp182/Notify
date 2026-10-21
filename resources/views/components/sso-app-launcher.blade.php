{{--
    Componente SSO App Launcher (waffle estilo Google).

    Mismas reglas que el inicio del SSO (HMSrvAuth home.blade.php):
      - Una sola seccion "Tus aplicaciones": web + internas (estas con etiqueta "Interna").
      - Sin el propio SSO (Core), sin apps legacy (Legacy), sin apps moviles (HMov=1)
        y sin apps sin URL o con '#'.
      - Logo: isotipo de la marca (Iso) sobre su color cuando el logo es el viejo largo;
        si no, el logo nuevo (ImgUrl / ImgShop).
      - Al final, "Ver todas las aplicaciones" -> inicio del SSO.

    Lee de session('SsoApps') (endpoint SSO /AuthSSO/apps). Ver skill hm-sso-ecosystem.

    Schema por item: NApp, Url, CPri, HInt, HMov, ImgUrl, ImgShop, Iso, Core, Legacy
    (los tres ultimos los agrega el SSO; sin ellos el componente degrada a lo anterior).
--}}
@php
    use Illuminate\Support\Facades\Session;
    $launcherApps = Session::get('SsoApps', []);

    $launcherTiles = [];
    foreach ($launcherApps as $launcherA) {
        $hint = (int)($launcherA['HInt'] ?? 0);
        $hmov = (int)($launcherA['HMov'] ?? 0);
        $url  = trim((string)($launcherA['Url'] ?? ''));
        // Sesiones anteriores al cambio del SSO no traen Core/Legacy: se deduce del id (valores de config/sso.php).
        $idApp  = (int)($launcherA['IdApp'] ?? $launcherA['IdAppCore'] ?? 0);
        $esCore = $launcherA['Core']   ?? ($idApp === 67);
        $esLeg  = $launcherA['Legacy'] ?? in_array($idApp, [1, 32, 49, 56], true);
        if ($esCore || $esLeg || $hmov === 1) {
            continue;
        }
        if ($url === '' || $url === '#') {
            continue;
        }
        $launcherA['_interna'] = $hint === 1;
        $launcherTiles[] = $launcherA;
    }
    $launcherTotal = count($launcherTiles);
    $launcherHome  = rtrim((string) config('services.core_sso.base_uri', ''), '/');
    $launcherHome  = $launcherHome !== '' ? $launcherHome . '/home' : '';

    $renderTile = function (array $a): string {
        $name  = $a['NApp']    ?? '';
        $url   = $a['Url']     ?? '#';
        $color = $a['CPri']    ?? '#556ee6';
        $iso   = $a['Iso']     ?? '';
        $img   = $a['ImgUrl']  ?? $a['ImgShop'] ?? $a['Img'] ?? '';
        $tag   = !empty($a['_interna']) ? '<span class="sso-app-tile-tag">Interna</span>' : '';
        if ($iso) {
            $icon = '<div class="sso-app-tile-icon sso-app-tile-icon--iso" style="background-color: '.e($color).';">'.
                        '<img src="'.e($iso).'" data-alt="'.e($img).'" alt="" '.
                        'onerror="this.onerror=null;this.parentNode.classList.remove(\'sso-app-tile-icon--iso\');this.parentNode.classList.add(\'sso-app-tile-icon--img\');this.src=this.dataset.alt">'.
                    '</div>';
        } elseif ($img) {
            $icon = '<div class="sso-app-tile-icon sso-app-tile-icon--img"><img src="'.e($img).'" alt=""></div>';
        } else {
            $icon = '<div class="sso-app-tile-icon" style="background-color: '.e($color).';">'.
                        '<span>'.e(mb_strtoupper(mb_substr($name, 0, 1))).'</span></div>';
        }
        return '<a href="'.e($url).'" class="sso-app-tile" target="_blank" rel="noopener">'.
                   $icon.'<span class="sso-app-tile-name">'.e($name).'</span>'.$tag.
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
    .sso-app-tile-icon--iso img{width:70%;height:70%;object-fit:contain;}
    .sso-app-tile-icon span{font-size:20px;font-weight:600;}
    .sso-app-tile-name{font-size:11px;line-height:1.2;color:#495057;word-break:break-word;}
    .sso-app-tile-tag{margin-top:.2rem;font-size:9px;line-height:1;padding:.15rem .4rem;border-radius:999px;background:rgba(116,120,141,.16);color:#74788d;font-weight:600;}
    .sso-app-more{display:block;margin-top:.6rem;padding:.55rem .25rem 0;border-top:1px solid rgba(0,0,0,.06);text-align:center;font-size:12px;font-weight:600;color:inherit;text-decoration:none;}
    .sso-app-more:hover{text-decoration:underline;color:inherit;}
    .sso-app-launcher-empty{padding:1.5rem .5rem;text-align:center;color:#9ea4ab;font-size:.85rem;}
    html[data-tema="oscuro"] .sso-app-tile-tag{background:rgba(255,255,255,.1);color:#9aa5c0;}
    html[data-tema="oscuro"] .sso-app-more{border-top-color:rgba(255,255,255,.08);}
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
            <div class="sso-app-section">
                <div class="sso-app-section-title">Tus aplicaciones</div>
                <div class="sso-app-grid">
                    @foreach($launcherTiles as $launcherApp)
                        {!! $renderTile($launcherApp) !!}
                    @endforeach
                </div>
            </div>
        @endif
        @if($launcherHome !== '')
            <a class="sso-app-more" href="{{ $launcherHome }}" target="_blank" rel="noopener">Ver todas las aplicaciones</a>
        @endif
    </div>
</div>
