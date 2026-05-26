<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductHistory;

class HistoryService
{
    public function record(
        Product $product,
        string $actionType,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?float $quantityChange = null,
        ?float $quantityBefore = null,
        ?float $quantityAfter = null,
        ?string $technicianName = null,
        ?string $licensePlate = null,
        ?int $depositId = null,
    ): ProductHistory {
        return $product->histories()->create([
            'action_type' => $actionType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'technician_name' => $technicianName,
            'license_plate' => $licensePlate,
            'deposit_id' => $depositId,
            'quantity_change' => $quantityChange,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
        ]);
    }

    public function recordEntry(
        Product $product,
        int $referenceId,
        float $addedGood,
        float $addedDamaged,
        float $beforeAvailable,
        float $afterAvailable,
        float $beforeDamaged,
        float $afterDamaged,
    ): ProductHistory {
        $desc = "Entrada: +{$addedGood} disponibles".($addedDamaged > 0 ? ", +{$addedDamaged} dañados" : '');

        return $this->record(
            $product,
            'entry',
            $desc,
            'ProductEntry',
            $referenceId,
            $addedGood,
            $beforeAvailable,
            $afterAvailable,
        );
    }

    public function recordExit(
        Product $product,
        int $referenceId,
        float $removed,
        float $beforeAvailable,
        float $afterAvailable,
        ?string $technicianName = null,
        ?int $depositId = null,
    ): ProductHistory {
        return $this->record(
            $product,
            'exit',
            "Salida: -{$removed} unidades",
            'ProductExit',
            $referenceId,
            -$removed,
            $beforeAvailable,
            $afterAvailable,
            $technicianName,
            null,
            $depositId,
        );
    }

    public function recordDamaged(
        Product $product,
        float $deltaDamaged,
        float $beforeAvailable,
        float $afterAvailable,
        float $beforeDamaged,
        float $afterDamaged,
    ): ProductHistory {
        return $this->record(
            $product,
            'damaged_marked',
            "Ajuste unidades dañadas: disponibles {$beforeAvailable} → {$afterAvailable}, dañados {$beforeDamaged} → {$afterDamaged}",
            null,
            null,
            -$deltaDamaged,
            $beforeAvailable,
            $afterAvailable,
        );
    }
}
