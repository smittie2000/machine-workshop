<?php

namespace App\Actions;

use App\Data\CatalogueData;
use App\Models\CatalogueVersion;
use Illuminate\Support\Facades\DB;
use LogicException;

class ImportCatalogue
{
    /** Import the complete reviewed definition; omitted draft rows are removed. */
    public function handle(array $definition, bool $release = true): CatalogueVersion
    {
        $data = CatalogueData::validateAndCreate($definition);

        return DB::transaction(function () use ($data, $release): CatalogueVersion {
            $catalogue = CatalogueVersion::query()->firstOrCreate(['id' => $data->id], [
                'name' => $data->name, 'simulation_version' => $data->simulationVersion,
            ]);
            $catalogue = CatalogueVersion::query()->lockForUpdate()->findOrFail($catalogue->id);
            if ($catalogue->released_at !== null) {
                $expected = $data->toArray();
                usort($expected['materials'], fn ($a, $b) => strcmp($a['key'], $b['key']));
                usort($expected['parts'], fn ($a, $b) => strcmp($a['key'], $b['key']));
                if (CatalogueData::fromModel($catalogue)->toArray() !== $expected) {
                    throw new LogicException('Seed differs from the sealed catalogue; use a new release key.');
                }

                return $catalogue;
            }
            $catalogue->fill(['name' => $data->name, 'simulation_version' => $data->simulationVersion])->save();
            foreach ($catalogue->parts()->get() as $part) {
                $part->delete();
            }
            foreach ($catalogue->materials()->get() as $material) {
                $material->delete();
            }
            $materials = [];
            foreach ($data->materials as $material) {
                $materials[$material->key] = $catalogue->materials()->create($material->toArray())->id;
            }
            foreach ($data->parts as $part) {
                $catalogue->parts()->create([
                    'key' => $part->key, 'name' => $part->name,
                    'material_id' => $materials[$part->materialKey],
                    'body_type' => $part->bodyType, 'shape_type' => $part->shapeType,
                    'radius_mm' => $part->radiusMm, 'width_mm' => $part->widthMm,
                    'height_mm' => $part->heightMm, 'mass_g' => $part->massG, 'visual_key' => $part->visualKey,
                ]);
            }
            if ($release) {
                $catalogue->released_at = now();
                $catalogue->save();
            }

            return $catalogue;
        });
    }
}
