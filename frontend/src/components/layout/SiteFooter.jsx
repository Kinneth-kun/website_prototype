import { Link } from "react-router-dom";
import { SocialLinks } from "./SocialLinks";
import { useSettings } from "../../hooks/useContent";
import { assetUrl } from "../../config/site";

export function SiteFooter() {
  const settings=useSettings();
  const links=(key,fallback)=>{try{return JSON.parse(settings[key])||fallback}catch{return fallback}};
  const discover=links("footer.discover_links",[["/mall","The Mall"],["/directory","Directory"],["/events","Events & Promos"],["/services","Services"],["/leasing","Leasing Spaces"],["/about","About Island Central"]]);
  const connect=links("footer.connect_links",[["/leasing","Retail Leasing"],["/leasing","Dining Leasing"],["/events","Events & Collaborations"],["/inquire","Contact Us"],["/","Back to Home"]]);
  const legal=links("footer.legal_links",[["/privacy-policy","Privacy Policy"],["/terms-of-service","Terms of Service"],["/cookies-policy","Cookies Policy"]]);
  return <footer className="site-footer-restored">
    <div className="footer-main-restored">
      <div className="footer-column"><h3>Discover Your Hub</h3>{discover.map(([to,label],index)=><Link key={`${to}-${index}`} to={to}>{label}</Link>)}</div>
      <div className="footer-column"><h3>Connect &amp; Curate</h3>{connect.map(([to,label],index)=><Link key={`${to}-${index}`} to={to}>{label}</Link>)}</div>
      <div className="footer-brand-restored"><img src={assetUrl(settings["general.logo_url"]||"images/general_images/icm_logo_transparent.png")} alt="Island Central Mactan"/><strong>{settings["footer.tagline"]||"Mactan's Premier Destination"}</strong><SocialLinks settings={settings}/></div>
    </div>
    <div className="footer-bottom-restored"><p>&copy; {new Date().getFullYear()} {settings["footer.copyright"]||"Island Central Mactan. All rights reserved."}</p><div>{legal.map(([to,label])=><Link key={to} to={to}>{label}</Link>)}</div></div>
  </footer>;
}
