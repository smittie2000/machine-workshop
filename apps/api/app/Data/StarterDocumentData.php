<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class StarterDocumentData extends Data
{
    public function __construct(public string $title, public PuzzleDocumentData $document) {}
}
