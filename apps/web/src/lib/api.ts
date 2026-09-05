import { mutationOptions, queryOptions } from '@tanstack/react-query'
import type {
  CatalogueData,
  StarterDocumentData,
  ValidateDocumentData,
  ValidatedDocumentData,
} from '@workshop/contracts'
import { show } from '../generated/routes/v1/catalogues'
import { sandbox } from '../generated/routes/v1/starters'
import { basketballBrick } from '../generated/routes/v1/prototypes'
import { validate } from '../generated/routes/v1/documents'
import type { RouteDefinition } from '../generated/wayfinder'

export class ApiError extends Error {
  constructor(public readonly status: number, public readonly details: unknown) {
    super(`API request failed (${status})`)
  }
}

// The API validates domain data. This adapter only handles HTTP transport.
async function fetchData<T>(
  route: RouteDefinition<'get' | 'post'>,
  options: { body?: ValidateDocumentData; signal?: AbortSignal } = {},
): Promise<T> {
  const response = await fetch(route.url, {
    method: route.method.toUpperCase(),
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      ...(options.body ? { 'Content-Type': 'application/json' } : {}),
    },
    body: options.body ? JSON.stringify(options.body) : undefined,
    signal: options.signal,
  })
  if (!response.ok) {
    throw new ApiError(response.status, await response.json().catch(() => null))
  }
  return response.json() as Promise<T>
}

// Browser query functions use the same-origin proxy; do not prefetch these in SSR.
export const catalogueQueryOptions = (release: string) => queryOptions({
  queryKey: ['catalogue', release],
  queryFn: ({ signal }) => fetchData<CatalogueData>(show(encodeURIComponent(release)), { signal }),
  staleTime: Infinity,
})

export const starterQueryOptions = () => queryOptions({
  queryKey: ['starter', 'sandbox'],
  queryFn: ({ signal }) => fetchData<StarterDocumentData>(sandbox(), { signal }),
})

export const prototypeQueryOptions = () => queryOptions({
  queryKey: ['prototype', 'basketball-brick'],
  queryFn: ({ signal }) => fetchData<StarterDocumentData>(basketballBrick(), { signal }),
  retry: false,
})

export const validateDocumentMutationOptions = () => mutationOptions({
  mutationKey: ['document', 'validate'],
  mutationFn: (body: ValidateDocumentData) => fetchData<ValidatedDocumentData>(validate(), { body }),
  retry: false,
})
