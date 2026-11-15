<?php

namespace Database\Seeders\Concerns;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Utilidades compartidas por los seeders de demostración.
 * Permite generar fechas dentro de los últimos 6 meses y "retroceder"
 * los timestamps de los modelos para que los datos sean coherentes.
 */
trait SeedsDemoData
{
    protected function randomDate(CarbonInterface $from, CarbonInterface $to): Carbon
    {
        $fromTimestamp = $from->getTimestamp();
        $toTimestamp = max($fromTimestamp, $to->getTimestamp());

        return Carbon::createFromTimestamp(
            random_int($fromTimestamp, $toTimestamp),
            $from->getTimezone(),
        );
    }

    /**
     * Ajusta created_at (y updated_at cuando el modelo lo usa) a la fecha indicada.
     */
    protected function backdate(Model $model, CarbonInterface $at): void
    {
        $attributes = ['created_at' => $at];

        $updatedAtColumn = $model->getUpdatedAtColumn();
        if ($updatedAtColumn !== null) {
            $attributes[$updatedAtColumn] = $at;
        }

        $model->forceFill($attributes)->saveQuietly();
    }
}
