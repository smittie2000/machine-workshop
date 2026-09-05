<?php

namespace App\Models;

use App\Data\PartData;
use App\Enums\BodyType;
use App\Enums\ShapeType;
use App\Models\Concerns\GuardsCatalogueRelease;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Part extends Model
{
    use GuardsCatalogueRelease, HasFactory;

    protected $guarded = [];

    protected function validateDefinition(): void
    {
        $attributes = $this->getAttributes();
        PartData::validate([
            'key' => $attributes['key'] ?? null,
            'name' => $attributes['name'] ?? null,
            'materialKey' => $this->material()->firstOrFail()->key,
            'bodyType' => $attributes['body_type'] ?? null,
            'shapeType' => $attributes['shape_type'] ?? null,
            'radiusMm' => $attributes['radius_mm'] ?? null,
            'widthMm' => $attributes['width_mm'] ?? null,
            'heightMm' => $attributes['height_mm'] ?? null,
            'massG' => $attributes['mass_g'] ?? null,
            'visualKey' => $attributes['visual_key'] ?? null,
        ]);
    }

    protected function casts(): array
    {
        return ['body_type' => BodyType::class, 'shape_type' => ShapeType::class,
            'radius_mm' => 'integer', 'width_mm' => 'integer', 'height_mm' => 'integer', 'mass_g' => 'integer'];
    }

    public function catalogueVersion(): BelongsTo
    {
        return $this->belongsTo(CatalogueVersion::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
