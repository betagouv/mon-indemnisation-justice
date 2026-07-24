import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/bris-de-porte/nouvelle-declaration')({
  component: RouteComponent,
})

function RouteComponent() {
  return <div>Hello "/bris-de-porte/nouvelle-declaration"!</div>
}
