<?php

namespace App\Support;

/**
 * Etiquetas en español para tipos de historial (action_type se guarda en inglés por convención técnica).
 */
final class HistoryActionLabels
{
    public const MAP = [
        'created' => 'Alta',
        'updated' => 'Actualización',
        'entry' => 'Entrada',
        'exit' => 'Salida',
        'damaged_marked' => 'Ajuste de unidades dañadas',
        'photo_updated' => 'Cambio de foto',
    ];

    public static function spanish(string $actionType): string
    {
        return self::MAP[$actionType] ?? $actionType;
    }

    /** Ej.: exit (Salida) — útil para clientes que reconocen el código y la traducción. */
    public static function forDisplay(string $actionType): string
    {
        $es = self::spanish($actionType);

        return array_key_exists($actionType, self::MAP)
            ? "{$actionType} ({$es})"
            : $actionType;
    }

    /** Texto para filtros desplegables: español primero. */
    public static function forFilterOption(string $actionType): string
    {
        $es = self::spanish($actionType);

        return array_key_exists($actionType, self::MAP)
            ? "{$es} ({$actionType})"
            : $actionType;
    }
}
