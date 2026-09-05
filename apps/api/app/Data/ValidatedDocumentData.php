<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ValidatedDocumentData extends Data
{
    public function __construct(public PuzzleDocumentData $document) {}
}
