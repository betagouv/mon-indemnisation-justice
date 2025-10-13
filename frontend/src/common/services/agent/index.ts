import { Container } from "inversify";
import { APIDocumentManager, DocumentManagerInterface } from "./document";
import {
  AgentManagerInterface,
  APIAgentManager,
} from "@/common/services/agent/agent.ts";

const container: Container = new Container();
const DocumentManagerImpl = Symbol.for("DocumentManagerInterface");

container
  .bind<DocumentManagerInterface>(DocumentManagerImpl)
  .to(APIDocumentManager)
  .inSingletonScope();

const AgentManagerImpl = Symbol.for("AgentManagerInterface");

container
  .bind<AgentManagerInterface>(AgentManagerInterface.$)
  .to(APIAgentManager)
  .inSingletonScope();

export { container, type DocumentManagerInterface, DocumentManagerImpl };
