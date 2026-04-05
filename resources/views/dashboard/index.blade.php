@extends('layouts.app')

@section('title', 'Dashboard')
@section('header-title', 'Dashboard')

@push('breadcrumb')
    <span>Inicio / Dashboard</span>
@endpush

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @php($cards = [
            ['Productos', $metrics['products_count'], 'text-blue-300', null],
            ['Disponibles', $metrics['available_sum'], 'text-emerald-300', null],
            ['Dañados', $metrics['damaged_sum'], 'text-amber-300', null],
            ['Stock bajo', $metrics['low_stock_count'], 'text-amber-400', route('products.index', ['stock' => 'low'])],
            ['Entradas hoy', $metrics['entries_today'], 'text-cyan-300', null],
            ['Salidas hoy', $metrics['exits_today'], 'text-violet-300', null],
        ])
        @foreach($cards as $card)
            @php([$label, $value, $color, $href] = array_pad($card, 4, null))
            <div class="deposito-card rounded-xl p-4 deposito-transition {{ $href ? 'cursor-pointer hover:border-blue-500/40' : '' }}"
                 @if($href) onclick="window.location='{{ $href }}'" @endif>
                <div class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</div>
                <div class="mt-1 text-2xl font-bold {{ $color }}">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="deposito-card rounded-xl p-4">
            <h2 class="mb-3 text-sm font-semibold text-gray-300">Alertas de stock (no leídas)</h2>
            <ul class="space-y-2 text-sm">
                @forelse($alerts as $a)
                    <li class="flex items-center justify-between rounded-lg border border-dep-border/60 px-3 py-2">
                        <span>{{ $a->product->product_code }} — {{ $a->product->name ?? '—' }}</span>
                        <form action="{{ route('stock-alerts.read', $a) }}" method="post" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs text-blue-400 hover:underline">Marcar leída</button>
                        </form>
                    </li>
                @empty
                    <li class="text-gray-500">Sin alertas pendientes.</li>
                @endforelse
            </ul>
        </div>
        <div class="deposito-card rounded-xl p-4 min-w-0">
            <h2 class="mb-3 text-sm font-semibold text-gray-300">Movimientos últimos 7 días</h2>
            <div class="chart-wrap w-full max-w-full min-w-0 overflow-hidden">
                <canvas id="movChart" class="block h-40 w-full min-w-0 max-w-full" height="160" aria-label="Gráfico de movimientos"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="deposito-card rounded-xl p-4">
            <h2 class="mb-3 text-sm font-semibold text-gray-300">Últimas entradas</h2>
            <ul class="space-y-2 text-sm">
                @foreach($recentEntries as $e)
                    <li><a href="{{ route('entries.show', $e) }}" class="text-blue-400 hover:underline">{{ $e->entry_code }}</a> — {{ $e->entry_date?->format('d/m/Y') }} ({{ $e->items_count }} ítems)</li>
                @endforeach
            </ul>
        </div>
        <div class="deposito-card rounded-xl p-4">
            <h2 class="mb-3 text-sm font-semibold text-gray-300">Últimas salidas</h2>
            <ul class="space-y-2 text-sm">
                @foreach($recentExits as $x)
                    <li><a href="{{ route('exits.show', $x) }}" class="text-blue-400 hover:underline">{{ $x->exit_code }}</a> — {{ $x->exit_date?->format('d/m/Y') }}
                        @if($x->is_for_workshop)<span class="badge-pill ml-2 bg-amber-500/20 text-amber-300">Taller</span>@else{{ $x->technician_name }}@endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const ctx = document.getElementById('movChart');
            if (!ctx) return;
            const labels = @json($chart['labels']);
            const eData = @json($chart['entries']);
            const xData = @json($chart['exits']);
            const wrap = ctx.closest('.chart-wrap');
            const H = 160;
            const bottomPad = 22;
            const n = labels.length;

            function draw() {
                if (!n) return;
                const rawW = wrap
                    ? wrap.getBoundingClientRect().width
                    : ctx.parentElement.getBoundingClientRect().width;
                const w = Math.max(120, Math.floor(rawW));
                const dpr = Math.min(window.devicePixelRatio || 1, 2);
                ctx.style.width = '100%';
                ctx.style.maxWidth = '100%';
                ctx.style.height = H + 'px';
                ctx.width = Math.floor(w * dpr);
                ctx.height = Math.floor(H * dpr);
                const c = ctx.getContext('2d');
                c.setTransform(dpr, 0, 0, dpr, 0, 0);
                const pad = w < 360 ? 8 : 16;
                const chartH = H - bottomPad - pad;
                const max = Math.max(1, ...eData, ...xData);
                const barW = Math.max(2, (w - pad * 2) / (n * 3) - 1);
                c.fillStyle = '#2a3048';
                c.fillRect(0, 0, w, H);
                labels.forEach(function (lb, i) {
                    if (i >= eData.length || i >= xData.length) return;
                    const x0 = pad + i * barW * 3;
                    const eh = (eData[i] / max) * chartH;
                    const xh = (xData[i] / max) * chartH;
                    c.fillStyle = '#3b82f6';
                    c.fillRect(x0, pad + chartH - eh, barW, eh);
                    c.fillStyle = '#10b981';
                    c.fillRect(x0 + barW + 2, pad + chartH - xh, barW, xh);
                    c.fillStyle = '#9ca3af';
                    c.font = '10px Inter,sans-serif';
                    c.textAlign = 'center';
                    c.fillText(lb, x0 + barW, H - 5);
                });
                c.textAlign = 'start';
            }

            draw();
            if (typeof ResizeObserver !== 'undefined' && wrap) {
                new ResizeObserver(draw).observe(wrap);
            } else {
                window.addEventListener('resize', draw);
            }
        })();
    </script>
    @endpush
@endsection
