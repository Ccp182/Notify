# Tarea: migrar HMNotify a cliente SSO contra HMSrvAuth (multi-pais, 4 paises)
# Incluye: upgrade Laravel 8 → Laravel 12 desde cero + integracion SSO

## Contexto del ecosistema

Hunter Monitoreo tiene:
- HMSrvAuth (Laravel 12) en auth.24hm.net -> SSO centralizado via OAuth2/Passport,
  multi-pais (EC/PE/CO/CL), tablas oauth_* replicadas en cada BD de pais.
  **IMPORTANTE**: en este proyecto, los endpoints de negocio de HMNotify
  viven DENTRO de HMSrvAuth (no en HMSrv). Las APIs legado a migrar estan en:
    C:\Users\CarlosCarpioP\Desktop\Proyectos\CodeTest\SSO\HMSrv
  Especificamente en:
    - app/Http/Controllers/NotifyController.php
    - app/Handlers/NotifyDBHandlers.php
    - routes/servicios.php (grupo /Notify/{country}/...)
    - app/Handlers/AMDBHandlers.php (logica UsuarioAplicacionNotify en Autenticacion)
- TestTaller (testtaller2.24hm.net) -> cliente SSO ya migrado, REFERENCIA CANONICA
  MAS RECIENTE. Usa ExternalApiService, SSOController, EnsureSsoTokenIsValid.
- Onuris (onuristest.24hm.net) -> otro cliente SSO migrado (referencia adicional).

HMNotify es la proxima app cliente a migrar. Conserva su nombre "HMNotify"
(NO se renombra). URL productiva objetivo: `hmnotify.24hm.net`.

## Doble tarea: Laravel 12 desde cero + SSO

HMNotify actualmente es Laravel 8 (PHP 7.3+). NO hacer upgrade in-place.
Crear proyecto Laravel 12 DESDE CERO en la misma carpeta/rama, migrando solo
lo necesario del codigo legacy. El proyecto legacy esta en:
  C:\Users\CarlosCarpioP\Desktop\Proyectos\CodeTest\SSO\HMNotify

## Restriccion operativa CRITICA

Yo (el usuario) ejecuto TODO en el servidor y en las BDs. Tu solo:
- Modificas archivos en local.
- Al final de cada fase, me entregas una LISTA EXACTA de archivos modificados
  o creados, con ruta absoluta, para que yo los replique en el server.
- NO corras migrations, NO ejecutes `php artisan passport:client`, NO hagas
  deploys, NO toques supervisor. Solo generas los archivos/scripts y me los
  entregas.
- Los SPs te los paso yo manualmente cuando me los pidas. NO los inventes.

## Rama

Crea la rama `NotifySSO` en el repo de HMNotify desde la rama principal actual.
Todos los commits van ahi. En HMSrvAuth usa la rama actual (DevMain, NO crear
rama nueva).

## Skills a activar desde el inicio

- hm-client-sso     -> patrones de cliente OAuth2 (templates de SSOController,
                       EnsureSsoTokenIsValid, ExternalApiService, checklist,
                       red flags). LEELA COMPLETA.
- hm-architect-auth -> arquitectura de HMSrvAuth (Laravel 12), donde creamos
                       los endpoints de HMNotify. Patron Handler + SP +
                       DomainMiddleware + mapeo BD->API.
- sp-hunter         -> para revisar/documentar los SPs cuando te los pase.

NO actives hm-arch-srv-v1 (Lumen 8). Los endpoints de HMNotify NO van en HMSrv.

## Credenciales y documentacion

Usa el archivo: `HMSrvAuth/database/hmnotify_credentials.txt` (crealo siguiendo
la estructura de `testaller_credentials.txt` o `onurisplatform_credentials.txt`
que ya existen en esa carpeta).
Debe documentar:
- client_id / client_secret (placeholders; yo los genero con passport:client)
- base_uri, redirect_uri (`https://hmnotify.24hm.net/auth/callback`)
- Lista de BDs + conexiones (PX_DB sqlsrv, PX_DB_PERU sqlsrvpe, PX_DB_CO
  sqlsrvco, PX_DB_CL sqlsrvcl)
- Scripts SQL passport_sso para replicar el client en las 4 BDs
- .env a setear en HMNotify
- Las 4 reglas de oro del flujo OAuth multi-pais

## Que hace HMNotify

Sistema de envio de notificaciones push a las apps del ecosistema Hunter
(HMMovil, HunterGPS, Andorlink, Maresa, Alivo, Inka). Multi-pais (EC/PE/CO/CL).

Modulos:
1. Login con seleccion de app (combo) + validacion UsuarioAplicacionNotify
2. Dashboard de dispositivos (lista devices con push activo, filtros)
3. Notification Wizard v2 (envio avanzado: botones URL/call/WhatsApp,
   scheduling inmediato/una-vez/diario, campanas via SP)
4. Templates CSV (carga masiva por Chasis/Motor o Numero celular)

**SOLO se migra el flujo v2** (notificationWizard2). Las versiones legacy y v1
(postDispositivosSend, postDispositivosSendNew) NO se migran.

## Particularidad: seleccion de app post-login (Opcion B)

En el sistema legacy, el login pide en un combo una app (HMMovil, HunterGPS,
etc.) y valida que el usuario tenga permiso en la tabla `UsuarioAplicacionNotify`.
En SSO, el login ya no pasa por HMSrv sino por HMSrvAuth /oauth/authorize,
donde NO hay momento para pedir la app.

**SOLUCION APROBADA: Opcion B -- Middleware + pantalla post-login:**
1. SSO login normal -> callback -> sesion con User, Pais, access_token
2. Middleware `EnsureAppSelected` verifica `Session::has('AppNotify')`.
   Si no -> redirige a `/select-app`.
3. `/select-app` llama endpoint `POST /api/{country}/Notify/getUserApps`
   (retorna apps autorizadas del usuario desde UsuarioAplicacionNotify).
4. Si el usuario solo tiene 1 app -> auto-seleccionar y saltar la pantalla.
5. Si tiene >1 -> mostrar combo, usuario elige -> `Session::put('AppNotify')`.
6. Todas las rutas del dashboard estan protegidas por AMBOS middlewares
   (EnsureSsoTokenIsValid + EnsureAppSelected).

## Endpoints legado en HMSrv a migrar a HMSrvAuth (9 endpoints)

Todos actualmente en HMSrv (Lumen 8) bajo `/Notify/{country}/...`:

| # | Ruta HMSrv                              | Tipo query     | Migrar a HMSrvAuth como                      |
|---|-----------------------------------------|----------------|-----------------------------------------------|
| 1 | POST /Notify/{c}/getDispoByApp          | SQL directo    | POST /api/{c}/Notify/getDispoByApp            |
| 2 | POST /Notify/{c}/getDispoUserByApp      | spNFDipositivosApp | POST /api/{c}/Notify/getDispoUserByApp    |
| 3 | POST /Notify/{c}/getPlataformas         | SQL directo    | POST /api/{c}/Notify/getPlataformas           |
| 4 | POST /Notify/{c}/getTipoEntidad         | SQL directo    | POST /api/{c}/Notify/getTipoEntidad           |
| 5 | POST /Notify/{c}/getGrupoSubUsuario     | SQL directo    | POST /api/{c}/Notify/getGrupoSubUsuario       |
| 6 | POST /Notify/{c}/getDispoByMotorChasis  | spNT_obetenerHMUsuarios | POST /api/{c}/Notify/getDispoByMotorChasis |
| 7 | POST /Notify/{c}/getDispoByNumero       | spNT_obetenerHMUsuariosByNumero | POST /api/{c}/Notify/getDispoByNumero |
| 8 | (nuevo) getUserApps                      | SQL directo    | POST /api/{c}/Notify/getUserApps              |
| 9 | (desde cliente) saveWizard               | spNotifyWizard_Save | POST /api/{c}/Notify/saveWizard          |

**IMPORTANTE sobre sentencias directas:** NO manejar sentencias SQL directas.
Los 4 endpoints con SQL raw (getDispoByApp, getPlataformas, getTipoEntidad,
getGrupoSubUsuario) necesitan SPs nuevos. Yo te paso la logica SQL y tu generas
los scripts CREATE PROCEDURE para que yo los cree en las 4 BDs. Igualmente
getUserApps necesita un SP nuevo.

SPs nuevos a crear (scripts SQL):
- spNF_DispoByApp (reemplaza SQL raw de getDispoByApp)
- spNF_Plataformas (reemplaza SQL raw de getPlataformas)
- spNF_TipoEntidad (reemplaza SQL raw de getTipoEntidad)
- spNF_GrupoSubUsuario (reemplaza SQL raw de getGrupoSubUsuario)
- spNF_UsuarioAppsNotify (nuevo, para getUserApps)

Todos deben seguir patron sp-hunter: @Ejecucion OUTPUT, @Mensaje OUTPUT,
TRY/CATCH, SET NOCOUNT ON.

## Stored Procedures existentes que usa HMNotify

Viven en cada BD de pais (PX_DB, PX_DB_PERU, PX_DB_CO, PX_DB_CL):

  1. spNFDipositivosApp (getDispoUserByApp)
  2. spNT_obetenerHMUsuarios (getDispoByMotorChasis)
  3. spNT_obetenerHMUsuariosByNumero (getDispoByNumero)
  4. spNotifyWizard_Save (saveWizard -- llama internamente a
     spNotifyCampaign_Upsert y spNotifySend_Insert)

**YA los tengo.** Te los paso en el primer mensaje si los necesitas.

## Tabla UsuarioAplicacionNotify (estructura)

```sql
CREATE TABLE [dbo].[UsuarioAplicacionNotify](
    [IdUsuario] [int] NOT NULL,
    [IdAplicacion] [int] NOT NULL,
    [IdAplicacionLocal] [int] NOT NULL,
    [FechaIngreso] [datetime] NULL,
    [UsuarioIngreso] [varchar](50) NULL,
    CONSTRAINT [PK_UsuarioAplicacionNotify] PRIMARY KEY CLUSTERED
    ([IdUsuario] ASC, [IdAplicacion] ASC, [IdAplicacionLocal] ASC)
);
```

El endpoint getUserApps solo necesita:
`SELECT IdAplicacionLocal FROM UsuarioAplicacionNotify WHERE IdUsuario = @IdUsuario`
El nombre de la app (HMMovil, HunterGPS, etc.) se resuelve en el CLIENTE
desde el .env (variable APPS = JSON mapping id->nombre).

## Imagen y HTML upload

Las imagenes y archivos HTML se suben al filesystem del CLIENTE (HMNotify)
y se referencian como `https://hmnotify.24hm.net/assets/upload/...`.
El endpoint saveWizard en HMSrvAuth solo recibe la URL ya resuelta.
NO migrar el storage de archivos a HMSrvAuth.

## Referencia canonica (TestTaller -- la mas reciente)

Proyecto cliente SSO migrado mas recientemente:
  C:\Users\CarlosCarpioP\Desktop\Proyectos\CodeTest\SSO\TestTaller
  Rama: TestTallerSSO

Mira sobre todo:
- app/Http/Controllers/SSOController.php         (flujo OAuth2 cliente)
- app/Http/Middleware/EnsureSsoTokenIsValid.php   (validacion periodica)
- app/Services/ExternalApiService.php             (HTTP client con Bearer token)
- routes/web.php                                  (rutas SSO + grupo protegido)
- config/services.php                             (seccion core_sso)
- config/auth.php                                 (simplificado, sin provider custom)
- bootstrap/app.php                               (patron Laravel 12)
- .env                                            (vars CORE_SSO_*)

Tambien mira Onuris como referencia adicional:
  C:\Users\CarlosCarpioP\Desktop\Proyectos\CodeTest\SSO\Onuris

Lado servidor, mira los endpoints ya migrados en HMSrvAuth:
- routes/api.php (grupos TestTaller y Onuris ya registrados)
- app/Http/Controllers/TestTallerController.php (patron applyCountry)
- app/Http/Controllers/OnurisPlatformController.php
- app/Handlers/TestTallerDBHandlers.php (patron setCountryConnection)
- app/Handlers/OnurisPlatformDBHandlers.php
- database/testaller_credentials.txt (plantilla de documentacion)
- app/Auth/HMAuthProvider.php (el /me endpoint expone campo User)

## Fuente de APIs legado

Todas las APIs actuales de HMNotify estan en HMSrv (Lumen 8):
  C:\Users\CarlosCarpioP\Desktop\Proyectos\CodeTest\SSO\HMSrv

Archivos clave:
- routes/servicios.php (rutas /Notify/{country}/*)
- app/Http/Controllers/NotifyController.php
- app/Handlers/NotifyDBHandlers.php
- app/Handlers/AMDBHandlers.php (logica UsuarioAplicacionNotify, lineas 122-133
  y 260-271 -- valida cuando IdAplicacionAlt==59)

El proyecto cliente legacy (Laravel 8) con toda la logica de vistas y envio:
  C:\Users\CarlosCarpioP\Desktop\Proyectos\CodeTest\SSO\HMNotify

Archivos clave del cliente legacy:
- app/Http/Controllers/MessageController.php (TODO el flujo de notificaciones,
  SOLO migrar postDispositivosSendNew2 = v2 wizard)
- app/Http/Controllers/LoginController.php (auth legacy a ELIMINAR)
- app/Providers/ExternalApiUserProvider.php (provider custom a ELIMINAR)
- app/Support/Tenant.php (connection switching a ELIMINAR)
- app/Http/Middleware/ShareSessionDataMiddleware.php (a ELIMINAR)
- app/Http/Middleware/CountryConnectionMiddleware.php (a ELIMINAR)
- app/Http/Middleware/DomainMiddleware.php (a ELIMINAR, tiene dd() de debug)
- app/Helpers/FunctionsHelper.php (migrar solo funciones usadas)
- app/Helpers/AesCipher.php (evaluar si se necesita)
- resources/views/notificationWizard2.blade.php (vista v2 a MIGRAR)
- resources/views/notificationWizard.blade.php (v1, NO migrar)
- resources/views/message.blade.php (legacy, NO migrar)
- resources/views/auth/login.blade.php (ELIMINAR)
- resources/views/layouts/* (migrar los necesarios)
- config/global.php (tiene URL_HM, APP_CORE, APPS JSON mapping)
- .env (tiene APPS='{"HMMOVIL":1,...}' con mapping id->nombre)

## Alcance multi-pais

Los 4 paises desde el arranque: EC, PE, CO, CL.
- client_id y client_secret unicos, replicados en las 4 BDs via scripts SQL.
- HMNotify usa UN solo .env (no uno por pais).
- session('Pais') del cliente = pais OAuth de routing, nunca sobrescribir.

## Decisiones ya tomadas

1. **Opcion B** para seleccion de app post-login (middleware EnsureAppSelected)
2. **Laravel 12 desde cero** (NO upgrade in-place de Laravel 8)
3. **Migrar spNotifyWizard_Save a endpoint** en HMSrvAuth (el cliente no
   conecta directo a BD)
4. **Migrar inserts directos** (HMMNotificaciones, NotifyLog) a endpoints
   (ya estan dentro de spNotifyWizard_Save para v2)
5. **Solo flujo v2** (postDispositivosSendNew2 / notificationWizard2)
6. **Imagenes se suben en el cliente**, URL se pasa al endpoint
7. **5 SPs nuevos** a crear (no sentencias SQL directas en handlers)
8. **Bug conocido**: case 2 duplicado en postDispositivosSendNew2
   (HTML y Vencimiento) -- eliminar Vencimiento, mantener solo HTML

## Flujo que espero que sigas

### FASE 1 - DESCUBRIMIENTO (interactiva)

  a) Lee el proyecto HMNotify legacy (Laravel 8) para entender la estructura.
  b) Lee HMSrv/NotifyController + NotifyDBHandlers para mapear endpoints.
  c) Lee HMSrvAuth (TestTaller y Onuris ya migrados) como referencia.
  d) Entregame:
     - Confirmacion de que entiendes la arquitectura completa
     - Tabla final de endpoints a crear en HMSrvAuth (9 endpoints)
     - Lista de archivos a crear/modificar en HMSrvAuth
     - Lista de archivos a crear en HMNotify (Laravel 12)
     - Lista de archivos legacy a ELIMINAR de HMNotify
     - Preguntas abiertas (si las hay)

  NO toques codigo en esta fase. Solo analisis.

### FASE 2 - PLAN DETALLADO

Presentame un plan numerado con checkpoints claros:
  - BLOQUE A: HMSrvAuth (credentials, SP scripts, handler, controller, rutas)
  - BLOQUE B: HMNotify Laravel 12 (scaffold, SSO, middlewares, controllers,
    vistas, helpers, config, .env)
  - Orden de commits
  - Pasos manuales que YO debo hacer en el server

Espera mi aprobacion antes de FASE 3.

### FASE 3 - EJECUCION

  - Crea los 5 scripts SQL de SPs nuevos (yo los ejecuto en las 4 BDs).
  - Crea handlers + controller + rutas en HMSrvAuth.
  - Crea proyecto Laravel 12 en HMNotify (rama NotifySSO).
  - Al terminar cada bloque, entregame lista de archivos + pasos server.

### FASE 4 - VERIFICACION

Checklist E2E para los 4 paises (EC/PE/CO/CL):
  - Login SSO desde hmnotify.24hm.net
  - Callback recibe ?country=XX correcto
  - Pantalla select-app aparece (o auto-seleccion si 1 app)
  - Dashboard carga dispositivos correctamente
  - Envio wizard v2 funciona (inmediato + programado + diario)
  - Templates CSV funcionan
  - Logout centralizado

## Resumen de entregables

1. Rama `NotifySSO` en HMNotify.
2. En HMSrvAuth (rama DevMain):
   - database/hmnotify_credentials.txt
   - 5 scripts SQL para SPs nuevos (spNF_*)
   - app/Handlers/NotifyDBHandlers.php (9 metodos)
   - app/Http/Controllers/NotifyController.php
   - routes/api.php (grupo {country}/Notify con 9 rutas)
3. En HMNotify (rama NotifySSO, Laravel 12 desde cero):
   - SSOController, EnsureSsoTokenIsValid, ExternalApiService
   - EnsureAppSelected middleware + AppSelectController
   - MessageController (solo v2, usa ExternalApiService)
   - Vistas: select-app, notificationWizard2, layouts
   - Helpers migrados (solo los usados)
   - config/services.php, config/auth.php, bootstrap/app.php (L12)
   - .env con CORE_SSO_* + APPS JSON
   - Auth legacy ELIMINADA
4. Lista de archivos + pasos manuales server por cada bloque.
5. Commits atomicos con /commit cuando yo autorice.

NO comiences FASE 3 sin mi aprobacion del plan de FASE 2.
Arranca por FASE 1.
