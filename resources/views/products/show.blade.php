@extends('layouts.app')

@section('title', $product->product_code)
@section('header-title', $product->product_code)

@section('content')
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('products.edit', $product) }}" class="rounded-lg bg-blue-600/80 px-3 py-2 text-sm">Editar</a>
        <a href="{{ route('entries.create', ['product_id' => $product->id]) }}" class="rounded-lg border border-dep-border px-3 py-2 text-sm">Registrar entrada</a>
        <a href="{{ route('exits.create', ['product_id' => $product->id]) }}" class="rounded-lg border border-dep-border px-3 py-2 text-sm">Registrar salida</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="deposito-card rounded-xl p-4 lg:col-span-1">
            @if($product->photo)
                <img src="{{ asset('storage/'.$product->photo) }}" class="w-full rounded-lg border border-dep-border" alt="">
            @endif
            <h1 class="mt-4 text-xl font-bold text-white">{{ $product->product_code }}</h1>
            <p class="text-gray-400">{{ $product->name ?? 'Sin nombre' }}</p>
            @php($st = $product->stock_status)
            <div class="mt-2">
                @if($st === 'ok')<span class="badge-pill bg-emerald-500/20 text-emerald-300">OK</span>
                @elseif($st === 'low')<span class="badge-pill bg-amber-500/20 text-amber-300">Stock bajo</span>
                @elseif($st === 'out')<span class="badge-pill bg-red-500/20 text-red-300">Sin stock</span>
                @else<span class="badge-pill bg-orange-500/20 text-orange-300">Con dañados</span>
                @endif
            </div>
        </div>
        <div class="deposito-card rounded-xl p-4 lg:col-span-2">
            <div class="grid gap-3 sm:grid-cols-4">
                <div class="rounded-lg bg-dep-bg/50 p-3">
                    <div class="text-xs text-gray-500">Disponibles</div>
                    <div class="text-2xl font-bold text-emerald-300">{{ $product->available_quantity }}</div>
                </div>
                <div class="rounded-lg bg-dep-bg/50 p-3">
                    <div class="text-xs text-gray-500">Dañados</div>
                    <div class="text-2xl font-bold text-amber-300">{{ $product->damaged_quantity }}</div>
                </div>
                <div class="rounded-lg bg-dep-bg/50 p-3">
                    <div class="text-xs text-gray-500">Total</div>
                    <div class="text-2xl font-bold text-white">{{ $product->total_quantity }}</div>
                </div>
                <div class="rounded-lg bg-dep-bg/50 p-3">
                    <div class="text-xs text-gray-500">Mínimo</div>
                    <div class="text-2xl font-bold text-gray-300">{{ $product->minimum_stock }}</div>
                </div>
            </div>

            <div class="mt-6 border-t border-dep-border pt-4">
                <h2 class="text-sm font-semibold text-gray-300">Actualizar unidades dañadas (total)</h2>
                <form action="{{ route('products.damaged', $product) }}" method="post" class="mt-2 flex flex-wrap items-end gap-2">
                    @csrf
                    @method('PATCH')
                    <input type="number" step="0.001" name="damaged_quantity" min="0" value="{{ $product->damaged_quantity }}" class="rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">
                    <button type="submit" id="btn-damaged" class="rounded-lg bg-amber-600 px-3 py-2 text-sm text-white">Confirmar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="deposito-card mt-8 rounded-xl p-4">
        <h2 class="mb-3 text-sm font-semibold text-gray-300">Historial</h2>
        <form method="get" class="mb-4 flex flex-wrap gap-2 text-sm">
            <select name="h_action" class="rounded-lg border border-dep-border bg-dep-bg px-2 py-1 text-white">
                <option value="">Tipo de movimiento</option>
                @foreach(['created','updated','entry','exit','damaged_marked','photo_updated'] as $at)
                    <option value="{{ $at }}" @selected(request('h_action')===$at)>{{ \App\Support\HistoryActionLabels::forFilterOption($at) }}</option>
                @endforeach
            </select>
            <input type="date" name="h_from" value="{{ request('h_from') }}" class="rounded-lg border border-dep-border bg-dep-bg px-2 py-1 text-white">
            <input type="date" name="h_to" value="{{ request('h_to') }}" class="rounded-lg border border-dep-border bg-dep-bg px-2 py-1 text-white">
            <button type="submit" class="rounded-lg bg-blue-600 px-3 py-1 text-white">Filtrar</button>
        </form>
        <ul class="space-y-3">
            @foreach($history as $h)
                <li class="flex gap-3 border-l-2 border-blue-500/40 pl-3 text-sm">
                    <span class="text-gray-500">{{ $h->created_at->format('d/m/Y H:i') }}</span>
                    <span class="font-mono text-xs text-blue-300" title="{{ $h->action_label_es }}">{{ $h->action_display }}</span>
                    <span class="text-gray-300">{{ $h->description }}</span>
                    @if($h->quantity_change !== null)
                        <span class="text-gray-500" title="Disponibles antes → después">{{ $h->quantity_before }} → {{ $h->quantity_after }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $history->links() }}</div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('btn-damaged')?.addEventListener('click', function (e) {
            e.preventDefault();
            const f = this.closest('form');
            Swal.fire({
                title: '¿Confirmar?',
                text: 'Se ajustarán disponibles y dañados según el total indicado.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí',
                background: '#1c2030',
                color: '#e5e7eb',
            }).then((r) => { if (r.isConfirmed) f.submit(); });
        });
    </script>
    @endpush
@endsection
