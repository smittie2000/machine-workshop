<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

class RegionGoalData extends Data
{
    public function __construct(
        #[Max(64)] public string $objectId,
        #[Between(-50000, 50000)] public int $xMm,
        #[Between(-50000, 50000)] public int $yMm,
        #[Between(1, 100000)] public int $widthMm,
        #[Between(1, 100000)] public int $heightMm,
        #[In(30)] public int $consecutiveTicks,
    ) {}
}
