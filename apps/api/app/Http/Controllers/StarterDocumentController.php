<?php

namespace App\Http\Controllers;

use App\Data\PuzzleDocumentData;
use App\Data\StarterDocumentData;

class StarterDocumentController extends Controller
{
    public function __invoke(): StarterDocumentData
    {
        $starter = config('machine-workshop.starter');

        return new StarterDocumentData($starter['title'], PuzzleDocumentData::validateAndCreate($starter['document']));
    }
}
