import { Container } from "inversify";
import {
  APIUsagerManager,
  UsagerManagerInterface,
} from "@/apps/requerant/services/UsagerManager";
import {
  DossierManagerInterface,
  InMemoryDossierManager,
} from "@/apps/requerant/services/DossierManager.ts";

const container: Container = new Container();

container
  .bind<UsagerManagerInterface>(UsagerManagerInterface.$)
  .to(APIUsagerManager)
  .inSingletonScope();

container
  .bind<DossierManagerInterface>(DossierManagerInterface.$)
  .to(InMemoryDossierManager)
  .inSingletonScope();

export { container };
