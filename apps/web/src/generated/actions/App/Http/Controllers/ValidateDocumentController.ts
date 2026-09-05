import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\ValidateDocumentController::__invoke
* @see app/Http/Controllers/ValidateDocumentController.php:12
* @route '/api/v1/documents/validate'
*/
const ValidateDocumentController = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: ValidateDocumentController.url(options),
    method: 'post',
})

ValidateDocumentController.definition = {
    methods: ["post"],
    url: '/api/v1/documents/validate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ValidateDocumentController::__invoke
* @see app/Http/Controllers/ValidateDocumentController.php:12
* @route '/api/v1/documents/validate'
*/
ValidateDocumentController.url = (options?: RouteQueryOptions) => {
    return ValidateDocumentController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ValidateDocumentController::__invoke
* @see app/Http/Controllers/ValidateDocumentController.php:12
* @route '/api/v1/documents/validate'
*/
ValidateDocumentController.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: ValidateDocumentController.url(options),
    method: 'post',
})

export default ValidateDocumentController