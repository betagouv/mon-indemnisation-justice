import {
  RequerantAPICLient,
  RequerantManagerImpl,
} from "@/common/services/RequerantManager";
import { Container } from "inversify";

const container: Container = new Container();
container.bind(RequerantManagerImpl).to(RequerantAPICLient).inSingletonScope();

export { container };
