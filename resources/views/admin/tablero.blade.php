@extends('layouts.admin')

@section('titulo', 'Tablero — Alumni Connect EFPISC')

@section('contenido')

    <h1 class="text-2xl font-bold text-slate-900">Tablero de indicadores</h1>
    <p class="mt-1 text-sm text-slate-500">Indicadores de empleabilidad de los egresados de la Escuela.</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Total de egresados registrados</p>
            <p class="mt-1 text-3xl font-bold text-institucional-700">{{ $indicadores['total_registrados'] }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Tasa de inserción laboral</p>
            <p class="mt-1 text-3xl font-bold text-institucional-700">{{ number_format($indicadores['tasa_insercion'], 1) }}%</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Ofertas laborales vigentes</p>
            <p class="mt-1 text-3xl font-bold text-institucional-700">{{ $ofertas_vigentes }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-slate-900">Distribución por rubro</h2>
            @if ($indicadores['por_rubro']->isEmpty())
                <p class="mt-3 text-sm text-slate-500">Aún no hay datos suficientes para mostrar este gráfico.</p>
            @else
                <canvas id="grafico-rubro" class="mt-4" role="img"
                        aria-label="Distribución de egresados por rubro"></canvas>
            @endif
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-slate-900">Distribución por promoción</h2>
            @if ($indicadores['por_promocion']->isEmpty())
                <p class="mt-3 text-sm text-slate-500">Aún no hay datos suficientes para mostrar este gráfico.</p>
            @else
                <canvas id="grafico-promocion" class="mt-4" role="img"
                        aria-label="Total de egresados y empleados por promoción"></canvas>
            @endif
        </div>
    </div>

    @if ($indicadores['por_rubro']->isNotEmpty() || $indicadores['por_promocion']->isNotEmpty())
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.1/chart.umd.min.js"></script>
        <script>
            (function () {
                var azulInstitucional = '#1d4ed8';
                var azulInstitucionalClaro = '#93c5fd';

                @if ($indicadores['por_rubro']->isNotEmpty())
                    new Chart(document.getElementById('grafico-rubro'), {
                        type: 'bar',
                        data: {
                            labels: @json($indicadores['por_rubro']->pluck('rubro')),
                            datasets: [{
                                label: 'Egresados',
                                data: @json($indicadores['por_rubro']->pluck('total')),
                                backgroundColor: azulInstitucional,
                            }],
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                        },
                    });
                @endif

                @if ($indicadores['por_promocion']->isNotEmpty())
                    new Chart(document.getElementById('grafico-promocion'), {
                        type: 'bar',
                        data: {
                            labels: @json($indicadores['por_promocion']->pluck('anio_egreso')),
                            datasets: [
                                {
                                    label: 'Total egresados',
                                    data: @json($indicadores['por_promocion']->pluck('total')),
                                    backgroundColor: azulInstitucionalClaro,
                                },
                                {
                                    label: 'Empleados',
                                    data: @json($indicadores['por_promocion']->pluck('empleados')),
                                    backgroundColor: azulInstitucional,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                        },
                    });
                @endif
            })();
        </script>
    @endif

@endsection
