<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registro — Depósito</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dep: {
                            bg: '#0f1117',
                            sidebar: '#151820',
                            card: '#1c2030',
                            border: '#2a3048',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="flex min-h-screen items-center justify-center bg-dep-bg text-gray-200">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <span class="text-3xl text-blue-400">●</span>
            <h1 class="mt-2 text-2xl font-bold text-white">Depósito</h1>
            <p class="text-sm text-gray-500">Crear cuenta</p>
        </div>

        <div class="deposito-card rounded-xl p-6">
            <h2 class="mb-6 text-lg font-semibold text-white">Registro</h2>

            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm text-red-200">
                    <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="post" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm text-gray-400">Nombre</label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white"
                           placeholder="Tu nombre" autofocus>
                </div>
                <div>
                    <label class="text-sm text-gray-400">Correo electrónico</label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                           class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white"
                           placeholder="correo@ejemplo.com">
                </div>
                <div>
                    <label class="text-sm text-gray-400">Contraseña</label>
                    <input type="password" name="password" required
                           class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white"
                           placeholder="Mínimo 6 caracteres">
                </div>
                <div>
                    <label class="text-sm text-gray-400">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" required
                           class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white"
                           placeholder="Repetí la contraseña">
                </div>
                <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">
                    Crear cuenta
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-gray-500">
                ¿Ya tenés cuenta? <a href="{{ route('login') }}" class="text-blue-400 hover:underline">Iniciá sesión</a>
            </p>
        </div>
    </div>
</body>
</html>
