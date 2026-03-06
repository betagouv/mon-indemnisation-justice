import {
  AdresseManagerInterface,
  APIAdresseManager,
} from "@/apps/requerant/services/AdresseManager.ts";
import {
  ApiDossierManager,
  DossierManagerInterface,
} from "@/apps/requerant/services/DossierManager.ts";
import {
  APIUsagerManager,
  UsagerManagerInterface,
} from "@/apps/requerant/services/UsagerManager";
import { Container } from "inversify";

const container: Container = new Container();

container
  .bind<UsagerManagerInterface>(UsagerManagerInterface.$)
  .to(APIUsagerManager)
  .inSingletonScope();

container
  .bind<DossierManagerInterface>(DossierManagerInterface.$)
  .to(ApiDossierManager)
  .inSingletonScope();

container
  .bind<AdresseManagerInterface>(AdresseManagerInterface.$)
  .to(APIAdresseManager)
  .inSingletonScope();

export { container };
