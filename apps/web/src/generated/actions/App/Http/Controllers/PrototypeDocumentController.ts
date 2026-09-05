import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PrototypeDocumentController::__invoke
* @see app/Http/Controllers/PrototypeDocumentController.php:10
* @route '/api/v1/prototypes/basketball-brick'
*/
const PrototypeDocumentController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: PrototypeDocumentController.url(options),
    method: 'get',
})

PrototypeDocumentController.definition = {
    methods: ["get","head"],
    url: '/api/v1/prototypes/basketball-brick',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PrototypeDocumentController::__invoke
* @see app/Http/Controllers/PrototypeDocumentController.php:10
* @route '/api/v1/prototypes/basketball-brick'
*/
PrototypeDocumentController.url = (options?: RouteQueryOptions) => {
    return PrototypeDocumentController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PrototypeDocumentController::__invoke
* @see app/Http/Controllers/PrototypeDocumentController.php:10
* @route '/api/v1/prototypes/basketball-brick'
*/
PrototypeDocumentController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: PrototypeDocumentController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PrototypeDocumentController::__invoke
* @see app/Http/Controllers/PrototypeDocumentController.php:10
* @route '/api/v1/prototypes/basketball-brick'
*/
PrototypeDocumentController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: PrototypeDocumentController.url(options),
    method: 'head',
})

export default PrototypeDocumentController