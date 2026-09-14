{{-- Zona de mensajes flash: éxito (verde) y errores (rojo, único uso del color rojo). --}}
<div class="mx-auto max-w-5xl px-4 pt-4 sm:px-6 lg:px-8">
    @if (session('exito'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">
            {{ session('exito') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
            <p class="font-semibold">Se encontraron los siguientes problemas:</p>
            <ul class="mt-1 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $mensaje)
                    <li>{{ $mensaje }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
