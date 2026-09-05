import { Link } from '@tanstack/react-router'
import { WorkshopHeader } from '../../components/WorkshopHeader'
import { WorkshopFooter } from '../../components/WorkshopFooter'
import { PartArtwork } from '../../components/PartArtwork'

export function WelcomeScreen() {
  return <div className="site-shell">
    <WorkshopHeader />
    <main>
      <section className="welcome-hero">
        <div className="hero-copy">
          <p className="eyebrow"><span className="status-dot" /> Welcome to the workshop</p>
          <h1>Great things start<br />with a little <em>what if.</em></h1>
          <p className="hero-description">A ball. A brick. A spark of curiosity.<br />Explore how simple parts move, meet, and set ideas in motion.</p>
          <Link to="/prototype" className="button button-primary">Try the first experiment <span aria-hidden="true">↗</span></Link>
          <p className="button-note">No setup. Just press play.</p>
        </div>
        <div className="hero-illustration" role="img" aria-label="A basketball suspended above a brick platform on a workshop drawing">
          <span className="drawing-label">FIG. 01 / A SIMPLE BEGINNING</span>
          <div className="orbit orbit-one" /><div className="orbit orbit-two" />
          <span className="pencil-note">let's see what happens <span>↘</span></span>
          <div className="drop-guide" />
          <PartArtwork visualKey="basketball" className="hero-ball" />
          <div className="hero-shadow" />
          <PartArtwork visualKey="platform-brick" className="hero-brick" />
          <span className="drawing-dimension">← &nbsp; a solid place to start &nbsp; →</span>
          <span className="drawing-cross cross-one">+</span><span className="drawing-cross cross-two">+</span>
          <span className="drawing-stamp">LET CURIOSITY<br /><strong>DO ITS THING.</strong></span>
        </div>
      </section>
      <section className="experiment-section" aria-labelledby="experiment-title">
        <div className="section-heading"><div><p className="eyebrow">On the bench</p><h2 id="experiment-title">One small experiment. A good place to begin.</h2></div><span className="section-index">001 — START HERE</span></div>
        <Link to="/prototype" className="experiment-card">
          <div className="experiment-thumbnail" aria-hidden="true"><PartArtwork visualKey="basketball" /><PartArtwork visualKey="platform-brick" /></div>
          <div className="experiment-copy"><span className="tag">First experiment</span><h3>The bounce test</h3><p>Drop a basketball onto brick. Watch gravity and bounce get to work.</p></div>
          <span className="experiment-arrow" aria-hidden="true">↗</span>
        </Link>
      </section>
    </main>
    <WorkshopFooter />
  </div>
}
