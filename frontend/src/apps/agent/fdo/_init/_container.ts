import { Container } from "inversify";
import {
  DeclarationManagerInterface,
  APIDeclarationManager,
} from "@/apps/agent/fdo/services/declaration.ts";
import {
  AgentManagerInterface,
  APIAgentManager,
} from "@/common/services/agent/agent.ts";

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
