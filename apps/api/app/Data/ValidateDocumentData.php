<?php

namespace App\Data;

use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Data;

class ValidateDocumentData extends Data
{
    public function __construct(public PuzzleDocumentData $document) {}

    public static function rules(): array
    {
        return ['document' => ['required', 'array:schemaVersion,catalogueVersion,instances,lockedInstanceIds,inventory,goal']];
    }

    public static function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach (array_diff(array_keys($validator->getData()), ['document']) as $key) {
                $validator->errors()->add($key, 'Unknown request field.');
            }
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            try {
                PuzzleDocumentData::validate($validator->getData()['document']);
            } catch (ValidationException $exception) {
                foreach ($exception->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $validator->errors()->add("document.$field", $message);
                    }
                }
            }
        });
    }
}
