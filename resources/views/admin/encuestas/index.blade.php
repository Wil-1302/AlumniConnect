@extends('layouts.admin')

@section('titulo', 'Encuestas — Alumni Connect EFPISC')

@section('contenido')

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Encuestas de seguimiento</h1>
        <a href="{{ route('admin.encuestas.crear') }}"
           class="w-full rounded-md bg-institucional-700 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-institucional-800 sm:w-auto">
            Crear nueva encuesta
        </a>
    </div>

    @if ($encuestas->isEmpty())
        <div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
            <p class="text-sm font-medium text-slate-700">Todavía no se ha creado ninguna encuesta.</p>
            <p class="mt-1 text-sm text-slate-500">Usa el botón "Crear nueva encuesta" para publicar la primera.</p>
        </div>
    @else
        <div class="mt-6 overflow-x-auto rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Título</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Vigencia</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Preguntas</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Respuestas</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Estado</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-slate-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($encuestas as $encuesta)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $encuesta->titulo }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $encuesta->fecha_inicio->format('d/m/Y') }} &ndash; {{ $encuesta->fecha_fin->format('d/m/Y') }}
                                @if ($encuesta->vigente)
                                    <span class="ml-1 inline-block rounded-full bg-institucional-50 px-2 py-0.5 text-xs font-medium text-institucional-700">
                                        En curso
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $encuesta->preguntas_count }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $encuesta->respuestas_count }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium
                                             {{ $encuesta->activa ? 'bg-institucional-50 text-institucional-700' : 'bg-slate-200 text-slate-500' }}">
                                    {{ $encuesta->activa ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('admin.encuestas.resultados', $encuesta->id) }}"
                                       class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                        Resultados
                                    </a>
                                    @if ($encuesta->activa)
                                        <form method="POST" action="{{ route('admin.encuestas.desactivar', $encuesta->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                                Desactivar
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.encuestas.activar', $encuesta->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-md bg-institucional-700 px-3 py-1.5 text-xs font-medium text-white hover:bg-institucional-800">
                                                Activar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $encuestas->links() }}
        </div>
    @endif

@endsection
