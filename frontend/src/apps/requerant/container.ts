import { Container } from "inversify";
import {
  APIUsagerManager,
  UsagerManagerInterface,
} from "@/apps/requerant/services/UsagerManager";

const container: Container = new Container();

container
  .bind<UsagerManagerInterface>(UsagerManagerInterface.$)
  .to(APIUsagerManager)
  .inSingletonScope();

export { container };
