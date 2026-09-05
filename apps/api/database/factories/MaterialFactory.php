<?php

namespace Database\Factories;

use App\Models\CatalogueVersion;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Material> */
class MaterialFactory extends Factory
{
    public function definition(): array
    {
        return ['catalogue_version_id' => CatalogueVersion::factory(), 'key' => fake()->unique()->slug(2),
            'name' => 'Test surface', 'friction' => '0.50000', 'restitution' => '0.60000'];
    }
}
