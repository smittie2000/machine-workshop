<?php

namespace App\Models;

use App\Data\MaterialData;
use App\Models\Concerns\GuardsCatalogueRelease;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class Material extends Model
{
    use GuardsCatalogueRelease, HasFactory;

    protected $guarded = [];

    protected function validateDefinition(): void
    {
        // Validate stored inputs before decimal casts can round coefficients.
        MaterialData::validate(Arr::only($this->getAttributes(), ['key', 'name', 'friction', 'restitution']));
    }

    protected function casts(): array
    {
        return ['friction' => 'decimal:5', 'restitution' => 'decimal:5'];
    }

    public function catalogueVersion(): BelongsTo
    {
        return $this->belongsTo(CatalogueVersion::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }
}
