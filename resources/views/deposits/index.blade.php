@extends('layouts.app')

@section('title', 'Depósitos')
@section('header-title', 'Depósitos')

@push('breadcrumb')
    <span>Depósitos</span>
@endpush

@section('content')
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <form method="get" class="flex flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar depósito..."
                   class="rounded-lg border border-dep-border bg-dep-card px-3 py-2 text-sm text-white placeholder-gray-500">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white">Filtrar</button>
        </form>
        <a href="{{ route('deposits.create') }}" class="ml-auto rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white">Nuevo depósito</a>
    </div>

    <div class="deposito-card overflow-hidden rounded-xl">
        <div class="overflow-x-auto">
            <table class="deposito-table w-full text-left text-sm">
                <thead class="border-b border-dep-border text-xs uppercase text-gray-500">
                    <tr>
                        <th class="p-3">Nombre</th>
                        <th class="p-3">Fecha creación</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dep-border/60">
                    @forelse($deposits as $d)
                        <tr>
                            <td class="p-3 font-medium text-white">{{ $d->name }}</td>
                            <td class="p-3 text-gray-400">{{ $d->created_at->format('d/m/Y') }}</td>
                            <td class="p-3">
                                <a href="{{ route('deposits.show', $d) }}" class="text-blue-400 hover:underline">Ver</a>
                                <a href="{{ route('deposits.edit', $d) }}" class="ml-2 text-amber-400 hover:underline">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-6 text-center text-gray-500">No se encontraron depósitos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-dep-border p-3">{{ $deposits->links() }}</div>
    </div>
@endsection
