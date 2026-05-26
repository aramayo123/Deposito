@extends('layouts.app')

@section('title', $deposit->name)
@section('header-title', $deposit->name)

@section('content')
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="deposito-card rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-white">{{ $totalExitsCount }}</div>
            <div class="text-xs text-gray-500">Salidas totales</div>
        </div>
        <div class="deposito-card rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-blue-300">{{ $totalProductsSent }}</div>
            <div class="text-xs text-gray-500">Productos distintos enviados</div>
        </div>
        <div class="deposito-card rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-emerald-300">{{ $history->count() }}</div>
            <div class="text-xs text-gray-500">Movimientos registrados</div>
        </div>
    </div>

    <div class="mb-4 flex gap-2">
        <a href="{{ route('deposits.edit', $deposit) }}" class="rounded-lg bg-amber-600 px-4 py-2 text-sm text-white">Editar</a>
        <form action="{{ route('deposits.destroy', $deposit) }}" method="post" onsubmit="return confirm('¿Eliminar este depósito? Las salidas asociadas no se eliminarán.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm text-white">Eliminar</button>
        </form>
        <a href="{{ route('deposits.index') }}" class="rounded-lg border border-dep-border px-4 py-2 text-sm text-gray-300">Volver</a>
    </div>

    @if($exits->isNotEmpty())
        <div class="deposito-card mb-6 rounded-xl p-4">
            <h2 class="mb-3 text-sm font-semibold text-gray-300">Últimas salidas hacia este depósito</h2>
            <table class="deposito-table w-full text-left text-sm">
                <thead class="border-b border-dep-border text-xs uppercase text-gray-500">
                    <tr>
                        <th class="p-3">Código</th>
                        <th class="p-3">Fecha</th>
                        <th class="p-3">Técnico / Taller</th>
                        <th class="p-3">Ítems</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dep-border/60">
                    @foreach($exits as $x)
                        <tr>
                            <td class="p-3 font-mono text-blue-300">{{ $x->exit_code }}</td>
                            <td class="p-3">{{ $x->exit_date?->format('d/m/Y') }} {{ $x->exit_time }}</td>
                            <td class="p-3">
                                @if($x->is_for_workshop)
                                    <span class="badge-pill bg-amber-500/20 text-amber-300">Uso taller</span>
                                @else
                                    {{ $x->technician_name ?? '—' }}
                                @endif
                            </td>
                            <td class="p-3">{{ $x->items_count }}</td>
                            <td class="p-3"><a href="{{ route('exits.show', $x) }}" class="text-blue-400 hover:underline">Ver</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($history->isNotEmpty())
        <div class="deposito-card rounded-xl p-4">
            <h2 class="mb-3 text-sm font-semibold text-gray-300">Historial de movimientos</h2>
            <div class="space-y-2 text-sm">
                @foreach($history as $h)
                    @php($prod = $h->product)
                    <div class="rounded-lg border border-dep-border/40 border-l-2 border-l-blue-500/40 px-3 py-2">
                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0">
                            <span class="font-medium text-gray-200">{{ $h->action_display }}</span>
                            <span class="text-gray-500">·</span>
                            @if($prod)
                                <a href="{{ route('products.show', $prod) }}" class="text-blue-400 hover:underline">{{ $prod->product_code }}</a>
                                <span class="text-gray-400">— {{ $prod->name ?? '—' }}</span>
                            @endif
                        </div>
                        @if($h->description)
                            <div class="mt-1 text-xs text-gray-400">{{ $h->description }}</div>
                        @endif
                        <div class="mt-1 flex flex-wrap gap-x-2 gap-y-0 text-[11px] text-gray-500">
                            <span>{{ $h->created_at->format('d/m/Y H:i') }}</span>
                            @if($h->quantity_change !== null)
                                <span>Cant.: {{ $h->quantity_change > 0 ? '+' : '' }}{{ $h->quantity_change }} → {{ $h->quantity_after }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($exits->isEmpty() && $history->isEmpty())
        <div class="deposito-card rounded-xl p-6 text-center text-gray-500">
            Aún no hay movimientos registrados hacia este depósito.
        </div>
    @endif
@endsection
