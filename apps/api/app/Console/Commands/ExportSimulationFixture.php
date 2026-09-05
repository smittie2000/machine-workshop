<?php

namespace App\Console\Commands;

use App\Data\CatalogueData;
use App\Data\PuzzleDocumentData;
use App\Models\CatalogueVersion;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('workshop:export-simulation-fixture')]
#[Description('Export the validated basketball/brick fixture from the released Laravel catalogue')]
class ExportSimulationFixture extends Command
{
    public function handle(): int
    {
        $document = PuzzleDocumentData::validateAndCreate(config('machine-workshop.basketball-brick.document'));
        $catalogue = CatalogueVersion::query()->whereNotNull('released_at')->findOrFail($document->catalogueVersion);
        $path = base_path('../../packages/simulation/fixtures/basketball-brick.json');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode([
            'catalogue' => CatalogueData::fromModel($catalogue)->toArray(),
            'document' => $document->toArray(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
        $this->info('Exported basketball-brick.json from '.$catalogue->id);

        return self::SUCCESS;
    }
}
