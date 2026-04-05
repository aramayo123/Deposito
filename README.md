# Sistema de gestión de depósito

Aplicación web **Laravel 11** + **PHP 8.2** para control de inventario: productos, entradas/salidas con tickets, historial, alertas de stock mínimo, dashboard y búsqueda global. Frontend con **Blade**, **Tailwind CSS** y **JavaScript** vía CDN (sin Vite ni bundlers). Notificaciones en tiempo real con **Laravel Reverb** (broadcasting) y toasts en **CSS + JS** puro.

## Requisitos del sistema

- PHP 8.2+ con extensiones: `pdo_mysql`, `gd`, `fileinfo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- MySQL 8+
- Composer 2+

## Instalación paso a paso

1. Clonar o copiar el proyecto en tu servidor local (por ejemplo `c:\wamp64\www\Deposito`).
2. `composer install`
3. `copy .env.example .env` (Windows) o `cp .env.example .env` (Linux/macOS)
4. `php artisan key:generate`
5. En `.env`, configurar base de datos: `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (usar `DB_CONNECTION=mysql` para MySQL).
6. Configurar broadcasting para Reverb (o Pusher): `BROADCAST_CONNECTION=reverb` y las variables `REVERB_*` según `php artisan reverb:install`.
7. `php artisan migrate --seed`
8. `php artisan storage:link`
9. En una terminal aparte: `php artisan queue:work` (cola en base de datos para eventos en cola).
10. En otra terminal: `php artisan reverb:start` (si usás Reverb).
11. `php artisan serve`
12. Abrir `http://localhost:8000` (el inicio redirige al dashboard).

### Archivos estáticos

Los estilos base están en `public/css/app.css` (y duplicados en `resources/css/app.css` según estructura del proyecto). Los scripts están en `public/js/` y se reflejan en `resources/js/` para mantener la convención de carpetas.

## Tests PHP

```bash
php artisan test
```

Los tests usan SQLite en memoria (`phpunit.xml`) y `BROADCAST_CONNECTION=log` para no requerir Reverb en ejecución.

```bash
php artisan test --coverage
```
(Requiere Xdebug o PCOV configurado.)

## Colección Postman

1. Abrir Postman → **Import** → archivo `postman_collection.json`.
2. Crear un environment con `base_url = http://localhost:8000` y `api_prefix = /api` si lo usás en scripts.
3. Para rutas web con sesión/CSRF: hacer primero `GET {{base_url}}/` y copiar el token CSRF de la cookie/sesión o del meta tag HTML hacia la variable `csrf_token` del environment.

## Variables de entorno importantes

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=deposito
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
QUEUE_CRON_TOKEN=
QUEUE_CRON_MAX_SECONDS=55
```

Valores típicos de Reverb (tras `php artisan reverb:install`):

```
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

## Estructura funcional

- **Productos**: CRUD, foto en `storage/app/public/products`, ajuste de dañados, historial y API JSON.
- **Entradas / Salidas**: tickets con ítems múltiples, transacciones, validación de stock en salidas.
- **Dashboard**: métricas cacheadas 60 s, alertas, últimos movimientos, gráfico Canvas 7 días.
- **Informes**: búsqueda global (`/reports/global` + `GET /api/reports/search`).
- **Alertas**: `GET /api/stock-alerts`, `PATCH /api/stock-alerts/{id}/read`.

## Guía de conceptos (para entregar a cliente o equipo)

### Estructura resumida del proyecto

| Ruta / carpeta | Rol |
|----------------|-----|
| `app/Http/Controllers` | Lógica HTTP: pantallas web y respuestas JSON de la API. |
| `app/Http/Requests` | Validación de formularios (reglas centralizadas). |
| `app/Http/Resources` | Formato JSON uniforme para la API. |
| `app/Models` | Tablas y relaciones Eloquent. |
| `app/Services` | Reglas reutilizables (historial, alertas). |
| `app/Events` / `app/Listeners` | Eventos en cola y notificaciones broadcast. |
| `app/Support` | Utilidades (p. ej. etiquetas en español del historial). |
| `database/migrations` | Esquema de la base de datos. |
| `database/seeders` | Datos de prueba. |
| `resources/views` | Plantillas Blade (HTML del sistema). |
| `public/css`, `public/js` | CSS y JS servidos sin compilar (Tailwind/Swal por CDN en el layout). |
| `routes/web.php` | Rutas del navegador (formularios, sesión). |
| `routes/api.php` | Rutas JSON bajo prefijo `/api`. |

### ¿Para qué sirve la cola (`QUEUE_CONNECTION=database` + `php artisan queue:work`)?

No es “para multiusuario” en sí. **Sirve para procesar trabajos en segundo plano** sin bloquear la respuesta al navegador.

En este proyecto, los eventos `LowStockEvent` y `ProductMovementEvent` implementan `ShouldQueue`: Laravel los encola y un worker (`queue:work`) los ejecuta después. Ahí se hace el **broadcast** (avisar por WebSocket) sin que el usuario espere si la red es lenta o el servidor de websockets no responde al instante.

- **Varios usuarios** pueden usar el sistema a la vez igual con o sin cola; la cola mejora **rendimiento y fiabilidad** (reintentos, no frenar el guardado del formulario).
- En **tests**, `QUEUE_CONNECTION=sync` ejecuta el trabajo en el mismo proceso (inmediato, sin worker).

### Push en tiempo real y Reverb (`php artisan reverb:start`)

**Reverb** es el servidor de **WebSockets** de Laravel: mantiene conexiones abiertas con el navegador y envía eventos cuando ocurre algo (stock bajo, nueva entrada/salida).

1. En `.env`: `BROADCAST_CONNECTION=reverb` y variables `REVERB_APP_*`, `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME` (suelen generarse con `php artisan reverb:install`).
2. Arrancar el servidor: `php artisan reverb:start`.
3. El JS del layout (`notifications.js`) se conecta al host/puerto configurados y escucha el canal `deposito-notifications`.

### Error en el puerto 8080 (o “address already in use”)

Ese puerto **ya lo usa otro programa** (otro Reverb, otro servicio, Docker, etc.).

**Solución:** en `.env` cambiá por un puerto libre, por ejemplo:

```env
REVERB_PORT=6001
```

Reiniciá `reverb:start` y, si el front lee el puerto desde el servidor, asegurate de que `REVERB_PORT` en `.env` coincida con lo que usa el navegador (el layout toma `env('REVERB_PORT')` vía Blade).

### ¿Reverb es obligatorio en producción?

**No.** Solo hace falta si querés **notificaciones en tiempo real** en el navegador (campanita / toasts vía WebSocket).

- **Sin Reverb (ni Pusher):** poné `BROADCAST_CONNECTION=log` o `null` en `.env`. El sistema sigue funcionando: productos, entradas, salidas, historial, alertas en base de datos y dashboard. Los eventos no “llegan al instante” al cliente, pero el negocio no se cae.
- **Con servicio gestionado:** podés usar **Pusher** (u otro) en lugar de Reverb si no querés mantener un proceso WebSocket propio.
- **Con Reverb en producción:** necesitás un VPS/servidor donde puedas dejar **siempre corriendo** `php artisan reverb:start` (o un supervisor tipo systemd). En **hosting compartido clásico** suele ser inviable; en ese caso desactivá tiempo real o usá Pusher.

### Cola en hosting compartido: ruta `GET /run-queue`

Muchos hostings **no permiten** dejar `php artisan queue:work` corriendo 24/7. Como alternativa, este proyecto expone una URL que **procesa la cola una vez por llamada** (hasta vaciar trabajos pendientes o hasta un límite de segundos), pensada para llamarla desde **cron** cada 1–5 minutos.

1. En `.env` definí un secreto largo y aleatorio:

   ```env
   QUEUE_CRON_TOKEN=cambiá_por_un_secreto_largo_y_único
   QUEUE_CRON_MAX_SECONDS=55
   ```

   Si `QUEUE_CRON_TOKEN` está **vacío**, la ruta responde **404** (nadie puede ejecutarla por error).

2. En el panel de cron del hosting (o `crontab`), programá por ejemplo cada 5 minutos:

   ```bash
   wget -q -O - "https://TU-DOMINIO.com/run-queue?token=cambiá_por_un_secreto_largo_y_único"
   ```

   O con curl:

   ```bash
   curl -fsS "https://TU-DOMINIO.com/run-queue?token=TU_SECRETO"
   ```

3. Ajustá `QUEUE_CRON_MAX_SECONDS` si el hosting corta PHP a los 30 s (por ejemplo `25`).

**Importante:** la URL con token es sensible; no la compartas ni la subas a repositorios públicos. Rotá el token si se filtra.

Seguís necesitando `QUEUE_CONNECTION=database` (o redis) y la tabla `jobs` migrada; esta ruta solo **sustituye** al worker permanente, no a la cola en sí.

### Tests: `MissingAppKeyException`

PHPUnit **no carga el `.env` de tu carpeta** como el servidor: necesita `APP_KEY` definida en `phpunit.xml` (ya incluida en este repo) **o** que exista `.env` con `APP_KEY` generada (`php artisan key:generate`).

Si ves “No application encryption key”, comprobá que `phpunit.xml` tenga la línea `<env name="APP_KEY" value="base64:..."/>`.

### Historial: texto en inglés (`entry`, `exit`, …)

En base de datos se guardan códigos **técnicos** en inglés (convención Laravel / APIs). En pantalla se muestran como **`exit (Salida)`**, **`entry (Entrada)`**, etc., usando `App\Support\HistoryActionLabels`. La descripción larga ya está en español (“Salida: -3 unidades”, etc.).

## Licencia

MIT (igual que Laravel).
