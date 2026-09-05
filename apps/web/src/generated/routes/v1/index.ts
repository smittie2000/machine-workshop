import catalogues from './catalogues'
import starters from './starters'
import prototypes from './prototypes'
import documents from './documents'

const v1 = {
    catalogues: Object.assign(catalogues, catalogues),
    starters: Object.assign(starters, starters),
    prototypes: Object.assign(prototypes, prototypes),
    documents: Object.assign(documents, documents),
}

export default v1