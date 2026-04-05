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
        ?int $quantityChange = null,
        ?int $quantityBefore = null,
        ?int $quantityAfter = null,
        ?string $technicianName = null,
        ?string $licensePlate = null,
    ): ProductHistory {
        return $product->histories()->create([
            'action_type' => $actionType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'technician_name' => $technicianName,
            'license_plate' => $licensePlate,
            'quantity_change' => $quantityChange,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
        ]);
    }

    public function recordEntry(
        Product $product,
        int $referenceId,
        int $addedGood,
        int $addedDamaged,
        int $beforeAvailable,
        int $afterAvailable,
        int $beforeDamaged,
        int $afterDamaged,
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
        int $removed,
        int $beforeAvailable,
        int $afterAvailable,
        ?string $technicianName = null,
        ?string $licensePlate = null,
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
            $licensePlate,
        );
    }

    public function recordDamaged(
        Product $product,
        int $deltaDamaged,
        int $beforeAvailable,
        int $afterAvailable,
        int $beforeDamaged,
        int $afterDamaged,
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
