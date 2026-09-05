<?php

namespace Tests\Feature;

use App\Actions\ImportCatalogue;
use App\Data\PuzzleDocumentData;
use App\Models\CatalogueVersion;
use Database\Seeders\WorkshopOneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DocumentValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WorkshopOneSeeder::class);
    }

    public function test_basketball_prototype_is_valid_and_kept_separate_from_empty_sandbox(): void
    {
        $prototype = $this->getJson('/api/v1/prototypes/basketball-brick')->assertOk()
            ->assertExactJson(config('machine-workshop.basketball-brick'))->json();
        $this->assertSame(['basketball', 'platform-brick'], array_column($prototype['document']['instances'], 'partKey'));
        $this->postJson('/api/v1/documents/validate', ['document' => $prototype['document']])->assertOk();
        $this->getJson('/api/v1/starters/sandbox')->assertOk()->assertJsonCount(0, 'document.instances');
        $fixture = json_decode(file_get_contents(base_path('../../packages/simulation/fixtures/basketball-brick.json')), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame($prototype['document'], $fixture['document']);
        $this->getJson('/api/v1/catalogues/workshop-1')->assertExactJson($fixture['catalogue']);
    }

    public function test_starter_and_guest_round_trip_preserve_exact_document_without_writes(): void
    {
        $starter = $this->getJson('/api/v1/starters/sandbox')->assertOk()
            ->assertExactJson(config('machine-workshop.starter'))->json();
        $this->assertSame('workshop-1', $starter['document']['catalogueVersion']);
        $this->assertSame([], $starter['document']['instances']);
        $this->assertNull($starter['document']['goal']);
        $this->getJson('/api/v1/starters/material-demo')->assertNotFound();
        $document = $this->documentWithParts();
        $document['instances'] = array_reverse($document['instances']);
        $document['instances'][0]['xMm'] = -50000;
        $document['instances'][0]['yMm'] = 50000;
        $document['instances'][0]['rotationMilliDegrees'] = 359999;
        DB::enableQueryLog();
        $this->postJson('/api/v1/documents/validate', ['document' => $document])->assertOk()->assertExactJson(['document' => $document]);
        foreach (DB::getQueryLog() as $query) {
            $this->assertDoesNotMatchRegularExpression('/^\s*(insert|update|delete|create|alter|drop)/i', $query['query']);
        }
        DB::disableQueryLog();
        $this->assertSame($document, PuzzleDocumentData::validateAndCreate($document)->toArray());
    }

    #[DataProvider('invalidDocuments')]
    public function test_invalid_nested_document_is_rejected(string $path, mixed $value): void
    {
        $document = $this->documentWithParts();
        data_set($document, $path, $value);
        $this->postJson('/api/v1/documents/validate', ['document' => $document])->assertUnprocessable()->assertJsonStructure(['message', 'errors']);
    }

    public static function invalidDocuments(): array
    {
        return [
            ['schemaVersion', 2], ['schemaVersion', '1'], ['catalogueVersion', 'unknown'],
            ['instances.0.id', 'space id'], ['instances.0.id', 'é'], ['instances.0.id', 'platform'],
            ['instances.0.partKey', 'unknown'], ['instances.0.xMm', 50001], ['instances.0.yMm', -50001],
            ['instances.0.xMm', 1.5], ['instances.0.xMm', '0'], ['instances.0.xMm', true],
            ['instances.0.xMm', null], ['instances.0.xMm', 'NaN'], ['instances.0.xMm', 'Infinity'],
            ['instances.0.rotationMilliDegrees', -1], ['instances.0.rotationMilliDegrees', 360000],
            ['instances.0.scale', 2], ['instances.0.materialKey', 'wood'],
            ['instances.0.friction', 0.3], ['gravity', -10], ['connections', []],
            ['lockedInstanceIds', ['ball-brick']], ['inventory', [['partKey' => 'basketball', 'quantity' => 1]]],
            ['goal', ['objectId' => 'ball-brick', 'xMm' => 0, 'yMm' => 0, 'widthMm' => 100, 'heightMm' => 100, 'consecutiveTicks' => 30]],
            ['goal', ''], ['instances', ['named' => []]], ['instances', null],
        ];
    }

    public function test_missing_keys_unknown_root_keys_and_non_object_documents_are_rejected(): void
    {
        $document = config('machine-workshop.starter.document');
        foreach (array_keys($document) as $key) {
            $missing = $document;
            unset($missing[$key]);
            $this->postJson('/api/v1/documents/validate', ['document' => $missing])->assertUnprocessable();
        }
        $this->postJson('/api/v1/documents/validate', ['document' => $document, 'score' => 1])->assertUnprocessable();
        $this->postJson('/api/v1/documents/validate', ['document' => 'invalid'])->assertUnprocessable();
    }

    public function test_empty_sandbox_and_overlapping_placements_are_allowed_but_limit_is_enforced(): void
    {
        $document = config('machine-workshop.starter.document');
        $document['instances'] = [];
        $this->postJson('/api/v1/documents/validate', ['document' => $document])->assertOk();
        for ($i = 0; $i < 200; $i++) {
            $document['instances'][] = ['id' => "ball-$i", 'partKey' => 'basketball', 'xMm' => 0, 'yMm' => 0, 'rotationMilliDegrees' => 0];
        }
        $this->postJson('/api/v1/documents/validate', ['document' => $document])->assertOk();
        $document['instances'][] = ['id' => 'overflow', 'partKey' => 'basketball', 'xMm' => 0, 'yMm' => 0, 'rotationMilliDegrees' => 0];
        $this->postJson('/api/v1/documents/validate', ['document' => $document])->assertUnprocessable();
    }

    public function test_draft_catalogue_and_part_from_another_release_are_rejected(): void
    {
        $draft = CatalogueVersion::factory()->create();
        $document = $this->documentWithParts();
        $document['catalogueVersion'] = $draft->id;
        $this->postJson('/api/v1/documents/validate', ['document' => $document])->assertUnprocessable();
        $definition = WorkshopOneSeeder::definition();
        $definition['id'] = 'other';
        $definition['parts'][0]['key'] = 'other-ball';
        (new ImportCatalogue)->handle($definition);
        $document['catalogueVersion'] = 'workshop-1';
        $document['instances'][0]['partKey'] = 'other-ball';
        $this->postJson('/api/v1/documents/validate', ['document' => $document])->assertUnprocessable();
    }

    public function test_body_size_limit_applies_to_actual_bytes(): void
    {
        $json = json_encode(['document' => config('machine-workshop.starter.document')]);
        $this->call('POST', '/api/v1/documents/validate', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
        ], $json.str_repeat(' ', 256 * 1024))->assertUnprocessable();
    }

    public function test_guest_validation_is_rate_limited(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->postJson('/api/v1/documents/validate', [])->assertUnprocessable();
        }
        $this->postJson('/api/v1/documents/validate', [])->assertStatus(429);
    }

    /** A populated test document independent of the empty product starter. */
    private function documentWithParts(): array
    {
        return [
            ...config('machine-workshop.starter.document'),
            'instances' => [
                ['id' => 'ball', 'partKey' => 'basketball', 'xMm' => 0, 'yMm' => 1000, 'rotationMilliDegrees' => 0],
                ['id' => 'platform', 'partKey' => 'platform-wood', 'xMm' => 0, 'yMm' => 0, 'rotationMilliDegrees' => 0],
            ],
        ];
    }
}
