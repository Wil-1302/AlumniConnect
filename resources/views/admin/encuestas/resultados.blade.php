@extends('layouts.admin')

@section('titulo', 'Resultados — ' . $encuesta->titulo)

@section('contenido')

    <a href="{{ route('admin.encuestas.index') }}" class="text-sm font-medium text-institucional-700 hover:underline">
        &larr; Volver a encuestas
    </a>

    @php $totalRespuestas = $resultados->first()->total ?? 0; @endphp

    <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $encuesta->titulo }}</h1>
    <p class="mt-1 text-sm text-slate-500">
        {{ $encuesta->fecha_inicio->format('d/m/Y') }} &ndash; {{ $encuesta->fecha_fin->format('d/m/Y') }}
        · {{ $totalRespuestas }} {{ $totalRespuestas === 1 ? 'respuesta' : 'respuestas' }} recibidas
    </p>

    @if ($totalRespuestas === 0)
        <div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
            <p class="text-sm font-medium text-slate-700">Esta encuesta todavía no tiene respuestas.</p>
            <p class="mt-1 text-sm text-slate-500">Los resultados aparecerán aquí en cuanto los egresados respondan.</p>
        </div>
    @else
        <div class="mt-6 space-y-6">
            @foreach ($resultados as $resultado)
                <section class="rounded-lg border border-slate-200 bg-white p-5">
                    <h2 class="font-semibold text-slate-900">{{ $resultado->pregunta->enunciado }}</h2>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $resultado->pregunta->tipo === 'escala' ? 'Pregunta de escala (1 a 5)' : 'Pregunta de opción múltiple' }}
                        · {{ $resultado->total }} {{ $resultado->total === 1 ? 'respuesta' : 'respuestas' }}
                        @if ($resultado->promedio !== null)
                            · Promedio: <strong>{{ $resultado->promedio }}</strong>
                        @endif
                    </p>

                    <div class="mt-4 space-y-2">
                        @if ($resultado->pregunta->tipo === 'escala')
                            @for ($valor = 1; $valor <= 5; $valor++)
                                @php
                                    $cantidad = $resultado->distribucion->get($valor, 0);
                                    $porcentaje = $resultado->total > 0 ? round($cantidad / $resultado->total * 100) : 0;
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-xs text-slate-600">
                                        <span>{{ $valor }}</span>
                                        <span>{{ $cantidad }} ({{ $porcentaje }}%)</span>
                                    </div>
                                    <div class="mt-0.5 h-2 w-full rounded-full bg-slate-100">
                                        <div class="h-2 rounded-full bg-institucional-600" style="width: {{ $porcentaje }}%"></div>
                                    </div>
                                </div>
                            @endfor
                        @else
                            @foreach ($resultado->distribucion as $texto => $cantidad)
                                @php
                                    $porcentaje = $resultado->total > 0 ? round($cantidad / $resultado->total * 100) : 0;
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-xs text-slate-600">
                                        <span>{{ $texto }}</span>
                                        <span>{{ $cantidad }} ({{ $porcentaje }}%)</span>
                                    </div>
                                    <div class="mt-0.5 h-2 w-full rounded-full bg-slate-100">
                                        <div class="h-2 rounded-full bg-institucional-600" style="width: {{ $porcentaje }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    @endif

@endsection
