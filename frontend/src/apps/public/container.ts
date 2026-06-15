import { Container } from "inversify";
import {
  LocalStorageTestEligibiliteManager,
  TestEligibiliteManagerInterface,
} from "@/apps/public/services/TestEligibiliteManager";

const container: Container = new Container();

container
  .bind<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$)
  .to(LocalStorageTestEligibiliteManager)
  .inSingletonScope();

export { container };
