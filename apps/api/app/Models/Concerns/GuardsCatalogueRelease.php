<?php

namespace App\Models\Concerns;

use App\Models\CatalogueVersion;
use LogicException;

trait GuardsCatalogueRelease
{
    abstract protected function validateDefinition(): void;

    protected function incrementOrDecrement(mixed $column, mixed $amount, mixed $extra, mixed $method): never
    {
        throw new LogicException('Catalogue definitions must be changed through save or the importer.');
    }

    protected function incrementOrDecrementEach(array $columns, array $extra, string $method): never
    {
        throw new LogicException('Catalogue definitions must be changed through save or the importer.');
    }

    protected function lockDraftCatalogue(): void
    {
        if ($this->exists && $this->isDirty('catalogue_version_id')) {
            throw new LogicException('Definitions cannot move between releases.');
        }
        $catalogue = CatalogueVersion::query()->lockForUpdate()->findOrFail($this->catalogue_version_id);
        if ($catalogue->released_at !== null) {
            throw new LogicException('Released catalogue definitions cannot be changed.');
        }
    }

    public function save(array $options = []): bool
    {
        return $this->getConnection()->transaction(function () use ($options): bool {
            $this->lockDraftCatalogue();

            $this->validateDefinition();

            return parent::save($options);
        });
    }

    public function delete(): ?bool
    {
        return $this->getConnection()->transaction(function (): ?bool {
            $this->lockDraftCatalogue();

            return parent::delete();
        });
    }
}
