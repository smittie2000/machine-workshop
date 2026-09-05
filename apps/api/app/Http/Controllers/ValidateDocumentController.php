<?php

namespace App\Http\Controllers;

use App\Data\ValidatedDocumentData;
use App\Data\ValidateDocumentData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidateDocumentController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = ValidateDocumentData::validateAndCreate($request->all());

        return response()->json(new ValidatedDocumentData($data->document));
    }
}
