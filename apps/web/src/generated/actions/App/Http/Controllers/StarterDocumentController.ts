import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\StarterDocumentController::__invoke
* @see app/Http/Controllers/StarterDocumentController.php:10
* @route '/api/v1/starters/sandbox'
*/
const StarterDocumentController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: StarterDocumentController.url(options),
    method: 'get',
})

StarterDocumentController.definition = {
    methods: ["get","head"],
    url: '/api/v1/starters/sandbox',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\StarterDocumentController::__invoke
* @see app/Http/Controllers/StarterDocumentController.php:10
* @route '/api/v1/starters/sandbox'
*/
StarterDocumentController.url = (options?: RouteQueryOptions) => {
    return StarterDocumentController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\StarterDocumentController::__invoke
* @see app/Http/Controllers/StarterDocumentController.php:10
* @route '/api/v1/starters/sandbox'
*/
StarterDocumentController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: StarterDocumentController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\StarterDocumentController::__invoke
* @see app/Http/Controllers/StarterDocumentController.php:10
* @route '/api/v1/starters/sandbox'
*/
StarterDocumentController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: StarterDocumentController.url(options),
    method: 'head',
})

export default StarterDocumentController