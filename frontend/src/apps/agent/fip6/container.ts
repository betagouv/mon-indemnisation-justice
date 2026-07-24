import {
  APIDeclarationManager,
  DeclarationManagerInterface,
} from "@/apps/agent/fdo/services/declaration.ts";
import {
  APIDocumentManager,
  DocumentManagerInterface,
} from "@/common/services/agent/document.ts";
import { AgentManagerInterface, APIAgentManager } from "@fip6//services/agent";
import {
  APIDossierManager,
  DossierManagerInterface,
} from "@fip6/services/dossier.ts";
import { Container } from "inversify";

const container: Container = new Container();

container
  .bind<AgentManagerInterface>(AgentManagerInterface.$)
  .to(APIAgentManager)
  .inSingletonScope();

container
  .bind<DossierManagerInterface>(DossierManagerInterface.$)
  .to(APIDossierManager)
  .inSingletonScope();

container
  .bind<DocumentManagerInterface>(DocumentManagerInterface.$)
  .to(APIDocumentManager)
  .inSingletonScope();

container
  .bind<DeclarationManagerInterface>(DeclarationManagerInterface.$)
  .to(APIDeclarationManager)
  .inSingletonScope();

export { container };
