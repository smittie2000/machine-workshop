import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\StarterDocumentController::__invoke
* @see app/Http/Controllers/StarterDocumentController.php:10
* @route '/api/v1/starters/sandbox'
*/
export const sandbox = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: sandbox.url(options),
    method: 'get',
})

sandbox.definition = {
    methods: ["get","head"],
    url: '/api/v1/starters/sandbox',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\StarterDocumentController::__invoke
* @see app/Http/Controllers/StarterDocumentController.php:10
* @route '/api/v1/starters/sandbox'
*/
sandbox.url = (options?: RouteQueryOptions) => {
    return sandbox.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\StarterDocumentController::__invoke
* @see app/Http/Controllers/StarterDocumentController.php:10
* @route '/api/v1/starters/sandbox'
*/
sandbox.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: sandbox.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\StarterDocumentController::__invoke
* @see app/Http/Controllers/StarterDocumentController.php:10
* @route '/api/v1/starters/sandbox'
*/
sandbox.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: sandbox.url(options),
    method: 'head',
})

const starters = {
    sandbox: Object.assign(sandbox, sandbox),
}

export default starters