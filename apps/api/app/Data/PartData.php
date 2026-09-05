<?php

namespace App\Data;

use App\Enums\BodyType;
use App\Enums\ShapeType;
use App\Rules\NullValue;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class PartData extends Data
{
    public const VISUAL_KEYS = ['basketball', 'platform-brick', 'platform-wood', 'platform-rubber'];

    public function __construct(
        public string $key,
        public string $name,
        public string $materialKey,
        public BodyType $bodyType,
        public ShapeType $shapeType,
        public ?int $radiusMm,
        public ?int $widthMm,
        public ?int $heightMm,
        public ?int $massG,
        public string $visualKey,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        $ball = ($context->payload['shapeType'] ?? null) === 'ball';
        $dynamic = ($context->payload['bodyType'] ?? null) === 'dynamic';
        $dimension = ['required', 'integer:strict', 'between:1,10000'];

        return [
            'key' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/D'],
            'name' => ['required', 'string', 'max:120'],
            'materialKey' => ['required', 'string', 'max:64'],
            'bodyType' => ['required', Rule::enum(BodyType::class)],
            'shapeType' => ['required', Rule::enum(ShapeType::class)],
            'radiusMm' => $ball ? $dimension : ['present', new NullValue],
            'widthMm' => $ball ? ['present', new NullValue] : $dimension,
            'heightMm' => $ball ? ['present', new NullValue] : $dimension,
            'massG' => $dynamic ? ['required', 'integer:strict', 'between:1,100000'] : ['present', new NullValue],
            'visualKey' => ['required', Rule::in(self::VISUAL_KEYS)],
        ];
    }
}
