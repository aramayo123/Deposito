@extends('layouts.app')

@section('title', $exit->exit_code)
@section('header-title', $exit->exit_code)

@section('content')
    <div class="deposito-card mb-6 rounded-xl p-4 text-sm">
        <p><span class="text-gray-500">Fecha:</span> {{ $exit->exit_date?->format('d/m/Y') }} {{ $exit->exit_time }}</p>
        @if($exit->is_for_workshop)
            <p class="mt-2"><span class="badge-pill bg-amber-500/20 text-amber-300">Uso del taller</span></p>
        @else
            <p class="mt-2"><span class="text-gray-500">Técnico:</span> {{ $exit->technician_name ?? '—' }}</p>
            <p><span class="text-gray-500">Depósito:</span> {{ $exit->deposit?->name ?? '—' }}</p>
        @endif
        @if($exit->notes)<p class="mt-2 text-gray-400">{{ $exit->notes }}</p>@endif
    </div>
    <div class="deposito-card rounded-xl p-4">
        <h2 class="mb-3 text-sm font-semibold">Ítems</h2>
        <table class="w-full text-left text-sm">
            <thead class="text-xs text-gray-500">
                <tr>
                    <th class="p-2">Foto</th>
                    <th class="p-2">Código</th>
                    <th class="p-2">Nombre</th>
                    <th class="p-2">Cantidad</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dep-border/60">
                @foreach($exit->items as $it)
                    @php($p = $it->product)
                    <tr>
                        <td class="p-2">
                            @if($p->photo)
                                <img src="{{ asset('storage/'.$p->photo) }}" class="h-10 w-10 rounded object-cover" alt="">
                            @else
                                —
                            @endif
                        </td>
                        <td class="p-2 font-mono text-blue-300">{{ $p->product_code }}</td>
                        <td class="p-2 text-gray-400">{{ $p->name ?? '—' }}</td>
                        <td class="p-2">{{ $it->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <a href="{{ route('exits.index') }}" class="mt-4 inline-block text-sm text-blue-400 hover:underline">← Volver</a>

    @if(session('exit_skipped'))
        @push('scripts')
        <script>
            Swal.fire({
                title: 'Resultado de la salida',
                html: '{!! implode("", array_map(function($p) { return "<div style=\'color:#4ade80;margin:2px 0;text-align:left;font-size:13px;\'>✓ " . e($p["code"]) . " — " . e($p["name"]) . " (Cant: " . $p["quantity"] . ")</div>"; }, session('exit_processed', []))) !!}' +
                      '{!! implode("", array_map(function($s) { return "<div style=\'color:#f87171;margin:2px 0;text-align:left;font-size:13px;\'>✗ " . e($s["code"]) . " — " . e($s["name"]) . " — Pide: " . $s["requested"] . " / Stock: " . $s["available"] . "</div>"; }, session('exit_skipped', []))) !!}',
                icon: 'warning',
                background: '#1c2030',
                color: '#e5e7eb',
                confirmButtonText: 'Entendido',
                customClass: { popup: 'rounded-xl' }
            });
        </script>
        @endpush
    @endif
@endsection
