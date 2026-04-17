# HMNotify (Laravel 12 + SSO)

Cliente web del sistema centralizado de notificaciones push de Hunter
Monitoreo. Reemplaza el HMNotify legacy (Laravel 8 + auth local contra HMSrv).

Autenticacion via OAuth2 Authorization Code contra **HMSrvAuth**
(`auth.24hm.net`). Los 9 endpoints de negocio viven en HMSrvAuth bajo el
prefijo `/api/{country}/Notify/*`.

## Stack

- PHP 8.2+ / Laravel 12
- Bootstrap 5 / Nazox v2.1.0 (template)
- Guzzle para HTTP a HMSrvAuth
- Driver de sesion por archivo (o Redis en multi-nodo)

## Estructura relevante

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── SSOController.php         ← OAuth flow (authorize/callback/logout)
│   │   ├── AppSelectController.php   ← Opcion B: pantalla post-login
│   │   └── MessageController.php     ← Dashboard + wizard v2 + AJAX proxies
│   └── Middleware/
│       ├── EnsureSsoTokenIsValid.php ← Protege rutas autenticadas
│       └── EnsureAppSelected.php     ← Requiere Session::AppNotify
├── Services/
│   └── ExternalApiService.php        ← Cliente HTTP a HMSrvAuth
├── Helpers/
│   └── FunctionsHelper.php           ← Helpers globales (autoload files)
├── Providers/
│   └── AppServiceProvider.php

config/
├── services.php                       ← core_sso section
├── global.php                         ← APPS mapping (IdAplicacion core → {local, name, color})
└── (resto estandar Laravel 12)

resources/views/
├── layouts/
│   ├── app.blade.php                  ← Layout autenticado (Nazox)
│   ├── auth.blade.php                 ← Layout login/select-app
│   ├── head-css.blade.php, topbar.blade.php, sidebar.blade.php,
│   ├── footer.blade.php, vendor-scripts.blade.php, page-title.blade.php
├── select-app.blade.php               ← Pantalla post-login (Opcion B)
├── message.blade.php                  ← Dashboard de dispositivos
├── notificationWizard2.blade.php      ← Wizard v2 (4 pasos)
└── auth/sso-error.blade.php

public/assets/                         ← Assets Nazox (libs, css, images) - preservados del legacy
```

## Rutas

### Publicas
- `GET /login` → redirige a `auth.24hm.net/oauth/authorize`
- `GET /auth/callback` → recibe `?code=&state=&country=`, intercambia por token
- `POST /logout` → cierra sesion local + HMSrvAuth

### Con SSO valido pero sin app seleccionada
- `GET /select-app` → lista apps autorizadas (llama `Notify/getUserApps`)
- `POST /select-app` → guarda `Session::AppNotify`

### Completamente protegidas (SSO + AppNotify)
- `GET /dashboard` → listado de dispositivos
- `GET /wizard` → wizard v2
- `POST /wizard/send` → envia la campana
- `POST /wizard/template-send` / `/wizard/template-num-send` → CSV masivo
- `POST /api-proxy/dispositivos*` / `GET /api-proxy/catalogos` → AJAX proxies

## Flujo OAuth multi-pais (reglas de oro)

1. `session('Pais')` se setea UNA vez en el callback desde `?country=` inyectado
   por HMSrvAuth. **NUNCA** se sobrescribe con el `Country` del perfil.
2. El POST `/oauth/token` siempre incluye `country` en el body (si no,
   `DomainMiddleware` de HMSrvAuth busca el auth_code en la BD equivocada).
3. Las llamadas a API siempre usan `/api/{country}/...` con el pais de
   `session('Pais')`.
4. El `client_id`/`client_secret` es **el mismo** en las 4 BDs (EC/PE/CO/CL).

## Deploy

### 1. Subir al server

```bash
cd /var/www/html/
git clone -b NotifySSO https://github.com/Ccp182/Notify.git HMNotify
cd HMNotify
```

### 2. Instalar deps

```bash
composer install --optimize-autoloader --no-dev
```

### 3. Configurar .env

```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env`:
- `APP_URL=https://hmnotify.24hm.net`
- `APP_DEBUG=false`
- `CORE_SSO_CLIENT_ID=<UUID del passport:client creado>`
- `CORE_SSO_CLIENT_SECRET=<secret plaintext>`
- `APPS='{...}'` con el mapping final para cada pais

### 4. Permisos

```bash
chown -R www-data:www-data storage bootstrap/cache public/assets/upload
chmod -R 775 storage bootstrap/cache public/assets/upload
```

### 5. Caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Virtual host (Apache ejemplo)

```apache
<VirtualHost *:443>
    ServerName hmnotify.24hm.net
    DocumentRoot /var/www/html/HMNotify/public

    <Directory /var/www/html/HMNotify/public>
        AllowOverride All
        Require all granted
    </Directory>

    SSLEngine on
    SSLCertificateFile    /etc/letsencrypt/live/hmnotify.24hm.net/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/hmnotify.24hm.net/privkey.pem

    ErrorLog  ${APACHE_LOG_DIR}/hmnotify-error.log
    CustomLog ${APACHE_LOG_DIR}/hmnotify-access.log combined
</VirtualHost>
```

Nginx equivalente: `root /var/www/html/HMNotify/public;` + bloque PHP-FPM
estandar de Laravel.

### 7. Verificacion

- `https://hmnotify.24hm.net/` → redirige a login SSO.
- Login → callback → `/select-app` (o auto redirect si 1 sola app).
- Dashboard muestra dispositivos activos.
- Wizard permite crear/programar notificacion.

## Pre-requisitos en HMSrvAuth

Antes del deploy, asegurar:

- ✅ OAuth client creado en PX_DB con
  `php artisan passport:client --redirect_uri="https://hmnotify.24hm.net/auth/callback" --name="HMNotify"`.
- ✅ `oauth_clients` replicado a PX_DB_PERU, PX_DB_CO, PX_DB_CL
  (script en `HMSrvAuth/database/sql/passport_client_hmnotify.sql`).
- ✅ 11 SPs `spNT_*` instalados en las 4 BDs
  (carpeta `HMSrvAuth/database/sp/`).
- ✅ 9 rutas `/api/{country}/Notify/*` registradas en HMSrvAuth
  (ya en `routes/api.php` de HMSrvAuth).

## Skills relacionadas (para sesiones futuras de Claude)

- `hm-client-sso` — patron OAuth2 cliente.
- `hm-architect-auth` — arquitectura HMSrvAuth.
- `hm-nazox` — design system Nazox v2.1.0.
- `sp-hunter` — estandar de Stored Procedures.

## Rama

- `main`: HMNotify legacy (Laravel 8). Congelado, solo lectura.
- `NotifySSO`: esta migracion (Laravel 12 + SSO). Commit de trabajo.
