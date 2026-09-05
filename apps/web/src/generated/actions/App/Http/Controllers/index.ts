import CatalogueController from './CatalogueController'
import StarterDocumentController from './StarterDocumentController'
import PrototypeDocumentController from './PrototypeDocumentController'
import ValidateDocumentController from './ValidateDocumentController'

const Controllers = {
    CatalogueController: Object.assign(CatalogueController, CatalogueController),
    StarterDocumentController: Object.assign(StarterDocumentController, StarterDocumentController),
    PrototypeDocumentController: Object.assign(PrototypeDocumentController, PrototypeDocumentController),
    ValidateDocumentController: Object.assign(ValidateDocumentController, ValidateDocumentController),
}

export default Controllers