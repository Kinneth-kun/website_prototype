const navToggle = document.querySelector(".nav-toggle");
const mainNav = document.querySelector(".main-nav");
const navLinks = document.querySelectorAll(".main-nav a");
const contactForm = document.querySelector(".contact-form");
const siteHeader = document.querySelector(".site-header");
const sliders = document.querySelectorAll("[data-slider]");
const featureCarousel = document.querySelector("[data-feature-carousel]");
const revealTargets = document.querySelectorAll(
  [
    ".section-heading",
    ".quick-info article",
    ".mall-stats article",
    ".leasing-stats article",
    ".directory-card",
    ".event-card",
    ".tenant-grid article",
    ".promo-list article",
    ".skybar-feature",
    ".leasing-panel",
    ".leasing-media-card",
    ".available-space-listing",
    ".space-grid article",
    ".leasing-benefits article",
    ".leasing-process article",
    ".leasing-cta > *",
    ".about-intro",
    ".about-media",
    ".values-grid article",
    ".services-grid article",
    ".contact-section > *",
    ".location-section > *",
    ".policy-content article",
    ".footer-main > *",
  ].join(",")
);

document.body.classList.add("is-loaded");

const updateHeaderState = () => {
  siteHeader?.classList.toggle("scrolled", window.scrollY > 24);
};

updateHeaderState();
window.addEventListener("scroll", updateHeaderState, { passive: true });

navToggle?.addEventListener("click", () => {
  const isOpen = mainNav.classList.toggle("open");
  siteHeader?.classList.toggle("menu-open", isOpen);
  navToggle.setAttribute("aria-expanded", String(isOpen));
});

navLinks.forEach((link) => {
  link.addEventListener("click", () => {
    mainNav.classList.remove("open");
    siteHeader?.classList.remove("menu-open");
    navToggle?.setAttribute("aria-expanded", "false");
  });
});

contactForm?.addEventListener("submit", (event) => {
  event.preventDefault();
  const button = contactForm.querySelector("button");
  button.textContent = "Inquiry Prepared";
  setTimeout(() => {
    button.textContent = "Send Inquiry";
  }, 2200);
});

const prepareRevealTarget = (target, index) => {
  target.classList.add("reveal");
  target.style.setProperty("--reveal-delay", `${Math.min(index % 6, 5) * 70}ms`);

  if (target.matches(".about-media, .location-section > :last-child, .contact-form")) {
    target.classList.add("reveal-right");
  } else if (target.matches(".about-intro, .location-section > :first-child, .contact-section > :first-child")) {
    target.classList.add("reveal-left");
  } else if (target.matches(".tenant-grid article, .directory-card, .event-card, .services-grid article")) {
    target.classList.add("reveal-scale");
  }
};

revealTargets.forEach(prepareRevealTarget);

if ("IntersectionObserver" in window) {
  const revealObserver = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      });
    },
    { threshold: 0.14, rootMargin: "0px 0px -70px 0px" }
  );

  revealTargets.forEach((target) => revealObserver.observe(target));
} else {
  revealTargets.forEach((target) => target.classList.add("is-visible"));
}

sliders.forEach((slider) => {
  const slides = Array.from(slider.querySelectorAll(".skybar-track img"));
  const dots = Array.from(slider.querySelectorAll("[data-slide-to]"));
  const prevButton = slider.querySelector("[data-slider-prev]");
  const nextButton = slider.querySelector("[data-slider-next]");
  let currentSlide = 0;

  const showSlide = (index) => {
    currentSlide = (index + slides.length) % slides.length;
    slides.forEach((slide, slideIndex) => {
      slide.classList.toggle("active", slideIndex === currentSlide);
    });
    dots.forEach((dot, dotIndex) => {
      dot.classList.toggle("active", dotIndex === currentSlide);
    });
  };

  prevButton?.addEventListener("click", () => showSlide(currentSlide - 1));
  nextButton?.addEventListener("click", () => showSlide(currentSlide + 1));
  dots.forEach((dot) => {
    dot.addEventListener("click", () => showSlide(Number(dot.dataset.slideTo)));
  });
});

if (featureCarousel) {
  const track = featureCarousel.querySelector(".highlights-track");
  const pages = Array.from(featureCarousel.querySelectorAll(".skybar-feature"));
  const dots = Array.from(featureCarousel.querySelectorAll("[data-feature-to]"));
  const prevButton = featureCarousel.querySelector("[data-feature-prev]");
  const nextButton = featureCarousel.querySelector("[data-feature-next]");
  let currentPage = 0;

  const showFeature = (index) => {
    currentPage = (index + pages.length) % pages.length;
    track.style.transform = `translateX(-${currentPage * 100}%)`;
    dots.forEach((dot, dotIndex) => {
      dot.classList.toggle("active", dotIndex === currentPage);
    });
  };

  prevButton?.addEventListener("click", () => showFeature(currentPage - 1));
  nextButton?.addEventListener("click", () => showFeature(currentPage + 1));
  dots.forEach((dot) => {
    dot.addEventListener("click", () => showFeature(Number(dot.dataset.featureTo)));
  });
}
