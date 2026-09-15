# Alumni Connect EFPISC

Plataforma de Vinculación y Seguimiento de Egresados de la Escuela de Formación
Profesional de Ingeniería de Sistemas y Computación — UNDAC.

Proyecto del curso **Gestión de Proyectos**, VIII ciclo, semestre 2026 B.
Docente: Mg. De la Cruz Rocca, Marco Antonio. **Grupo 5**.

---

## 1. Estado

Andamiaje del prototipo (entregable E-09). Contiene la estructura de carpetas,
las migraciones, los modelos, los repositorios, los servicios, los controladores
y las rutas. **Las vistas Blade están pendientes de construcción** en las
actividades A25 a A33 del cronograma.

## 2. Requisitos previos

- PHP 8.2 o superior
- Composer 2
- PostgreSQL 16
- Node 18 o superior (solo para compilar los recursos de interfaz)

## 3. Instalación

```bash
# 1. Crear el proyecto Laravel base y copiar este andamiaje encima
composer create-project laravel/laravel alumni-connect
cd alumni-connect

# 2. Copiar las carpetas app/, database/, routes/ y los archivos raíz

# 3. Configurar el entorno
cp .env.example .env
php artisan key:generate

# 4. Crear la base de datos y ejecutar las migraciones
php artisan migrate
php artisan db:seed --class=CatalogosSeeder

# 5. Levantar el servidor
php artisan serve
```

## 4. Registro del middleware

En `bootstrap/app.php`, dentro de `withMiddleware`:

```php
$middleware->alias([
    'rol' => App\Http\Middleware\VerificarRol::class,
]);
```

## 5. Reglas de la arquitectura

Estas reglas **no son sugerencias**. Su incumplimiento constituye una no
conformidad conforme al plan de calidad (entregable E-06).

| Regla | Detalle |
|---|---|
| Separación en capas | Presentación → Negocio → Datos. Ninguna capa salta niveles. |
| Controladores delgados | Solo reciben, delegan y responden. Más de 20 líneas es señal de alarma. |
| Sin consultas fuera de repositorios | Ningún `DB::` ni `Model::where` fuera de `Repositories/`. |
| Reglas de negocio en servicios | Las reglas RN-01 a RN-10 viven en `Services/`, nunca en controladores. |
| Organización por dominio | El código se agrupa por dominio funcional, no por tipo de archivo. |
| Sin credenciales en el código | Todo parámetro sensible va en `.env`, excluido de Git. |
| Identificadores en español | Salvo palabras reservadas y sufijos de convención del framework. |

## 6. Estructura

```
app/
├── Domain/          Lógica de negocio y acceso a datos, agrupados por dominio
│   ├── Catalogos/   Rubros, situaciones laborales y padrón institucional
│   ├── Egresados/   Registro, perfil e historial laboral
│   ├── Encuestas/   Encuestas, preguntas y respuestas
│   ├── Ofertas/     Bolsa de trabajo
│   ├── Reportes/    Indicadores y exportación
│   └── Seguridad/   Usuarios, autenticación y auditoría
├── Http/            Capa de presentación: controladores y middleware
└── Shared/          Código transversal, sin reglas de negocio
```

Cada dominio contiene `Models/`, `Services/`, `Repositories/` y `Requests/`.

## 7. Flujo de una petición

Ejemplo del caso de uso CU-01 (registro de egresado):

```
routes/web.php
  └─ RegistrarEgresadoRequest   valida formato y obligatoriedad
       └─ RegistroController    delega; no contiene lógica
            └─ RegistroEgresadoService   aplica RN-01, RN-02 y RN-08
                 └─ EgresadoRepository   persiste en transacción
```

## 8. Trabajo pendiente

- [ ] Vistas Blade de los diez escenarios del numeral 6.1 del entregable E-08
      (no se pudo verificar contra ese documento: nunca estuvo disponible en
      `docs/` durante el desarrollo; sí se construyeron todas las pantallas
      pedidas explícitamente)
- [x] Módulo de encuestas: controladores de la administración (RF-19, RF-20)
- [x] Exportadores a Excel y PDF (RF-24, RF-25)
- [ ] Recordatorios de actualización de perfil (RF-21)
- [ ] Autenticación con Google (RF-11)
- [x] Pruebas de las reglas RN-01, RN-02, RN-04, RN-06, RN-07 y RN-08
- [x] Carga inicial del padrón de egresados (RF-31: importación masiva desde
      Excel/CSV en `/admin/padron/importar`, solo admin_principal;
      `PadronPruebaSeeder` sigue siendo aparte, solo para desarrollo)
- [x] Comando `usuario:crear-admin` para el primer acceso en producción,
      reemplaza la creación manual de administradores en base de datos

## 9. Convenciones de ramas

```
main                    Versión estable, solo por fusión revisada
develop                 Integración continua del equipo
feature/descripcion     Trabajo en curso

# Prohibido trabajar directamente sobre main (respuesta al riesgo R-10)
```
