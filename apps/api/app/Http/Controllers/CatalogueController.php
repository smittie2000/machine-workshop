<?php

namespace App\Http\Controllers;

use App\Data\CatalogueData;
use App\Models\CatalogueVersion;

class CatalogueController extends Controller
{
    public function show(string $catalogue): CatalogueData
    {
        return CatalogueData::fromModel(CatalogueVersion::query()->whereNotNull('released_at')->findOrFail($catalogue));
    }
}
