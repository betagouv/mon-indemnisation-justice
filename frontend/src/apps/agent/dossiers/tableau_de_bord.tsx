import React, {StrictMode} from 'react'
import ReactDOM from 'react-dom/client'
import {
    Outlet,
    RouterProvider,
    Link,
    createRouter,
    createRoute,
    createRootRoute,
} from '@tanstack/react-router'
import {TanStackRouterDevtools} from '@tanstack/react-router-devtools'
import {startReactDsfr} from "@codegouvfr/react-dsfr/spa";
import {ColorScheme} from "@codegouvfr/react-dsfr/useIsDark";
import {ListeDossierATransmettre} from "@/apps/agent/dossiers/components/ListeDossierATransmettre.tsx";
import {
    ListeDossierEnAttenteIndemnisation
} from "@/apps/agent/dossiers/components/ListeDossierEnAttenteIndemnisation.tsx";

startReactDsfr({defaultColorScheme: localStorage.getItem('scheme') as ColorScheme ?? "system"});

const rootRoute = createRootRoute({
    component: () => (
        <>
            <Outlet/>
            <TanStackRouterDevtools/>
        </>
    ),
})

const indexRoute = createRoute({
    getParentRoute: () => rootRoute,
    path: '/agent/redacteur/dossiers/liste'
});

indexRoute.addChildren([
    createRoute({
        getParentRoute: () => indexRoute,
        path: 'a-transmettre',
        component: function Index() {
            return (
                <ListeDossierATransmettre/>
            )
        },
    }),
    createRoute({
        getParentRoute: () => indexRoute,
        path: 'en-attente-indemnisation',
        component: function Index() {
            return (
                <ListeDossierEnAttenteIndemnisation/>
            )
        },
    })
])

const routeTree = rootRoute.addChildren([indexRoute])

const router = createRouter({routeTree})

declare module '@tanstack/react-router' {
    interface Register {
        router: typeof router
    }
}

const rootElement = document.getElementById('react-app')!
if (!rootElement.innerHTML) {
    const root = ReactDOM.createRoot(rootElement)
    root.render(
        <StrictMode>
            <RouterProvider router={router}/>
        </StrictMode>,
    )
}
