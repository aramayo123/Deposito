@extends('layouts.app')

@section('title', 'Salidas')
@section('header-title', 'Tickets de salida')

@section('content')
    <div class="mb-4 flex flex-wrap gap-2">
        <form method="get" class="flex flex-wrap gap-2 text-sm">
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-dep-border bg-dep-card px-2 py-1 text-white">
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-dep-border bg-dep-card px-2 py-1 text-white">
            <input type="text" name="technician" value="{{ request('technician') }}" placeholder="Técnico" class="rounded-lg border border-dep-border bg-dep-card px-2 py-1 text-white">
            <input type="text" name="license_plate" value="{{ request('license_plate') }}" placeholder="Patente" class="rounded-lg border border-dep-border bg-dep-card px-2 py-1 text-white">
            <input type="text" name="product" value="{{ request('product') }}" placeholder="Producto" class="rounded-lg border border-dep-border bg-dep-card px-2 py-1 text-white">
            <button type="submit" class="rounded-lg bg-blue-600 px-3 py-1 text-white">Filtrar</button>
        </form>
        <a href="{{ route('exits.create') }}" class="ml-auto rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white">Nueva salida</a>
    </div>
    <div class="deposito-card overflow-hidden rounded-xl">
        <table class="deposito-table w-full text-left text-sm">
            <thead class="border-b border-dep-border text-xs uppercase text-gray-500">
                <tr>
                    <th class="p-3">Código</th>
                    <th class="p-3">Fecha</th>
                    <th class="p-3">Técnico / Taller</th>
                    <th class="p-3">Patente</th>
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
                        <td class="p-3">{{ $x->license_plate ?? '—' }}</td>
                        <td class="p-3">{{ $x->items_count }}</td>
                        <td class="p-3"><a href="{{ route('exits.show', $x) }}" class="text-blue-400 hover:underline">Ver</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-dep-border p-3">{{ $exits->links() }}</div>
    </div>
@endsection
