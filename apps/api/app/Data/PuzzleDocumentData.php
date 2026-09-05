<?php

namespace App\Data;

use App\Models\CatalogueVersion;
use App\Rules\NullValue;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Data;

class PuzzleDocumentData extends Data
{
    /** @param PartInstanceData[] $instances
     * @param  string[]  $lockedInstanceIds
     * @param  InventoryItemData[]  $inventory
     */
    public function __construct(
        public int $schemaVersion,
        public string $catalogueVersion,
        public array $instances,
        public array $lockedInstanceIds,
        public array $inventory,
        public ?RegionGoalData $goal,
    ) {}

    public static function rules(): array
    {
        return [
            'schemaVersion' => ['required', 'integer:strict', 'in:1'],
            'catalogueVersion' => ['required', 'string', 'max:64'],
            'instances' => ['present', 'array', 'list', 'max:200'],
            'lockedInstanceIds' => ['present', 'array', 'list', 'size:0'],
            'inventory' => ['present', 'array', 'list', 'size:0'],
            'goal' => ['present', new NullValue],
        ];
    }

    public static function withValidator(Validator $validator): void
    {
        $validator->addRules([
            'instances.*' => ['required', 'array:id,partKey,xMm,yMm,rotationMilliDegrees'],
            'instances.*.id' => ['distinct:strict'],
        ]);
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $input = $validator->getData();
            foreach (array_diff(array_keys($input), ['schemaVersion', 'catalogueVersion', 'instances', 'lockedInstanceIds', 'inventory', 'goal']) as $key) {
                $validator->errors()->add($key, 'Unknown document field.');
            }
            if ($input['goal'] !== null) {
                $validator->errors()->add('goal', 'Only sandbox documents are accepted.');
            }
            $catalogue = CatalogueVersion::query()->whereNotNull('released_at')->find($input['catalogueVersion']);
            if (! $catalogue) {
                $validator->errors()->add('catalogueVersion', 'A released catalogue is required.');

                return;
            }
            $keys = $catalogue->parts()->pluck('key')->all();
            foreach ($input['instances'] as $index => $instance) {
                if (! in_array($instance['partKey'], $keys, true)) {
                    $validator->errors()->add("instances.$index.partKey", 'Part must belong to the selected catalogue.');
                }
            }
        });
    }
}
