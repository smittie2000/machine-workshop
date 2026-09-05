import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\CatalogueController::show
* @see app/Http/Controllers/CatalogueController.php:10
* @route '/api/v1/catalogues/{catalogue}'
*/
export const show = (args: { catalogue: string | number } | [catalogue: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/v1/catalogues/{catalogue}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CatalogueController::show
* @see app/Http/Controllers/CatalogueController.php:10
* @route '/api/v1/catalogues/{catalogue}'
*/
show.url = (args: { catalogue: string | number } | [catalogue: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { catalogue: args }
    }

    if (Array.isArray(args)) {
        args = {
            catalogue: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        catalogue: args.catalogue,
    }

    return show.definition.url
            .replace('{catalogue}', parsedArgs.catalogue.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CatalogueController::show
* @see app/Http/Controllers/CatalogueController.php:10
* @route '/api/v1/catalogues/{catalogue}'
*/
show.get = (args: { catalogue: string | number } | [catalogue: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CatalogueController::show
* @see app/Http/Controllers/CatalogueController.php:10
* @route '/api/v1/catalogues/{catalogue}'
*/
show.head = (args: { catalogue: string | number } | [catalogue: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

const CatalogueController = { show }

export default CatalogueController