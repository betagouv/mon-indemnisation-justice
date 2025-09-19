import * as React from 'react'
import {createRootRoute, createRootRouteWithContext, Outlet} from '@tanstack/react-router'
import {AgentContext} from "@/routes/contexts/AgentContext.ts";

export const Route = createRootRouteWithContext<AgentContext>()({
    component: () => <Outlet/>,
    notFoundComponent: () => {
        return (
            <div>
                {/* TODO s'inspirer de pages d'erreur comme celles-ci https://ui.mantine.dev/category/error-pages/ */}
                <p>Oups vous êtes perdu</p>
            </div>
        )
    },
})