@extends('layouts.app')

@section('title', 'Entradas')
@section('header-title', 'Tickets de entrada')

@section('content')
    <div class="mb-4 flex flex-wrap gap-2">
        <form method="get" class="flex flex-wrap gap-2 text-sm">
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-dep-border bg-dep-card px-2 py-1 text-white">
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-dep-border bg-dep-card px-2 py-1 text-white">
            <input type="text" name="entry_code" value="{{ request('entry_code') }}" placeholder="Código ticket" class="rounded-lg border border-dep-border bg-dep-card px-2 py-1 text-white">
            <input type="text" name="product" value="{{ request('product') }}" placeholder="Producto" class="rounded-lg border border-dep-border bg-dep-card px-2 py-1 text-white">
            <button type="submit" class="rounded-lg bg-blue-600 px-3 py-1 text-white">Filtrar</button>
        </form>
        <a href="{{ route('entries.create') }}" class="ml-auto rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white">Nueva entrada</a>
    </div>
    <div class="deposito-card overflow-hidden rounded-xl">
        <table class="deposito-table w-full text-left text-sm">
            <thead class="border-b border-dep-border text-xs uppercase text-gray-500">
                <tr>
                    <th class="p-3">Código</th>
                    <th class="p-3">Fecha</th>
                    <th class="p-3">Hora</th>
                    <th class="p-3">Ítems</th>
                    <th class="p-3">Productos</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dep-border/60">
                @foreach($entries as $e)
                    <tr>
                        <td class="p-3 font-mono text-blue-300">{{ $e->entry_code }}</td>
                        <td class="p-3">{{ $e->entry_date?->format('d/m/Y') }}</td>
                        <td class="p-3">{{ $e->entry_time }}</td>
                        <td class="p-3">{{ $e->items_count }}</td>
                        <td class="p-3 text-xs text-gray-400">
                            {{ $e->items->take(3)->pluck('product.product_code')->filter()->implode(', ') }}
                        </td>
                        <td class="p-3"><a href="{{ route('entries.show', $e) }}" class="text-blue-400 hover:underline">Ver</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-dep-border p-3">{{ $entries->links() }}</div>
    </div>
@endsection
