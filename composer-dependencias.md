# Dependencias adicionales a instalar

Sobre una instalación base de Laravel 11:

```bash
# Exportación a Excel (RF-24)
composer require maatwebsite/excel

# Exportación a PDF (RF-25)
composer require barryvdh/laravel-dompdf

# Autenticación con Google (RF-11, prioridad "Debería tener")
composer require laravel/socialite
```

Utilidades de desarrollo:

```bash
composer require --dev laravel/pint       # formato de código
composer require --dev nunomaduro/collision
```

Verificación del estilo de código antes de cada entrega, conforme al
plan de calidad del entregable E-06:

```bash
./vendor/bin/pint --test
```
