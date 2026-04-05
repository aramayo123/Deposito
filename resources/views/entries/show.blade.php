@extends('layouts.app')

@section('title', $entry->entry_code)
@section('header-title', $entry->entry_code)

@section('content')
    <div class="deposito-card mb-6 rounded-xl p-4 text-sm">
        <p><span class="text-gray-500">Fecha:</span> {{ $entry->entry_date?->format('d/m/Y') }} {{ $entry->entry_time }}</p>
        @if($entry->notes)<p class="mt-2 text-gray-400">{{ $entry->notes }}</p>@endif
    </div>
    <div class="deposito-card rounded-xl p-4">
        <h2 class="mb-3 text-sm font-semibold">Ítems</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs text-gray-500">
                    <tr>
                        <th class="p-2">Foto</th>
                        <th class="p-2">Código</th>
                        <th class="p-2">Nombre</th>
                        <th class="p-2">Recibidos</th>
                        <th class="p-2">Dañados</th>
                        <th class="p-2">Notas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dep-border/60">
                    @foreach($entry->items as $it)
                        @php($p = $it->product)
                        <tr>
                            <td class="p-2">
                                @if($p->photo)
                                    <img src="{{ asset('storage/'.$p->photo) }}" class="h-10 w-10 rounded object-cover" alt="">
                                @else
                                    <span class="text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="p-2 font-mono text-blue-300">{{ $p->product_code }}</td>
                            <td class="p-2 text-gray-400">{{ $p->name ?? '—' }}</td>
                            <td class="p-2">{{ $it->quantity_received }}</td>
                            <td class="p-2">{{ $it->quantity_damaged }}</td>
                            <td class="p-2 text-xs text-gray-500">{{ $it->damage_notes }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <a href="{{ route('entries.index') }}" class="mt-4 inline-block text-sm text-blue-400 hover:underline">← Volver</a>
@endsection
