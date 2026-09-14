@extends('layouts.admin')

@section('titulo', 'Importar padrón — Alumni Connect EFPISC')

@section('contenido')

    <a href="{{ route('admin.padron.index') }}" class="text-sm font-medium text-institucional-700 hover:underline">
        &larr; Volver al padrón
    </a>

    <h1 class="mt-2 text-2xl font-bold text-slate-900">Importar padrón de egresados</h1>
    <p class="mt-1 text-sm text-slate-500">RF-31 — carga masiva desde un archivo Excel o CSV.</p>

    <div class="mt-6 max-w-2xl rounded-lg border border-slate-200 bg-white p-5">
        <h2 class="text-sm font-semibold text-slate-800">Formato esperado del archivo</h2>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-600">
            <li>Formato: <strong>.xlsx</strong>, <strong>.xls</strong> o <strong>.csv</strong>, máximo 5 MB.</li>
            <li>
                La primera fila debe traer exactamente estas columnas, en cualquier orden:
                <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">dni</code>,
                <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">nombres</code>,
                <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">apellidos</code>,
                <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">anio_egreso</code>,
                <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">grado</code>.
            </li>
            <li><strong>dni</strong>: exactamente 8 dígitos.</li>
            <li><strong>grado</strong>: solo admite "bachiller" o "titulado".</li>
            <li><strong>anio_egreso</strong>: un año entre 1980 y el próximo año.</li>
            <li>Un DNI que ya existe en el padrón se omite: no se duplica ni se sobrescribe.</li>
            <li>Las filas con errores se reportan al final, pero no detienen la importación del resto.</li>
        </ul>
        <a href="{{ route('admin.padron.plantilla') }}"
           class="mt-4 inline-block rounded-md border border-institucional-700 px-4 py-2 text-sm font-semibold text-institucional-700 hover:bg-institucional-50">
            Descargar plantilla de ejemplo
        </a>
    </div>

    <form method="POST" action="{{ route('admin.padron.importar.guardar') }}" enctype="multipart/form-data"
          class="mt-6 max-w-2xl space-y-4 rounded-lg border border-slate-200 bg-white p-5">
        @csrf

        <div>
            <label for="archivo" class="block text-sm font-medium text-slate-700">Archivo del padrón</label>
            <input type="file" name="archivo" id="archivo" required accept=".xlsx,.xls,.csv"
                   class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                          {{ $errors->has('archivo') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
            @error('archivo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full rounded-md bg-institucional-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-institucional-800 sm:w-auto">
            Importar
        </button>
    </form>

@endsection
