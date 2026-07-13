import { useEffect, useState } from "react";
import { useLocation } from "react-router-dom";

export function useHeaderState() {
  const [scrolled, setScrolled] = useState(false);
  useEffect(() => {
    const update = () => setScrolled(scrollY > 36);
    update(); addEventListener("scroll", update, { passive: true });
    return () => removeEventListener("scroll", update);
  }, []);
  return scrolled;
}

export function usePageEffects() {
  const location = useLocation();
  useEffect(() => {
    scrollTo({ top: 0, behavior: "auto" });
    let observer;
    const timer = setTimeout(() => {
      const selector = ".hours-strip>div,.section>h2,.section>.eyebrow,.feature,.tenant,.content-grid>article,.media-feature,.space-list>article,.stats>article,.about-story>*,.values-grid>article,.form";
      const elements = [...document.querySelectorAll(selector)];
      elements.forEach((element, index) => {
        element.classList.add("reveal-ready");
        element.style.setProperty("--reveal-delay", `${(index % 6) * 70}ms`);
      });
      observer = new IntersectionObserver(entries => entries.forEach(entry => {
        if (entry.isIntersecting) { entry.target.classList.add("revealed"); observer.unobserve(entry.target); }
      }), { threshold: .1 });
      elements.forEach(element => observer.observe(element));
    }, 30);
    return () => { clearTimeout(timer); observer?.disconnect(); };
  }, [location.pathname]);
}
