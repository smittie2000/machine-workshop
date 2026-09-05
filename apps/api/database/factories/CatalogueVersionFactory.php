<?php

namespace Database\Factories;

use App\Enums\SimulationVersion;
use App\Models\CatalogueVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CatalogueVersion> */
class CatalogueVersionFactory extends Factory
{
    public function definition(): array
    {
        return ['id' => fake()->unique()->slug(3), 'name' => 'Draft catalogue',
            'simulation_version' => SimulationVersion::MaterialDemoOne, 'released_at' => null];
    }
}
