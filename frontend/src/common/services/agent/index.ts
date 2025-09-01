import { Container } from "inversify";
import { APIDocumentManager, DocumentManagerInterface } from "./document";

const container: Container = new Container();
const DocumentManagerImpl = Symbol.for("DocumentManagerInterface");

container
  .bind<DocumentManagerInterface>(DocumentManagerImpl)
  .to(APIDocumentManager)
  .inSingletonScope();

export { container, type DocumentManagerInterface, DocumentManagerImpl };
