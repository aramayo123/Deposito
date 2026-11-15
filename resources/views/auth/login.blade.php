<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión — Depósito</title>
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
            <p class="text-sm text-gray-500">Sistema de gestión</p>
        </div>

        <div class="deposito-card rounded-xl p-6">
            <h2 class="mb-6 text-lg font-semibold text-white">Iniciar sesión</h2>

            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm text-red-200">
                    <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="post" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm text-gray-400">Correo electrónico</label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                           class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white"
                           placeholder="correo@ejemplo.com" autofocus>
                </div>
                <div>
                    <label class="text-sm text-gray-400">Contraseña</label>
                    <input type="password" name="password" required
                           class="mt-1 w-full rounded-lg border border-dep-border bg-dep-bg px-3 py-2 text-sm text-white"
                           placeholder="••••••••">
                </div>
                <div>
                    <label class="flex items-center gap-2 text-sm text-gray-400">
                        <input type="checkbox" name="remember" class="rounded border-dep-border">
                        Recordarme
                    </label>
                </div>
                <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500">
                    Ingresar
                </button>
            </form>

            <div class="mt-5 rounded-lg border border-blue-500/20 bg-blue-500/5 p-4 text-sm">
                <p class="font-medium text-blue-300">Credenciales de prueba</p>
                <ul class="mt-2 space-y-1 text-gray-400">
                    <li>
                        Administrador:
                        <code class="rounded bg-dep-bg px-1.5 py-0.5 text-gray-200">admin@deposito.com</code>
                        /
                        <code class="rounded bg-dep-bg px-1.5 py-0.5 text-gray-200">admin123</code>
                    </li>
                    <li>
                        Operador:
                        <code class="rounded bg-dep-bg px-1.5 py-0.5 text-gray-200">operador@deposito.com</code>
                        /
                        <code class="rounded bg-dep-bg px-1.5 py-0.5 text-gray-200">operador123</code>
                    </li>
                </ul>
            </div>

            <p class="mt-4 text-center text-sm text-gray-500">
                ¿No tenés cuenta? <a href="{{ route('register') }}" class="text-blue-400 hover:underline">Registrate</a>
            </p>
        </div>
    </div>
</body>
</html>
