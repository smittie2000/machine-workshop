<?php

namespace App\Data;

use App\Enums\SimulationVersion;
use App\Models\CatalogueVersion;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Data;

class CatalogueData extends Data
{
    /** @param MaterialData[] $materials
     * @param  PartData[]  $parts
     */
    public function __construct(
        public string $id,
        public string $name,
        public SimulationVersion $simulationVersion,
        public array $materials,
        public array $parts,
    ) {}

    public static function fromModel(CatalogueVersion $catalogue): self
    {
        return new self(
            $catalogue->id, $catalogue->name, $catalogue->simulation_version,
            $catalogue->materials()->get()->sortBy('key', SORT_STRING)->values()->map(fn ($material) => new MaterialData(
                $material->key, $material->name, (float) $material->friction, (float) $material->restitution,
            ))->all(),
            $catalogue->parts()->with('material')->get()->sortBy('key', SORT_STRING)->values()->map(fn ($part) => new PartData(
                $part->key, $part->name, $part->material->key, $part->body_type, $part->shape_type,
                $part->radius_mm, $part->width_mm, $part->height_mm, $part->mass_g, $part->visual_key,
            ))->all(),
        );
    }

    public static function rules(): array
    {
        return [
            'id' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/D'],
            'name' => ['required', 'string', 'max:120'],
            'simulationVersion' => ['required', Rule::enum(SimulationVersion::class)],
            'materials' => ['required', 'array', 'list', 'min:1'],
            'parts' => ['required', 'array', 'list', 'min:1'],
        ];
    }

    public static function withValidator(Validator $validator): void
    {
        // Add collection key rules after Spatie expands its nested DTO rules.
        $validator->addRules([
            'materials.*' => ['required', 'array:key,name,friction,restitution'],
            'materials.*.key' => ['distinct:strict'],
            'parts.*' => ['required', 'array:key,name,materialKey,bodyType,shapeType,radiusMm,widthMm,heightMm,massG,visualKey'],
            'parts.*.key' => ['distinct:strict'],
        ]);
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $input = $validator->getData();
            foreach (array_diff(array_keys($input), ['id', 'name', 'simulationVersion', 'materials', 'parts']) as $key) {
                $validator->errors()->add($key, 'Unknown catalogue field.');
            }
            $materials = array_column($input['materials'], 'key');
            foreach ($input['parts'] as $index => $part) {
                if (! in_array($part['materialKey'], $materials, true)) {
                    $validator->errors()->add("parts.$index.materialKey", 'Material must belong to this release.');
                }
            }
        });
    }
}
