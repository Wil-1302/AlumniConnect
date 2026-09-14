@extends('layouts.admin')

@section('titulo', 'Publicar oferta — Alumni Connect EFPISC')

@section('contenido')

    <a href="{{ route('admin.ofertas.index') }}" class="text-sm font-medium text-institucional-700 hover:underline">
        &larr; Volver a ofertas laborales
    </a>

    <h1 class="mt-2 text-2xl font-bold text-slate-900">Publicar nueva oferta laboral</h1>

    <form method="POST" action="{{ route('admin.ofertas.guardar') }}" class="mt-6 max-w-2xl space-y-5">
        @csrf

        <div>
            <label for="titulo" class="block text-sm font-medium text-slate-700">Título del puesto</label>
            <input type="text" name="titulo" id="titulo" required maxlength="150"
                   value="{{ old('titulo') }}"
                   class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                          {{ $errors->has('titulo') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
            @error('titulo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="empresa" class="block text-sm font-medium text-slate-700">Empresa</label>
            <input type="text" name="empresa" id="empresa" required maxlength="150"
                   value="{{ old('empresa') }}"
                   class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                          {{ $errors->has('empresa') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
            @error('empresa')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="rubro_id" class="block text-sm font-medium text-slate-700">
                    Rubro <span class="font-normal text-slate-400">(opcional)</span>
                </label>
                <select name="rubro_id" id="rubro_id"
                        class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                               {{ $errors->has('rubro_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                    <option value="">Sin especificar</option>
                    @foreach ($rubros as $rubro)
                        <option value="{{ $rubro->id }}" @selected((int) old('rubro_id') === $rubro->id)>
                            {{ $rubro->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('rubro_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="modalidad" class="block text-sm font-medium text-slate-700">Modalidad</label>
                <select name="modalidad" id="modalidad" required
                        class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                               {{ $errors->has('modalidad') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
                    <option value="">Seleccione</option>
                    <option value="presencial" @selected(old('modalidad') === 'presencial')>Presencial</option>
                    <option value="remoto" @selected(old('modalidad') === 'remoto')>Remoto</option>
                    <option value="hibrido" @selected(old('modalidad') === 'hibrido')>Híbrido</option>
                </select>
                @error('modalidad')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="descripcion" class="block text-sm font-medium text-slate-700">Descripción</label>
            <textarea name="descripcion" id="descripcion" rows="4" required
                      class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                             {{ $errors->has('descripcion') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">{{ old('descripcion') }}</textarea>
            @error('descripcion')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="requisitos" class="block text-sm font-medium text-slate-700">
                Requisitos <span class="font-normal text-slate-400">(opcional)</span>
            </label>
            <textarea name="requisitos" id="requisitos" rows="3"
                      class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                             {{ $errors->has('requisitos') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">{{ old('requisitos') }}</textarea>
            @error('requisitos')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="max-w-xs">
            <label for="fecha_cierre" class="block text-sm font-medium text-slate-700">Fecha de cierre</label>
            <input type="date" name="fecha_cierre" id="fecha_cierre" required
                   min="{{ now()->toDateString() }}"
                   value="{{ old('fecha_cierre') }}"
                   class="mt-1 block w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-1
                          {{ $errors->has('fecha_cierre') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-institucional-500 focus:ring-institucional-500' }}">
            <p class="mt-1 text-xs text-slate-500">No puede ser una fecha anterior a hoy.</p>
            @error('fecha_cierre')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="rounded-md bg-institucional-700 px-4 py-2 text-sm font-semibold text-white hover:bg-institucional-800">
                Publicar oferta
            </button>
            <a href="{{ route('admin.ofertas.index') }}"
               class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                Cancelar
            </a>
        </div>
    </form>

@endsection
