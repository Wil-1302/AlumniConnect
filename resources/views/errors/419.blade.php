@extends('layouts.publico')

@section('titulo', 'Página expirada — Alumni Connect EFPISC')

@section('contenido')
    <div class="mx-auto max-w-md text-center">
        <p class="text-sm font-semibold text-institucional-700">Error 419</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">La página expiró</h1>
        <p class="mt-3 text-sm text-slate-600">
            Tu sesión quedó inactiva por mucho tiempo y la página se desactualizó.
            Vuelve a iniciar sesión e inténtalo otra vez.
        </p>
        <a href="{{ route('login') }}"
           class="mt-6 inline-block rounded-md bg-institucional-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-institucional-800">
            Iniciar sesión
        </a>
    </div>
@endsection
