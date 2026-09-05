<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\Part;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Part> */
class PartFactory extends Factory
{
    public function definition(): array
    {
        return ['material_id' => Material::factory(),
            'catalogue_version_id' => fn (array $attributes) => Material::findOrFail($attributes['material_id'])->catalogue_version_id,
            'key' => fake()->unique()->slug(2), 'name' => 'Test ball', 'body_type' => 'dynamic',
            'shape_type' => 'ball', 'radius_mm' => 120, 'width_mm' => null, 'height_mm' => null,
            'mass_g' => 620, 'visual_key' => 'basketball'];
    }
}
