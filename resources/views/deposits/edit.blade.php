@extends('layouts.app')

@section('title', 'Editar depósito')
@section('header-title', 'Editar depósito')

@section('content')
    <form action="{{ route('deposits.update', $deposit) }}" method="post" class="deposito-card max-w-xl space-y-4 rounded-xl p-6">
        @csrf
        @method('PUT')
        <div>
            <label for="name" class="text-sm text-gray-400">Nombre <span class="text-red-400">*</span></label>
            <input name="name" id="name" required value="{{ old('name', $deposit->name) }}" maxlength="255"
                   placeholder="Nombre del depósito"
                   class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white placeholder-gray-600">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white">Guardar cambios</button>
            <a href="{{ route('deposits.show', $deposit) }}" class="rounded-lg border border-dep-border px-4 py-2 text-sm text-gray-300">Cancelar</a>
        </div>
    </form>
@endsection
