import { Container } from "inversify";
import {
  ApiAuthentificationService,
  AuthentificationServiceInterface,
} from "@/apps/public/services/AuthentificationService";
import { ApiAvocatService, AvocatServiceInterface } from "@/apps/public/services/AvocatService";
import { ApiBarreauService, BarreauServiceInterface } from "@/apps/public/services/BarreauService";
import {
  LocalStorageTestEligibiliteManager,
  TestEligibiliteManagerInterface,
} from "@/apps/public/services/TestEligibiliteManager";

const container: Container = new Container();

container
  .bind<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$)
  .to(LocalStorageTestEligibiliteManager)
  .inSingletonScope();

container
  .bind<AuthentificationServiceInterface>(AuthentificationServiceInterface.$)
  .to(ApiAuthentificationService)
  .inSingletonScope();

container
  .bind<AvocatServiceInterface>(AvocatServiceInterface.$)
  .to(ApiAvocatService)
  .inSingletonScope();

container
  .bind<BarreauServiceInterface>(BarreauServiceInterface.$)
  .to(ApiBarreauService)
  .inSingletonScope();

export { container };
