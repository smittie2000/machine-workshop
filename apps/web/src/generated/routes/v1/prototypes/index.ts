import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\PrototypeDocumentController::__invoke
* @see app/Http/Controllers/PrototypeDocumentController.php:10
* @route '/api/v1/prototypes/basketball-brick'
*/
export const basketballBrick = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: basketballBrick.url(options),
    method: 'get',
})

basketballBrick.definition = {
    methods: ["get","head"],
    url: '/api/v1/prototypes/basketball-brick',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PrototypeDocumentController::__invoke
* @see app/Http/Controllers/PrototypeDocumentController.php:10
* @route '/api/v1/prototypes/basketball-brick'
*/
basketballBrick.url = (options?: RouteQueryOptions) => {
    return basketballBrick.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PrototypeDocumentController::__invoke
* @see app/Http/Controllers/PrototypeDocumentController.php:10
* @route '/api/v1/prototypes/basketball-brick'
*/
basketballBrick.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: basketballBrick.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PrototypeDocumentController::__invoke
* @see app/Http/Controllers/PrototypeDocumentController.php:10
* @route '/api/v1/prototypes/basketball-brick'
*/
basketballBrick.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: basketballBrick.url(options),
    method: 'head',
})

const prototypes = {
    basketballBrick: Object.assign(basketballBrick, basketballBrick),
}

export default prototypes