import {
  index,
  route,
  rootRoute,
  physical,
} from "@tanstack/virtual-file-routes";

export const routes = rootRoute("root.tsx", [
  index("index.tsx"),
  route("/requisition-serrurier", "requisition-serrurier.tsx"),
  route("/foire-aux-questions", "foire-aux-questions.tsx"),
  physical("/agent/fdo", "agent/fdo"),
]);
