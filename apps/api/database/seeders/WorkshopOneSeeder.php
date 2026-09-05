<?php

namespace Database\Seeders;

use App\Actions\ImportCatalogue;
use Illuminate\Database\Seeder;

class WorkshopOneSeeder extends Seeder
{
    /**
     * Frozen release snapshot. New parts or changed values belong in a new release.
     * Starter placements and goals belong in config/machine-workshop.php.
     */
    public static function definition(): array
    {
        return [
            'id' => 'workshop-1',
            'name' => 'Workshop parts',
            'simulationVersion' => 'material-demo-1',
            'materials' => self::materials(),
            'parts' => self::parts(),
        ];
    }

    public function run(ImportCatalogue $import): void
    {
        $import->handle(self::definition());
    }

    /** @return list<array{key: string, name: string, friction: float, restitution: float}> */
    private static function materials(): array
    {
        return [
            [
                'key' => 'basketball-surface',
                'name' => 'Basketball surface',
                'friction' => 0.50,
                'restitution' => 1.00,
            ],
            [
                'key' => 'brick',
                'name' => 'Brick',
                'friction' => 0.60,
                'restitution' => 0.85,
            ],
            [
                'key' => 'wood',
                'name' => 'Wood',
                'friction' => 0.40,
                'restitution' => 0.60,
            ],
            [
                'key' => 'rubber-mat',
                'name' => 'Rubber mat',
                'friction' => 0.90,
                'restitution' => 0.25,
            ],
        ];
    }

    /** @return list<array{key: string, name: string, materialKey: string, bodyType: string, shapeType: string, radiusMm: ?int, widthMm: ?int, heightMm: ?int, massG: ?int, visualKey: string}> */
    private static function parts(): array
    {
        return [
            [
                'key' => 'basketball',
                'name' => 'Basketball',
                'materialKey' => 'basketball-surface',
                'bodyType' => 'dynamic',
                'shapeType' => 'ball',
                'radiusMm' => 120,
                'widthMm' => null,
                'heightMm' => null,
                'massG' => 620,
                'visualKey' => 'basketball',
            ],
            [
                'key' => 'platform-brick',
                'name' => 'Brick platform',
                'materialKey' => 'brick',
                'bodyType' => 'fixed',
                'shapeType' => 'cuboid',
                'radiusMm' => null,
                'widthMm' => 3000,
                'heightMm' => 300,
                'massG' => null,
                'visualKey' => 'platform-brick',
            ],
            [
                'key' => 'platform-wood',
                'name' => 'Wood platform',
                'materialKey' => 'wood',
                'bodyType' => 'fixed',
                'shapeType' => 'cuboid',
                'radiusMm' => null,
                'widthMm' => 3000,
                'heightMm' => 300,
                'massG' => null,
                'visualKey' => 'platform-wood',
            ],
            [
                'key' => 'platform-rubber',
                'name' => 'Rubber mat platform',
                'materialKey' => 'rubber-mat',
                'bodyType' => 'fixed',
                'shapeType' => 'cuboid',
                'radiusMm' => null,
                'widthMm' => 3000,
                'heightMm' => 300,
                'massG' => null,
                'visualKey' => 'platform-rubber',
            ],
        ];
    }
}
