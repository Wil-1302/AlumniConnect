# Despliegue en Render

Esta guía documenta, paso a paso, cómo desplegar Alumni Connect EFPISC en
[Render](https://render.com) usando PostgreSQL como base de datos. Sigue
el orden exacto: primero la base de datos, después el servicio web.

## 0. Antes de empezar

- El repositorio debe estar en GitHub (o GitLab/Bitbucket) y Render debe
  tener acceso a él.
- La rama que se despliega (normalmente `main`) debe tener el `Dockerfile`
  en la raíz del proyecto — ya está incluido en el repositorio.
- Necesitas una cuenta en Render (el plan gratuito alcanza para probar
  este proyecto, aunque las instancias gratuitas "duermen" tras un rato
  de inactividad).

## 1. Crear la base de datos PostgreSQL

1. En el panel de Render, click en **New +** → **PostgreSQL**.
2. Completa:
   - **Name**: `alumniconnect-db` (o el nombre que prefieras).
   - **Database**: `alumni_connect`.
   - **User**: déjalo con el valor sugerido.
   - **Region**: elige la más cercana a tus usuarios; **anota cuál
     elegiste**, el servicio web deberá crearse en la misma región.
   - **Plan**: Free (para pruebas) o el que corresponda.
3. Click en **Create Database**. Espera a que el estado pase a
   **Available** (uno o dos minutos).
4. Entra a la página de la base ya creada. En la sección **Connections**
   verás, entre otros datos:
   - **Hostname** (interno, algo como `alumniconnect-db.internal` o
     similar — úsalo si el web service vive en la misma región de
     Render, es más rápido y no requiere SSL)
   - **Port** (normalmente `5432`)
   - **Database**
   - **Username**
   - **Password**
   - **Internal Database URL** (una cadena `postgres://usuario:clave@host:puerto/basededatos` lista para usar)

   Guarda esta página abierta o copia estos valores a un lugar seguro:
   los necesitas en el paso 3.

## 2. Crear el Web Service

1. Click en **New +** → **Web Service**.
2. Conecta el repositorio de GitHub de Alumni Connect EFPISC y elige la
   rama a desplegar (`main`).
3. Render detecta el `Dockerfile` automáticamente y preselecciona
   **Environment: Docker**. No cambies esto — no uses "PHP" nativo, el
   `Dockerfile` ya resuelve las dependencias, los assets y la extensión
   `pdo_pgsql` que PostgreSQL necesita.
4. Completa:
   - **Name**: `alumniconnect` (o el nombre que prefieras; será parte de
     la URL pública `https://<name>.onrender.com`).
   - **Region**: **la misma región que elegiste para la base de datos**
     en el paso 1.
   - **Branch**: `main`.
   - **Instance Type**: Free para pruebas, o el plan que corresponda.
5. **No hagas click en "Create Web Service" todavía** — primero
   configura las variables de entorno del paso 3, para que el primer
   despliegue ya arranque bien.

## 3. Variables de entorno

En la sección **Environment Variables** del formulario de creación (o
después, en la pestaña **Environment** del servicio ya creado), agrega:

| Variable | Valor |
|---|---|
| `APP_NAME` | `Alumni Connect EFPISC` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | Ver nota debajo — **no la generes al azar tú mismo** |
| `APP_URL` | `https://<el-nombre-que-elegiste>.onrender.com` |
| `APP_LOCALE` | `es` |
| `APP_FALLBACK_LOCALE` | `es` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | El **Hostname interno** de la base (paso 1) |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `alumni_connect` |
| `DB_USERNAME` | El **Username** de la base (paso 1) |
| `DB_PASSWORD` | El **Password** de la base (paso 1) |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `LOG_CHANNEL` | `stack` |
| `LOG_LEVEL` | `error` |

**Sobre `APP_KEY`:** no debe salir del código ni de un archivo `.env`
local. Generarla es un comando, no un secreto que se transporta:

- Opción simple: agrega la variable `APP_KEY` vacía por ahora, deja que
  el primer despliegue termine, y luego desde la pestaña **Shell** del
  servicio en Render ejecuta:
  ```
  php artisan key:generate --show
  ```
  Copia el valor que imprime (empieza con `base64:`) y pégalo como valor
  de `APP_KEY` en **Environment**. Render reinicia el servicio solo al
  guardar la variable.

**Alternativa a `DB_HOST`/`DB_PORT`/etc. por separado:** en vez de las
cinco variables `DB_*`, puedes definir una sola `DB_URL` con el
**Internal Database URL** completo que Render te mostró en el paso 1.
Ambas formas funcionan; esta guía usa las discretas porque son las que
ya usa este proyecto en `.env`/`.env.example`.

## 4. Health check

En **Settings** del Web Service, campo **Health Check Path**, escribe:

```
/up
```

Esa ruta ya existe en la aplicación (`bootstrap/app.php`, `health: '/up'`)
y a partir de Laravel 11 responde 200 sin necesitar sesión ni base de
datos, así que sirve para que Render sepa cuándo el contenedor está listo.

## 5. Primer despliegue

1. Con las variables de entorno ya cargadas, click en **Create Web
   Service** (o **Deploy** si ya lo habías creado).
2. Render construye la imagen Docker: instala dependencias de Composer,
   compila los assets con Vite/Tailwind y arma la imagen final. La
   primera vez tarda varios minutos.
3. Al arrancar el contenedor, `docker/entrypoint.sh` ejecuta
   automáticamente `php artisan migrate --force` contra la base de Render
   — no necesitas correr las migraciones a mano. También cachea
   configuración, rutas y vistas.
4. Cuando el estado pase a **Live**, abre la URL pública
   (`https://<nombre>.onrender.com`) y verifica que `/` y `/login`
   carguen.

### Nota sobre dónde corren las migraciones

Las migraciones **no** se ejecutan durante la construcción de la imagen
(`docker build`): en ese momento el contenedor todavía no tiene acceso a
la base de datos de Render. Por eso corren en `docker/entrypoint.sh`, en
cada arranque del contenedor — es idempotente (Laravel omite las
migraciones ya aplicadas), así que no hay problema en que se repita en
cada despliegue.

Esto es seguro para una sola instancia, que es el caso de este proyecto.
Si en el futuro escalas el Web Service a más de una instancia
simultánea, dos contenedores podrían intentar migrar a la vez al
arrancar juntos. Ese día, conviene mover `php artisan migrate --force`
del `entrypoint.sh` a un paso de **Pre-Deploy Command** (si tu plan de
Render lo ofrece) o a un Job manual ejecutado una sola vez antes del
despliegue.

## 6. Verificación posterior al despliegue

- Abre `/` y `/login`: deben cargar sin error 500.
- Revisa los **Logs** del servicio en Render: no deberían aparecer
  excepciones al recibir tráfico normal.
- Desde la pestaña **Shell**, puedes correr comandos puntuales, por
  ejemplo para sembrar catálogos la primera vez:
  ```
  php artisan db:seed --class=CatalogosSeeder
  ```
  (No ejecutes `PadronPruebaSeeder` en producción: es solo para pruebas
  en desarrollo, así lo indica su propio comentario en el código. El
  padrón real se carga desde `/admin/padron/importar`, RF-31.)
- Crea el primer usuario administrador real desde la Shell, con:
  ```
  php artisan usuario:crear-admin
  ```
  Pide correo y contraseña de forma interactiva (contraseña oculta, con
  confirmación) y crea un usuario con rol `admin_principal`. Es el único
  rol que puede importar el padrón y crear administradores adicionales
  desde ahí en adelante.

## 7. Dominio propio (opcional)

En **Settings** → **Custom Domains** puedes apuntar un dominio de la
universidad si lo tienen disponible. Tras agregarlo, actualiza la
variable de entorno `APP_URL` para que coincida exactamente con el
dominio final (afecta enlaces generados por la aplicación, como los de
`route()`).

## 8. Resumen de archivos involucrados

| Archivo | Para qué sirve |
|---|---|
| `Dockerfile` | Define la imagen: compila assets, instala dependencias PHP, configura Apache |
| `docker/000-default.conf` | Vhost de Apache apuntando a `public/` |
| `docker/entrypoint.sh` | Migra la base y cachea config/rutas/vistas en cada arranque, antes de levantar Apache |
| `.dockerignore` | Evita que `vendor/`, `node_modules/`, `.env` y logs locales entren a la imagen |
| `.env.example` | Plantilla de referencia (no se usa en Render: ahí las variables se configuran en el panel, como se explicó arriba) |
