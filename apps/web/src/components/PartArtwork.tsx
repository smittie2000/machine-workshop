/** Explicit visual mapping. Dimensions here are artwork, never physical definitions. */
export function partAsset(visualKey: string): string {
  switch (visualKey) {
    case 'basketball': return '/assets/parts/basketball.svg'
    case 'platform-brick': return '/assets/parts/platform-brick.svg'
    default: throw new Error(`No artwork implemented for ${visualKey}`)
  }
}

export function PartArtwork({ visualKey, className = '', alt = '' }: { visualKey: string; className?: string; alt?: string }) {
  return <img className={className} src={partAsset(visualKey)} alt={alt} draggable={false} />
}
