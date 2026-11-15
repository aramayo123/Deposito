# Commit propuesto

> Este archivo contiene el mensaje de commit completo. Podés usarlo con:
>
> ```bash
> git add .
> git commit -F COMMIT_SEEDERS.md
> ```

---

```text
feat(seeders): datos demo con Faker, ruta /run-seeders y credenciales de prueba

Se reemplaza el seeding mínimo anterior por un conjunto completo de datos de
demostración generados con Faker, cubriendo todos los modelos y relaciones del
sistema. Todas las fechas se calculan al ejecutar el seeder y quedan dentro de
los últimos 6 meses (productos, entradas, salidas, historiales y alertas).

Cambios principales:

- Nuevo trait Database\Seeders\Concerns\SeedsDemoData con utilidades para
  generar fechas aleatorias dentro del rango y retroceder created_at/updated_at
  de cada modelo.
- UserSeeder: usuarios fijos de prueba y 12 usuarios Faker.
- DepositSeeder: 8 depósitos con fechas dentro del rango.
- ProductSeeder: 60 productos activos/inactivos, algunos con stock bajo o
  agotado, dañados y 3 productos con SoftDeletes.
- ProductHistorySeeder: altas, actualizaciones y cambios de foto en el
  historial de cada producto.
- ProductMovementSeeder: línea temporal cronológica de entradas, salidas y
  ajustes por daño, con ítems múltiples, depósitos, técnicos, salidas a taller
  y movimientos del día actual. Incluye salidas finales para dejar productos
  en estado bajo/agotado y que existan alertas reales.
- StockAlertSeeder: alertas low_stock y out_of_stock, leídas y no leídas,
  dentro de los últimos 14 días.
- DatabaseSeeder: orquesta todos los seeders dentro de una transacción para
  que la ejecución sea rápida y atómica.
- Nuevo RunSeedersController y ruta GET /run-seeders que ejecuta
  migrate:fresh --seed y devuelve un resumen de los registros generados.
- Seguridad de /run-seeders: sin token solo funciona desde localhost en
  local/testing; en cualquier otro caso requiere SEEDERS_TOKEN y ?token=...
  para evitar borrados accidentales.
- Credenciales de prueba publicadas en /login:
  admin@deposito.com / admin123 y operador@deposito.com / operador123.
- Documentación actualizada en README.md y .env.example (SEEDERS_TOKEN).

Modelos y relaciones cubiertos:

- User
- Deposit -> ProductExit
- Product -> ProductEntryItem, ProductExitItem, ProductHistory, StockAlert
- ProductEntry -> ProductEntryItem, productos mediante hasManyThrough
- ProductEntryItem -> ProductEntry, Product
- ProductExit -> ProductExitItem, Deposit
- ProductExitItem -> ProductExit, Product
- ProductHistory -> Product, Deposit
- StockAlert -> Product

Uso:

  http://localhost:8000/run-seeders
  https://TU-DOMINIO.com/run-seeders?token=TU_SECRETO

  php artisan migrate:fresh --seed

Nota: la ruta /run-seeders borra todos los datos actuales de la base de datos.
```
