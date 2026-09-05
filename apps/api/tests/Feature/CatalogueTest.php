<?php

namespace Tests\Feature;

use App\Actions\ImportCatalogue;
use App\Data\CatalogueData;
use App\Models\CatalogueVersion;
use App\Models\Material;
use App\Models\Part;
use Database\Seeders\WorkshopOneSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_is_exact_idempotent_and_public_dto_uses_numbers(): void
    {
        $this->seed();
        $before = [CatalogueVersion::first()->getAttributes(), Material::all()->toArray(), Part::all()->toArray()];
        $this->seed();
        $this->assertSame($before, [CatalogueVersion::first()->getAttributes(), Material::all()->toArray(), Part::all()->toArray()]);
        $expected = CatalogueData::validateAndCreate(WorkshopOneSeeder::definition())->toArray();
        usort($expected['materials'], fn ($a, $b) => strcmp($a['key'], $b['key']));
        usort($expected['parts'], fn ($a, $b) => strcmp($a['key'], $b['key']));
        $response = $this->getJson('/api/v1/catalogues/workshop-1')->assertOk()->assertExactJson($expected);
        foreach ($response->json('materials') as $material) {
            $this->assertIsNumeric($material['friction']);
            $this->assertFalse(is_string($material['friction']));
            $this->assertFalse(is_string($material['restitution']));
        }
        $this->assertDatabaseCount('materials', 4);
        $this->assertDatabaseCount('parts', 4);
        $this->assertDatabaseCount('catalogue_versions', 1);
        $this->assertDatabaseMissing('catalogue_versions', ['id' => 'prototype-1']);
        $this->assertSame(['basketball', 'platform-brick', 'platform-rubber', 'platform-wood'], array_column($response->json('parts'), 'key'));
    }

    public function test_draft_import_can_be_replaced_then_sealed_and_draft_is_not_public(): void
    {
        $definition = WorkshopOneSeeder::definition();
        $import = new ImportCatalogue;
        $draft = $import->handle($definition, release: false);
        $this->getJson('/api/v1/catalogues/workshop-1')->assertNotFound();
        $definition['materials'][1]['restitution'] = 0.8;
        $import->handle($definition, release: false);
        $this->assertSame('0.80000', $draft->materials()->where('key', 'brick')->first()->restitution);
        $import->handle($definition);
        $this->getJson('/api/v1/catalogues/workshop-1')->assertOk();
        $this->getJson('/api/v1/catalogues/missing')->assertNotFound();
    }

    public function test_changed_released_seed_is_refused_without_writes(): void
    {
        $this->seed(WorkshopOneSeeder::class);
        $before = CatalogueData::fromModel(CatalogueVersion::first())->toArray();
        $definition = WorkshopOneSeeder::definition();
        $definition['materials'][1]['restitution'] = 0.8;
        try {
            (new ImportCatalogue)->handle($definition);
            $this->fail('Released seed was changed.');
        } catch (LogicException) {
            $this->assertSame($before, CatalogueData::fromModel(CatalogueVersion::first())->toArray());
        }
    }

    #[DataProvider('releasedOperations')]
    public function test_released_model_writes_are_refused(string $operation): void
    {
        $import = new ImportCatalogue;
        $draft = $import->handle(WorkshopOneSeeder::definition(), release: false);
        $staleMaterial = $draft->materials()->first();
        $staleCatalogue = clone $draft;
        $import->handle(WorkshopOneSeeder::definition());
        $this->expectException(LogicException::class);
        match ($operation) {
            'catalogue-update' => CatalogueVersion::first()->update(['name' => 'Changed']),
            'catalogue-unseal' => CatalogueVersion::first()->update(['released_at' => null]),
            'catalogue-delete' => CatalogueVersion::first()->delete(),
            'material-update' => Material::first()->update(['friction' => 1]),
            'material-delete' => Material::first()->delete(),
            'material-increment' => Material::first()->increment('friction', 0.1),
            'part-increment' => Part::first()->incrementEach(['radius_mm' => 1]),
            'part-update' => Part::first()->update(['radius_mm' => 200]),
            'part-delete' => Part::first()->delete(),
            'material-create' => Material::factory()->create(['catalogue_version_id' => $draft->id]),
            'part-create' => Part::factory()->create(['catalogue_version_id' => $draft->id, 'material_id' => Material::first()->id]),
            'stale-material' => $staleMaterial->update(['name' => 'Stale change']),
            'stale-catalogue' => $staleCatalogue->update(['name' => 'Stale change']),
        };
    }

    public static function releasedOperations(): array
    {
        return array_map(fn ($operation) => [$operation], [
            'catalogue-update', 'catalogue-unseal', 'catalogue-delete', 'material-update', 'material-delete',
            'part-update', 'part-delete', 'material-create', 'part-create', 'stale-material', 'stale-catalogue',
            'material-increment', 'part-increment',
        ]);
    }

    #[DataProvider('invalidDefinitions')]
    public function test_import_validates_before_writing(string $path, mixed $value): void
    {
        $definition = WorkshopOneSeeder::definition();
        data_set($definition, $path, $value);
        try {
            (new ImportCatalogue)->handle($definition);
            $this->fail('Invalid import accepted.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('catalogue_versions', 0);
        }
    }

    public static function invalidDefinitions(): array
    {
        return [
            ['parts.0.radiusMm', null], ['parts.0.widthMm', 20], ['parts.0.massG', null],
            ['parts.0.widthMm', ''], ['parts.1.massG', 0], ['parts.1.massG', false],
            ['parts.1.radiusMm', 20], ['parts.1.massG', 20], ['parts.1.heightMm', null],
            ['parts.0.radiusMm', 10001], ['parts.0.massG', 100001], ['parts.0.radiusMm', '120'],
            ['parts.0.visualKey', 'script'], ['parts.0.materialKey', 'missing'],
            ['materials.0.friction', -1], ['materials.0.restitution', 1.1],
            ['materials.0.friction', 0.123456], ['materials.1.key', 'basketball-surface'],
            ['parts.1.key', 'basketball'], ['simulationVersion', 'unknown'],
            ['parts.0.gravity', 1], ['materials.0.options', []], ['extra', true],
        ];
    }

    #[DataProvider('invalidModelDefinitions')]
    public function test_model_saves_validate_geometry_and_coefficients_before_writing(string $table, array $changes): void
    {
        $part = Part::factory()->create();
        $row = $table === 'parts' ? $part : $part->material;
        $before = $row->fresh()->getAttributes();
        try {
            $row->update($changes);
            $this->fail('Invalid model definition accepted.');
        } catch (ValidationException|\ValueError) {
            $this->assertSame($before, $row->fresh()->getAttributes());
        }
    }

    public static function invalidModelDefinitions(): array
    {
        return [
            ['parts', ['radius_mm' => null]], ['parts', ['radius_mm' => 0]],
            ['parts', ['radius_mm' => 10001]], ['parts', ['width_mm' => 5]],
            ['parts', ['shape_type' => 'cuboid', 'radius_mm' => null, 'width_mm' => 5, 'height_mm' => null]],
            ['parts', ['shape_type' => 'unknown']], ['parts', ['body_type' => 'unknown']],
            ['parts', ['mass_g' => null]], ['parts', ['mass_g' => 100001]], ['parts', ['body_type' => 'fixed']],
            ['parts', ['visual_key' => 'unknown']],
            ['parts', ['radius_mm' => '120']],
            ['materials', ['friction' => 0.123456]],
            ['materials', ['friction' => -0.01]], ['materials', ['friction' => 2.01]],
            ['materials', ['restitution' => -0.01]], ['materials', ['restitution' => 1.01]],
        ];
    }

    public function test_postgres_rejects_cross_release_material_reference(): void
    {
        $part = Part::factory()->create();
        $foreign = Material::factory()->create();
        $this->expectException(QueryException::class);
        DB::table('parts')->where('id', $part->id)->update(['material_id' => $foreign->id]);
    }

    public function test_postgres_preserves_referenced_material(): void
    {
        $part = Part::factory()->create();
        $this->expectException(QueryException::class);
        DB::table('materials')->where('id', $part->material_id)->delete();
    }

    public function test_postgres_preserves_referenced_catalogue(): void
    {
        $material = Material::factory()->create();
        $this->expectException(QueryException::class);
        DB::table('catalogue_versions')->where('id', $material->catalogue_version_id)->delete();
    }

    public function test_postgres_rejects_duplicate_material_key(): void
    {
        $material = Material::factory()->create();
        $this->expectException(QueryException::class);
        Material::factory()->create(['catalogue_version_id' => $material->catalogue_version_id, 'key' => $material->key]);
    }

    public function test_incomplete_draft_cannot_be_sealed(): void
    {
        $draft = CatalogueVersion::factory()->create();
        $this->expectException(ValidationException::class);
        $draft->update(['released_at' => now()]);
    }

    public function test_catalogue_model_rejects_an_unsupported_recipe(): void
    {
        $draft = CatalogueVersion::factory()->create();
        $before = $draft->fresh()->getAttributes();
        try {
            $draft->update(['simulation_version' => 'unsupported']);
            $this->fail('Unsupported recipe accepted.');
        } catch (\ValueError) {
            $this->assertSame($before, $draft->fresh()->getAttributes());
        }
    }

    public function test_model_creation_validates_before_inserting(): void
    {
        $material = Material::factory()->create();
        $this->expectException(ValidationException::class);
        Part::factory()->create(['material_id' => $material->id, 'radius_mm' => 0]);
    }

    public function test_new_catalogue_variant_needs_no_schema_change(): void
    {
        $this->seed();
        $original = $this->getJson('/api/v1/catalogues/workshop-1')->assertOk()->json();
        $definition = WorkshopOneSeeder::definition();
        $definition['id'] = 'workshop-2';
        $variant = $definition['parts'][0];
        $variant['key'] = 'small-basketball';
        $variant['radiusMm'] = 100;
        $definition['parts'][] = $variant;
        (new ImportCatalogue)->handle($definition);
        $this->getJson('/api/v1/catalogues/workshop-2')->assertOk()->assertJsonCount(5, 'parts');
        $document = config('machine-workshop.starter.document');
        $document['catalogueVersion'] = 'workshop-2';
        $document['instances'] = [
            ['id' => 'small-ball', 'partKey' => 'small-basketball', 'xMm' => 0, 'yMm' => 0, 'rotationMilliDegrees' => 0],
        ];
        $this->postJson('/api/v1/documents/validate', ['document' => $document])->assertOk();
        $document['catalogueVersion'] = 'workshop-1';
        $this->postJson('/api/v1/documents/validate', ['document' => $document])
            ->assertUnprocessable()->assertJsonValidationErrors('document.instances.0.partKey');
        $this->seed();
        $this->getJson('/api/v1/catalogues/workshop-1')->assertOk()->assertExactJson($original);
    }
}
