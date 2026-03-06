import {
  RequerantAPICLient,
  RequerantManagerImpl,
} from "@/common/services/RequerantManager";
import { Container } from "inversify";

export { differentiel, fusion } from "./object";

const container: Container = new Container();
container.bind(RequerantManagerImpl).to(RequerantAPICLient).inSingletonScope();

export { container };
