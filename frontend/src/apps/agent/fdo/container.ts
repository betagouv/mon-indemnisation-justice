import { AgentManagerInterface, APIAgentManager } from "@fdo/services/agent";
import {
  APIDeclarationManager,
  DeclarationManagerInterface,
} from "@fdo/services/declaration.ts";
import { Container } from "inversify";

const container: Container = new Container();

container
  .bind<AgentManagerInterface>(AgentManagerInterface.$)
  .to(APIAgentManager)
  .inSingletonScope();

container
  .bind<DeclarationManagerInterface>(DeclarationManagerInterface.$)
  .to(APIDeclarationManager)
  .inSingletonScope();

export { container };
