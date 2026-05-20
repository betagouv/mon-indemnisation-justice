import { APIDeclarationManager, DeclarationManagerInterface } from "@/apps/agent/fdo/services/declaration.ts";
import { AgentManagerInterface, APIAgentManager } from "@/common/services/agent/agent.ts";
import { APIDocumentManager, DocumentManagerInterface } from "@/common/services/agent/document.ts";
import { APIDossierManager, DossierManagerInterface } from "@/common/services/agent/dossier.ts";
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
