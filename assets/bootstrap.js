import { startStimulusApp, registerControllers } from "vite-plugin-symfony/stimulus/helpers"
import { registerReactControllerComponents } from "vite-plugin-symfony/stimulus/helpers/react"

registerReactControllerComponents(import.meta.glob('./react/controllers/**/*.js(x)\?'));

const app = startStimulusApp();
registerControllers(
  app,
  import.meta.glob(
    "./controllers/*_controller.jsx",
    {
      query: "?stimulus",
      eager: true,
    },
  ),
);