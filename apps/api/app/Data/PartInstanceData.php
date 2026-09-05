<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class PartInstanceData extends Data
{
    public function __construct(
        public string $id,
        public string $partKey,
        public int $xMm,
        public int $yMm,
        public int $rotationMilliDegrees,
    ) {}

    public static function rules(): array
    {
        return [
            'id' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/D'],
            'partKey' => ['required', 'string', 'max:64'],
            'xMm' => ['required', 'integer:strict', 'between:-50000,50000'],
            'yMm' => ['required', 'integer:strict', 'between:-50000,50000'],
            'rotationMilliDegrees' => ['required', 'integer:strict', 'between:0,359999'],
        ];
    }
}
