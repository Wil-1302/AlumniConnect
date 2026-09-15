@extends('layouts.publico')

@section('titulo', 'Página no encontrada — Alumni Connect EFPISC')

@section('contenido')
    <div class="mx-auto max-w-md text-center">
        <p class="text-sm font-semibold text-institucional-700">Error 404</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Página no encontrada</h1>
        <p class="mt-3 text-sm text-slate-600">
            La página que buscas no existe o fue movida. Verifica la dirección
            e inténtalo de nuevo.
        </p>
        <a href="{{ route('inicio') }}"
           class="mt-6 inline-block rounded-md bg-institucional-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-institucional-800">
            Volver al inicio
        </a>
    </div>
@endsection
