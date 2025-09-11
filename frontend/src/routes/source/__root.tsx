import * as React from 'react'
import {createRootRoute, Outlet} from '@tanstack/react-router'

export const Route = createRootRoute({
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