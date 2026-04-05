@extends('layouts.app')

@section('title', 'Productos')
@section('header-title', 'Productos')

@push('breadcrumb')
    <span>Productos</span>
@endpush

@section('content')
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <form method="get" class="flex flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Código o nombre"
                   class="rounded-lg border border-dep-border bg-dep-card px-3 py-2 text-sm text-white placeholder-gray-500">
            <select name="stock" class="rounded-lg border border-dep-border bg-dep-card px-3 py-2 text-sm text-white">
                <option value="">Estado stock</option>
                <option value="low" @selected(request('stock')==='low')>Stock bajo</option>
                <option value="out" @selected(request('stock')==='out')>Sin stock</option>
                <option value="damaged" @selected(request('stock')==='damaged')>Con dañados</option>
            </select>
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="dir" value="{{ $dir }}">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500">Filtrar</button>
        </form>
        <a href="{{ route('products.create') }}" class="ml-auto rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Nuevo producto</a>
    </div>

    <div class="deposito-card overflow-hidden rounded-xl">
        <div class="overflow-x-auto">
            <table class="deposito-table w-full text-left text-sm">
                <thead class="border-b border-dep-border text-xs uppercase text-gray-500">
                    <tr>
                        <th class="p-3"></th>
                        @foreach(['product_code' => 'Código', 'name' => 'Nombre', 'available_quantity' => 'Disp.', 'damaged_quantity' => 'Dañ.', 'minimum_stock' => 'Mín.'] as $col => $lab)
                            <th class="p-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => $col, 'dir' => $sort === $col && $dir === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-blue-400">{{ $lab }}</a>
                            </th>
                        @endforeach
                        <th class="p-3">Estado</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dep-border/60">
                    @foreach($products as $p)
                        @php($st = $p->stock_status)
                        <tr class="deposito-transition">
                            <td class="p-2">
                                @if($p->photo)
                                    <img src="{{ asset('storage/'.$p->photo) }}" alt="" class="h-10 w-10 rounded object-cover">
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded bg-dep-border text-xs text-gray-500">—</div>
                                @endif
                            </td>
                            <td class="p-3 font-mono text-blue-300">{{ $p->product_code }}</td>
                            <td class="p-3 text-gray-400">{{ $p->name ?? '—' }}</td>
                            <td class="p-3">{{ $p->available_quantity }}</td>
                            <td class="p-3">{{ $p->damaged_quantity }}</td>
                            <td class="p-3">{{ $p->minimum_stock }}</td>
                            <td class="p-3">
                                @if($st === 'ok')<span class="badge-pill bg-emerald-500/20 text-emerald-300">OK</span>
                                @elseif($st === 'low')<span class="badge-pill bg-amber-500/20 text-amber-300">Bajo</span>
                                @elseif($st === 'out')<span class="badge-pill bg-red-500/20 text-red-300">Sin stock</span>
                                @else<span class="badge-pill bg-orange-500/20 text-orange-300">Dañados</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <a href="{{ route('products.show', $p) }}" class="text-blue-400 hover:underline">Ver</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-dep-border p-3">{{ $products->links() }}</div>
    </div>
@endsection
