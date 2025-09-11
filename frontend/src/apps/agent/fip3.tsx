import React, {StrictMode} from 'react'
import '@/apps/_init'
import {
    createRouter, RouterProvider,
} from '@tanstack/react-router'


import {routeTree} from '@/routes/agent-fip3.gen.ts'
import {container} from "@/common/services/agent";
import ReactDOM from "react-dom/client";
import {Provider} from "inversify-react";

// Set up a Router instance
const router = createRouter({
    routeTree,
    defaultPreload: 'intent',
    defaultStaleTime: 5000,
    scrollRestoration: true,
})


// Register things for typesafety
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
            <Provider container={container}>
                <RouterProvider router={router}/>
            </Provider>
        </StrictMode>
    );
}
