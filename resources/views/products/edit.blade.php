@extends('layouts.app')

@section('title', 'Editar '.$product->product_code)
@section('header-title', 'Editar producto')

@section('content')
    <form action="{{ route('products.update', $product) }}" method="post" enctype="multipart/form-data" class="deposito-card max-w-2xl space-y-4 rounded-xl p-6">
        @csrf
        @method('PUT')
        <div>
            <label for="product_code" class="group inline-flex cursor-help items-center gap-1.5 text-sm text-gray-400"
                   title="Código único en todo el depósito. Hasta 50 caracteres. Recomendado: mayúsculas, números y guiones, sin espacios. Ej.: TAL-001, MECH-SET-12.">
                Código <span class="text-red-400">*</span>
                <span class="rounded border border-blue-500/40 bg-blue-500/10 px-1 text-[10px] font-medium uppercase tracking-wide text-blue-300 opacity-80 group-hover:opacity-100">Ayuda</span>
            </label>
            <input name="product_code" id="product_code" required value="{{ old('product_code', $product->product_code) }}"
                   data-exclude-id="{{ $product->id }}"
                   maxlength="50" autocomplete="off"
                   class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">
            <p id="code-hint" class="mt-1 text-xs text-gray-500"></p>
        </div>
        <div>
            <label class="text-sm text-gray-400">Nombre</label>
            <input name="name" value="{{ old('name', $product->name) }}" class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">
        </div>
        <div>
            <label class="text-sm text-gray-400">Foto</label>
            @if($product->photo)
                <div class="mb-2"><img src="{{ asset('storage/'.$product->photo) }}" class="h-24 rounded border border-dep-border" alt=""></div>
            @endif
            <div id="drop" class="mt-1 cursor-pointer rounded-lg border border-dashed border-dep-border p-6 text-center text-sm text-gray-500">
                Nueva foto (opcional)
                <input type="file" name="photo" id="photo" accept="image/*" class="hidden">
            </div>
            <img id="preview" class="mt-2 hidden max-h-40 rounded-lg border border-dep-border">
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="text-sm text-gray-400">Disponibles</label>
                <input type="number" step="0.001" name="available_quantity" min="0" required value="{{ old('available_quantity', $product->available_quantity) }}"
                       class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-sm text-gray-400">Dañados</label>
                <input type="number" step="0.001" name="damaged_quantity" min="0" value="{{ old('damaged_quantity', $product->damaged_quantity) }}"
                       class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-sm text-gray-400">Stock mínimo</label>
                <input type="number" step="0.001" name="minimum_stock" min="0" required value="{{ old('minimum_stock', $product->minimum_stock) }}"
                       class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white">
            </div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white">Actualizar</button>
            <a href="{{ route('products.show', $product) }}" class="rounded-lg border border-dep-border px-4 py-2 text-sm text-gray-300">Volver</a>
        </div>
    </form>
    @push('scripts')
    <script src="{{ asset('js/product-form.js') }}"></script>
    @endpush
@endsection
