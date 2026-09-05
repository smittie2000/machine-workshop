<?php

namespace App\Models;

use App\Data\CatalogueData;
use App\Enums\SimulationVersion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use LogicException;

class CatalogueVersion extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['released_at' => 'immutable_datetime', 'simulation_version' => SimulationVersion::class];
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }

    public function save(array $options = []): bool
    {
        return $this->getConnection()->transaction(function () use ($options): bool {
            if ($this->exists) {
                $current = static::query()->lockForUpdate()->findOrFail($this->getRawOriginal('id'));
                if ($current->released_at !== null || $this->isDirty('id')) {
                    throw new LogicException('Released catalogues cannot be edited; create another release.');
                }
            }
            Validator::make([
                'id' => $this->id,
                'name' => $this->name,
                'simulationVersion' => $this->getAttributes()['simulation_version'] ?? null,
            ], Arr::only(CatalogueData::rules(), ['id', 'name', 'simulationVersion']))->validate();
            if ($this->released_at !== null) {
                if (! $this->exists) {
                    throw new LogicException('Import a complete draft before releasing.');
                }
                CatalogueData::validateAndCreate(CatalogueData::fromModel($this)->toArray());
            }

            return parent::save($options);
        });
    }

    public function delete(): ?bool
    {
        return $this->getConnection()->transaction(function (): ?bool {
            $current = static::query()->lockForUpdate()->findOrFail($this->getRawOriginal('id'));
            if ($current->released_at !== null) {
                throw new LogicException('Released catalogues cannot be deleted.');
            }

            return parent::delete();
        });
    }
}
