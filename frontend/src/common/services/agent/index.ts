import { Container } from "inversify";
import { APIDocumentManager, DocumentManagerInterface } from "./document";
import {
  AgentManagerInterface,
  APIAgentManager,
} from "@/common/services/agent/agent.ts";
import {
  APIDossierManager,
  DossierManagerInterface,
} from "@/common/services/agent/dossier.ts";

const container: Container = new Container();

container
  .bind<DossierManagerInterface>(DossierManagerInterface.$)
  .to(APIDossierManager)
  .inSingletonScope();

container
  .bind<DocumentManagerInterface>(DocumentManagerInterface.$)
  .to(APIDocumentManager)
  .inSingletonScope();

container
  .bind<AgentManagerInterface>(AgentManagerInterface.$)
  .to(APIAgentManager)
  .inSingletonScope();

export { container };
