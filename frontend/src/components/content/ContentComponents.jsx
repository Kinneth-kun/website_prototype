import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { useContent, useSettings } from "../../hooks/useContent";
import { assetUrl } from "../../config/site";

export const Loading = () => <div className="loading">Loading content&hellip;</div>;
export function PageHero({ title, text, variant="mall", eyebrow="Island Central Mactan", backgroundUrl, children }) { const style=backgroundUrl?{backgroundImage:`linear-gradient(90deg,rgba(3,29,35,.9),rgba(3,29,35,.3)),url("${assetUrl(backgroundUrl)}")`}:undefined;return <section className={`page-hero page-hero-${variant}`} style={style}><div><p className="eyebrow">{eyebrow}</p><h1>{title}</h1><p>{text}</p>{children}</div></section>; }
export function Feature({ icon,title,text,to }) { return <article className="feature"><i>{icon}</i><h3>{title}</h3><p>{text}</p><Link to={to}>Explore &rarr;</Link></article>; }
export function TenantGrid({ tenants }) { return <div className="card-grid">{tenants.map(tenant => <article className="tenant" key={tenant.id}><div className="tenant-logo-stage">{tenant.logo_url ? <img src={assetUrl(tenant.logo_url)} alt={`${tenant.name} logo`} loading="lazy" decoding="async"/> : <div className="logo-fallback">{tenant.name[0]}</div>}</div><div>{tenant.tenant_type&&<small className="tenant-type-badge">{tenant.tenant_type}</small>}<span>{tenant.location_detail || "Island Central Mactan"}</span><h3>{tenant.name}</h3></div></article>)}</div>; }
export function ContentCards({ resource }) { const [items,loading]=useContent(resource); if (loading) return <Loading/>; return <div className="content-grid">{items.map(item => <article key={item.id}>{item.cover_image_url && <img src={assetUrl(item.cover_image_url)} alt={item.title||item.name||""} loading="lazy" decoding="async"/>}<span>{item.space_type||item.venue||item.location_detail||item.status}</span><h3>{item.title||item.name}</h3><p>{item.summary||item.description}</p>{item.action_url && <a className="text-link card-action" href={item.action_url} target="_blank" rel="noreferrer">{item.action_label||"Learn more"} &rarr;</a>}</article>)}</div>; }

export function HoursStrip() {
  const [hours] = useContent("mall_hours"); const settings=useSettings(),now=new Date(); const today=hours.find(h=>+h.day_of_week===now.getDay());
  const minutes=now.getHours()*60+now.getMinutes(), opening=today?+today.opening_time.slice(0,2)*60:0, closing=today?+today.closing_time.slice(0,2)*60:0;
  const open=today&&!today.is_closed&&minutes>=opening&&minutes<closing;
  return <section className="hours-strip"><div><span>Mall hours</span><strong>{settings["hours.title"]||"Open daily for shopping, dining, services, and entertainment."}</strong><p>{settings["hours.schedule"]||"10:00 AM–9:00 PM Monday–Thursday · 9:00 AM–9:00 PM Friday–Sunday"}</p></div><div><span>Mall status</span><strong>{open?"Open now":"Closed"}</strong><p>{today?`${today.opening_time.slice(0,5)} - ${today.closing_time.slice(0,5)}`:"Loading..."}</p></div></section>;
}

export function MediaFeature({ item }) {
  const images=typeof item.images==="string"?JSON.parse(item.images):item.images||[]; const [active,setActive]=useState(0);
  useEffect(()=>{if(window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;const timer=setInterval(()=>setActive(i=>(i+1)%Math.max(images.length,1)),4500);return()=>clearInterval(timer)},[images.length]);
  useEffect(()=>{if(images.length>1){const preload=new Image();preload.src=assetUrl(images[(active+1)%images.length])}},[active,images]);
  return <section className="media-feature" aria-roledescription="carousel" aria-label={item.title}><div className="media-stage" aria-live="polite">{images[active]&&<img key={images[active]} className="active" src={assetUrl(images[active])} alt={`${item.title}, image ${active+1} of ${images.length}`} decoding="async"/>}<button type="button" className="slide prev" aria-label="Previous image" onClick={()=>setActive((active-1+images.length)%images.length)}>&lsaquo;</button><button type="button" className="slide next" aria-label="Next image" onClick={()=>setActive((active+1)%images.length)}>&rsaquo;</button><div className="dots">{images.map((_,i)=><button type="button" aria-label={`Show image ${i+1}`} aria-current={i===active?"true":undefined} key={i} className={i===active?"active":""} onClick={()=>setActive(i)}/>)}</div></div><div className="media-copy"><p className="eyebrow">{item.eyebrow}</p><h2>{item.title}</h2><p>{item.description}</p><a className="text-link" href={item.button_url}>{item.button_text}</a></div></section>;
}

export function HighlightsCarousel({ items }) {
  const [active,setActive]=useState(0); if(!items.length)return <Loading/>; const show=index=>setActive((index+items.length)%items.length);
  return <section className="highlights-carousel" aria-label="Featured destinations"><div className="highlights-viewport"><MediaFeature key={items[active].id} item={items[active]}/></div><button type="button" aria-label="Previous feature" className="feature-arrow feature-prev" onClick={()=>show(active-1)}>&lsaquo;</button><button type="button" aria-label="Next feature" className="feature-arrow feature-next" onClick={()=>show(active+1)}>&rsaquo;</button><div className="feature-indicators">{items.map((item,index)=><button type="button" aria-label={`Show ${item.title}`} aria-current={index===active?"true":undefined} key={item.id} className={index===active?"active":""} onClick={()=>show(index)}/>)}</div></section>;
}
