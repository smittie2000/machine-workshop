<?php

namespace App\Http\Controllers;

use App\Data\PuzzleDocumentData;
use App\Data\StarterDocumentData;

class PrototypeDocumentController extends Controller
{
    public function __invoke(): StarterDocumentData
    {
        $prototype = config('machine-workshop.basketball-brick');

        return new StarterDocumentData($prototype['title'], PuzzleDocumentData::validateAndCreate($prototype['document']));
    }
}
