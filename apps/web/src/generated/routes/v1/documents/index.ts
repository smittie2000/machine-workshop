import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\ValidateDocumentController::__invoke
* @see app/Http/Controllers/ValidateDocumentController.php:12
* @route '/api/v1/documents/validate'
*/
export const validate = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: validate.url(options),
    method: 'post',
})

validate.definition = {
    methods: ["post"],
    url: '/api/v1/documents/validate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ValidateDocumentController::__invoke
* @see app/Http/Controllers/ValidateDocumentController.php:12
* @route '/api/v1/documents/validate'
*/
validate.url = (options?: RouteQueryOptions) => {
    return validate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ValidateDocumentController::__invoke
* @see app/Http/Controllers/ValidateDocumentController.php:12
* @route '/api/v1/documents/validate'
*/
validate.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: validate.url(options),
    method: 'post',
})

const documents = {
    validate: Object.assign(validate, validate),
}

export default documents