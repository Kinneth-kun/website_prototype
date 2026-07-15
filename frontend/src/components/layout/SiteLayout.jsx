import { useEffect, useState } from "react";
import { Link, NavLink, useLocation } from "react-router-dom";
import { Menu } from "lucide-react";
import { mallNavigation, publicNavigation } from "../../config/site";
import { useHeaderState, usePageEffects } from "../../hooks/usePageEffects";
import { SiteFooter } from "./SiteFooter";
import { Seo } from "./Seo";
import { assetUrl } from "../../config/site";
import { useSettings } from "../../hooks/useContent";

export function SiteLayout({ children, variant = "default" }) {
  const [open, setOpen] = useState(false);
  const scrolled = useHeaderState();
  const location = useLocation();
  const settings = useSettings();
  const parseLinks=(key,fallback)=>{try{return JSON.parse(settings[key])||fallback}catch{return fallback}};
  const primaryLinks=parseLinks("navigation.primary",publicNavigation),savedMallLinks=parseLinks("navigation.mall",mallNavigation),mallLinks=savedMallLinks.some(([to])=>to==="/visit-planner")?savedMallLinks:[...savedMallLinks,["/visit-planner","Visit Planner"]];
  usePageEffects();
  useEffect(()=>{setOpen(false)},[location.pathname]);
  return <>
    <Seo/><a className="skip-link" href="#main-content">Skip to main content</a>
    <header className={`header public-header ${variant}-header${scrolled ? " scrolled" : ""}${open ? " menu-open" : ""}`}>
      <Link className="brand" to="/"><img src={assetUrl(settings["general.logo_url"]||"images/general_images/icm_logo_transparent.png")} alt="Island Central Mactan"/></Link>
      <button className="menu" onClick={() => setOpen(!open)} aria-expanded={open} aria-label="Toggle navigation"><Menu/></button>
      <nav className={open ? "open" : ""} aria-label="Primary navigation">{primaryLinks.map(([to,label]) => to === "/mall" ?
        <div className={`nav-dropdown${["/mall","/directory","/events","/visit-planner"].includes(location.pathname) ? " active" : ""}`} key={to}>
          <NavLink className="nav-parent" to={to} aria-haspopup="true" onClick={() => setOpen(false)}>{label}</NavLink>
          <div className="nav-menu" aria-label="Mall pages" role="menu">{mallLinks.map(([childTo,childLabel]) => <NavLink role="menuitem" key={childTo} to={childTo} onClick={() => setOpen(false)}>{childLabel}</NavLink>)}</div>
        </div> : <NavLink className={to === "/inquire" ? "nav-cta" : undefined} key={to} to={to} onClick={() => setOpen(false)}>{label}</NavLink>)}</nav>
    </header>
    <div id="main-content" tabIndex="-1">{children}</div>
    <SiteFooter/>
  </>;
}
