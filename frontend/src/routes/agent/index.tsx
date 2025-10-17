import React from "react";
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/agent/')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/agent_/"!</div>
}
