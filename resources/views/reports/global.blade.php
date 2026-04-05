@extends('layouts.app')

@section('title', 'Búsqueda global')
@section('header-title', 'Búsqueda global')

@section('content')
    <form id="global-search-form" class="deposito-card mb-6 space-y-4 rounded-xl p-6">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2">
                <label class="text-sm text-gray-400">Buscar por</label>
                <select name="type" class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">
                    <option value="product">Producto (código o nombre)</option>
                    <option value="technician">Técnico</option>
                    <option value="license_plate">Patente</option>
                    <option value="entry_code">Ticket de entrada</option>
                    <option value="exit_code">Ticket de salida</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="text-sm text-gray-400">Texto</label>
                <input type="search" name="q" required class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white" placeholder="Término...">
            </div>
            <div>
                <label class="text-sm text-gray-400">Desde</label>
                <input type="date" name="from" class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-sm text-gray-400">Hasta</label>
                <input type="date" name="to" class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-sm text-gray-400">Movimiento (historial)</label>
                <select name="movement" class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">
                    <option value="">—</option>
                    <option value="entry">Entrada</option>
                    <option value="exit">Salida</option>
                    <option value="updated">Modificación</option>
                </select>
            </div>
        </div>
        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">Buscar</button>
    </form>
    <div id="search-results"></div>
    @push('scripts')
    <script src="{{ asset('js/global-search.js') }}"></script>
    @endpush
@endsection
