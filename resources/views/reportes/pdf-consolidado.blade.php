<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte consolidado de egresados</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; }
        .encabezado { border-bottom: 2px solid #1d4ed8; padding-bottom: 10px; margin-bottom: 14px; }
        .encabezado .institucion { font-size: 9px; text-transform: uppercase; color: #475569; letter-spacing: 0.5px; }
        .encabezado .escuela { font-size: 11px; font-weight: bold; color: #1e3a8a; margin-top: 2px; }
        .encabezado h1 { font-size: 15px; margin: 8px 0 4px; color: #1e293b; }
        .metadatos { font-size: 9px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th {
            background-color: #1d4ed8; color: #ffffff; text-align: left;
            padding: 5px 6px; font-size: 9px;
        }
        tbody td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
        tbody tr:nth-child(even) { background-color: #f8fafc; }
        .pie { margin-top: 16px; font-size: 8px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="encabezado">
        <div class="institucion">Universidad Nacional Daniel Alcides Carrión (UNDAC)</div>
        <div class="escuela">Escuela de Formación Profesional de Ingeniería de Sistemas y Computación</div>
        <h1>Reporte consolidado de egresados</h1>
        <div class="metadatos">
            Generado el {{ $generadoEn->format('d/m/Y H:i') }} &middot; Emitido por: {{ $emitidoPor }}
        </div>
    </div>

    @if ($datos->isEmpty())
        <p>No se encontraron registros con los filtros aplicados.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Promoción</th>
                    <th>Grado</th>
                    <th>Situación</th>
                    <th>Rubro</th>
                    <th>Empresa</th>
                    <th>Cargo</th>
                    <th>Última actualización</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datos as $fila)
                    <tr>
                        <td>{{ $fila->nombres }}</td>
                        <td>{{ $fila->apellidos }}</td>
                        <td>{{ $fila->anio_egreso }}</td>
                        <td>{{ $fila->grado === 'titulado' ? 'Titulado(a)' : 'Bachiller' }}</td>
                        <td>{{ $fila->situacion ?? 'No registrada' }}</td>
                        <td>{{ $fila->rubro ?? '—' }}</td>
                        <td>{{ $fila->empresa ?? '—' }}</td>
                        <td>{{ $fila->cargo ?? '—' }}</td>
                        <td>{{ $fila->actualizado_en ? \Illuminate\Support\Carbon::parse($fila->actualizado_en)->format('d/m/Y') : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="pie">
        Alumni Connect EFPISC &mdash; Documento de uso interno, generado automáticamente.
    </div>
</body>
</html>
