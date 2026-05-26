@extends('layouts.app')

@section('title', 'Nueva salida')
@section('header-title', 'Nueva salida')

@section('content')
    <form action="{{ route('exits.store') }}" method="post" class="deposito-card space-y-6 rounded-xl p-6">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-sm text-gray-400">Fecha</label>
                <input type="date" name="exit_date" required value="{{ old('exit_date', now()->toDateString()) }}"
                       class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-sm text-gray-400">Hora</label>
                <input type="time" name="exit_time" required value="{{ old('exit_time', now()->format('H:i')) }}"
                       class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">
            </div>
            <div class="sm:col-span-2">
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox" name="is_for_workshop" id="is_for_workshop" value="1" class="rounded border-dep-border"
                           @checked(old('is_for_workshop'))>
                    Es para uso del taller
                </label>
            </div>
            <div id="tech-fields" class="sm:col-span-2 grid gap-4 sm:grid-cols-2 deposito-transition">
                <div>
                    <label class="text-sm text-gray-400">Técnico</label>
                    <input type="text" name="technician_name" value="{{ old('technician_name') }}"
                           class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">
                </div>
                <div>
                    <label class="text-sm text-gray-400">Depósito destino</label>
                    <select name="deposit_id" class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">
                        <option value="">— Sin depósito —</option>
                        @foreach($deposits as $d)
                            <option value="{{ $d->id }}" @selected(old('deposit_id') == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="sm:col-span-2">
                <label class="text-sm text-gray-400">Notas</label>
                <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-300">Ítems</h2>
                <button type="button" id="add-item" class="rounded-lg bg-blue-600 px-3 py-1 text-xs text-white">+ Agregar</button>
            </div>
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-gray-500">
                    <tr>
                        <th class="p-2">Producto</th>
                        <th class="p-2">Cantidad</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="items-body"></tbody>
            </table>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white">Guardar ticket</button>
            <a href="{{ route('exits.index') }}" class="rounded-lg border border-dep-border px-4 py-2 text-sm text-gray-300">Cancelar</a>
        </div>
    </form>

    <template id="item-row-tpl">
        <tr class="border-t border-dep-border/40">
            <td class="relative p-2">
                <input type="hidden" name="items[__IDX__][product_id]" class="product-id" required>
                <input type="text" class="product-search w-full rounded border border-dep-border bg-dep-bg px-2 py-1 text-xs text-white" placeholder="Buscar" autocomplete="off">
                <div class="autocomplete absolute z-10 mt-1 hidden max-h-40 w-full overflow-auto rounded border border-dep-border bg-dep-card shadow-lg"></div>
                <div class="stock-hint mt-1 text-[10px] text-gray-500"></div>
            </td>
            <td class="p-2">
                <input type="number" step="0.001" name="items[__IDX__][quantity]" min="0.001" required class="qty-out w-24 rounded border border-dep-border bg-dep-bg px-2 py-1 text-xs text-white">
            </td>
            <td class="p-2"><button type="button" class="btn-remove text-red-400">✕</button></td>
        </tr>
    </template>

    @push('scripts')
    <script>window.__EXIT_PRESET__ = @json($presetProductId);</script>
    <script src="{{ asset('js/exit-form.js') }}"></script>
    @endpush
@endsection
