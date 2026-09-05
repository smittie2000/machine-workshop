<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class MaterialData extends Data
{
    public function __construct(
        public string $key,
        public string $name,
        public float $friction,
        public float $restitution,
    ) {}

    public static function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/D'],
            'name' => ['required', 'string', 'max:120'],
            'friction' => ['required', 'numeric', 'between:0,2', 'decimal:0,5'],
            'restitution' => ['required', 'numeric', 'between:0,1', 'decimal:0,5'],
        ];
    }
}
