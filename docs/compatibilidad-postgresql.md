# Compatibilidad de las migraciones con PostgreSQL

## 1. Contexto

El esquema de base de datos de Alumni Connect EFPISC se diseñó originalmente con
sintaxis y tipos de MySQL, tal como se documentó en el entregable E-08 (Diseño
del Sistema). Al adoptar PostgreSQL 16 como motor de producción, se revisaron
las quince migraciones del andamiaje para verificar que todas pudieran
ejecutarse sobre PostgreSQL sin fallar, y para identificar cualquier
diferencia de comportamiento entre ambos motores que valiera la pena señalar
al equipo, aunque no impidiera correr las migraciones.

La revisión no encontró ningún caso que bloquee `php artisan migrate`. Sí se
encontraron tres puntos donde el mismo código de migración produce, en
PostgreSQL, un tipo de columna o mecanismo de validación distinto al que
produciría en MySQL. Los tres se explican a continuación, junto con la
decisión tomada sobre cada uno.

Dos casos aparte, que sí eran incompatibilidades reales (no simples
diferencias de tipo), se corrigieron directamente en el código: una expresión
`SUM(columna = 1)` en `ReporteEgresadosService::distribucionPorPromocion()`,
que MySQL acepta pero PostgreSQL rechaza por comparar un booleano con un
entero, y una condición `x.es_actual = 1` en la vista
`v_situacion_actual_egresado`, con el mismo problema. Ambas se reescribieron
con una forma compatible con los dos motores (`CASE WHEN ... THEN 1 ELSE 0
END` y `= TRUE`, respectivamente). Este documento no trata esos dos casos,
ya resueltos, sino los tres que se decidió dejar tal como están.

## 2. Enteros sin signo (`unsigned`)

Dieciocho columnas del esquema —prácticamente todas las claves foráneas del
modelo, más dos columnas de orden (`preguntas.orden` y
`opciones_pregunta.orden`)— se declaran con `unsignedInteger`,
`unsignedSmallInteger` o `unsignedTinyInteger`. Estas columnas aparecen en
`auditoria_accesos`, `egresados`, `experiencias_laborales`,
`estudios_posgrado`, `ofertas_laborales`, `encuestas`, `preguntas`,
`opciones_pregunta`, `respuestas_encuesta` y `detalle_respuestas`.

MySQL implementa el modificador `unsigned` a nivel de motor: la columna
rechaza cualquier valor negativo. PostgreSQL no tiene un tipo entero sin
signo, y el generador de esquema de Laravel para PostgreSQL simplemente
omite el modificador, creando la columna como un entero con signo del mismo
tamaño (`integer` o `smallint`, según corresponda). La migración se ejecuta
sin error; lo único que cambia es que PostgreSQL, a diferencia de MySQL, no
impide por sí mismo que se inserte un valor negativo en esa columna.

**Decisión:** no se agregan restricciones `CHECK (columna >= 0)` para
compensar esta diferencia. Las dieciocho columnas afectadas son claves
foráneas autogeneradas por el propio sistema (nunca reciben un valor
negativo escrito por el usuario) o contadores de orden que la aplicación
inicializa en 1 y solo incrementa. El riesgo real de que una de estas
columnas reciba un valor negativo es nulo, y agregar la restricción a mano
en dieciocho columnas añadiría complejidad a las migraciones sin reducir
un riesgo que ya es inexistente en la práctica.

## 3. Enumeraciones (`enum`)

Seis columnas usan `enum()` para restringir sus valores a una lista fija:
`padron_egresados.grado` y `egresados.grado` (`bachiller`, `titulado`),
`usuarios.rol` (`egresado`, `administrador`, `admin_principal`),
`estudios_posgrado.grado` (`diplomado`, `maestria`, `doctorado`),
`ofertas_laborales.modalidad` (`presencial`, `remoto`, `hibrido`) y
`preguntas.tipo` (`opcion_multiple`, `escala`).

MySQL implementa `ENUM` como un tipo de columna nativo. PostgreSQL no cuenta
con un equivalente directo en el generador de esquema de Laravel, de modo
que estas columnas se crean como texto (`VARCHAR`) acompañadas de una
restricción `CHECK (columna IN ('valor1', 'valor2', ...))` que PostgreSQL
evalúa en cada inserción o actualización.

**Decisión:** no se sustituye el `CHECK` por ningún mecanismo adicional. La
restricción cumple exactamente la misma función de validación que el
`ENUM` de MySQL: ambas impiden que se guarde un valor fuera de la lista
permitida. La única diferencia práctica aparece si en el futuro se necesita
agregar un valor nuevo a alguna de estas listas: en MySQL bastaría con un
`ALTER TABLE ... MODIFY COLUMN` sobre el `ENUM`, mientras que en PostgreSQL
se necesitaría una migración que elimine y vuelva a crear el `CHECK` con la
lista ampliada. Se trata de un costo de mantenimiento menor y ya conocido,
no de una pérdida de integridad de datos.

## 4. Enteros pequeños (`tinyIncrements`, `smallIncrements`, `tinyInteger`)

Cuatro columnas usan los tipos más pequeños del generador de esquema:
`situaciones_laborales.id` (`tinyIncrements`, clave primaria),
`rubros.id` (`smallIncrements`, clave primaria), `opciones_pregunta.valor`
(`tinyInteger`, nullable) y `detalle_respuestas.valor_escala`
(`tinyInteger`, nullable).

MySQL ofrece un tipo entero de un byte (`TINYINT`). PostgreSQL no lo tiene:
su entero más pequeño es `smallint`, de dos bytes, con rango de -32 768 a
32 767. El generador de esquema de Laravel traduce automáticamente
`tinyIncrements` y `tinyInteger` a `smallserial`/`smallint` cuando el motor
de destino es PostgreSQL.

**Decisión:** no se realiza ningún ajuste. El costo es de un byte adicional
por fila en cada una de estas cuatro columnas, sin ningún efecto sobre el
comportamiento de la aplicación: los valores reales que almacenan estas
columnas (identificadores de catálogos con pocas decenas de filas como
mucho, y valores de escala de encuesta que en la práctica van de 1 a 5)
están muy por debajo del límite de `smallint`. El cambio de tipo es
completamente transparente para el código de la aplicación.

## 5. Resumen

| Diferencia | Columnas afectadas | Efecto en PostgreSQL | Compensación aplicada |
|---|---|---|---|
| `unsigned` | 18, en 10 tablas | Se ignora; la columna queda con signo | Ninguna |
| `enum()` | 6, en 6 tablas | Se traduce a `VARCHAR` + `CHECK` | Ninguna |
| `tinyIncrements`/`tinyInteger` | 4, en 4 tablas | Se traduce a `smallserial`/`smallint` | Ninguna |

Ninguna de las tres diferencias impide ejecutar las migraciones ni compromete
la integridad de los datos del sistema; por eso se documentan como decisiones
conscientes de diseño y no se modifica el código de las migraciones para
compensarlas.
