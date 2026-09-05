import { Link } from '@tanstack/react-router'

export function WorkshopHeader() {
  return <header className="site-header">
    <Link to="/" className="brand" aria-label="Machine Workshop home">
      <span className="brand-mark" aria-hidden="true">mw<span>•</span></span>
      <span>Machine<br />Workshop</span>
    </Link>
    <nav aria-label="Main navigation">
      <Link to="/" activeOptions={{ exact: true }}>Welcome</Link>
      <Link to="/prototype">The test bench <span aria-hidden="true">↗</span></Link>
    </nav>
    <span className="edition">A little curiosity goes a long way.</span>
  </header>
}
