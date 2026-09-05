<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

class InventoryItemData extends Data
{
    public function __construct(
        #[Max(64)] public string $partKey,
        #[IntegerType, Between(0, 200)] public int $quantity,
    ) {}
}
