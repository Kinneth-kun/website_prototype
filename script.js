const navToggle = document.querySelector(".nav-toggle");
const mainNav = document.querySelector(".main-nav");
const navLinks = document.querySelectorAll(".main-nav a");
const contactForm = document.querySelector(".contact-form");
const siteHeader = document.querySelector(".site-header");
const sliders = document.querySelectorAll("[data-slider]");
const featureCarousel = document.querySelector("[data-feature-carousel]");
const spaceCards = Array.from(document.querySelectorAll("[data-space-card]"));
const spaceFilterButtons = document.querySelectorAll("[data-space-filter]");
const spaceSearch = document.querySelector("[data-space-search]");
const spaceSort = document.querySelector("[data-space-sort]");
const spaceClear = document.querySelector("[data-space-clear]");
const spaceCount = document.querySelector("[data-space-count]");
const spaceEmpty = document.querySelector("[data-space-empty]");
const spaceResults = document.querySelector("[data-space-results]");
const mallStatus = document.querySelector("[data-mall-status]");
const mallStatusLabel = document.querySelector("[data-mall-status-label]");
const mallDateTime = document.querySelector("[data-mall-datetime]");
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

const updateMallHours = () => {
  if (!mallStatus || !mallDateTime) return;

  const now = new Date();
  const manilaParts = new Intl.DateTimeFormat("en-US", {
    timeZone: "Asia/Manila",
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
    second: "2-digit",
    hour12: true,
  }).formatToParts(now);

  const getPart = (type) => manilaParts.find((part) => part.type === type)?.value || "";
  const weekday = getPart("weekday");
  const hour = Number(getPart("hour"));
  const minute = Number(getPart("minute"));
  const dayPeriod = getPart("dayPeriod");
  const hour24 = dayPeriod === "PM" && hour !== 12 ? hour + 12 : dayPeriod === "AM" && hour === 12 ? 0 : hour;
  const currentMinutes = hour24 * 60 + minute;
  const isFridayToSunday = ["Friday", "Saturday", "Sunday"].includes(weekday);
  const openMinutes = (isFridayToSunday ? 9 : 10) * 60;
  const closeMinutes = 21 * 60;
  const minutesUntilOpen = openMinutes - currentMinutes;
  const minutesUntilClose = closeMinutes - currentMinutes;

  let status = "Closed";
  let label = "Mall status";

  if (currentMinutes >= openMinutes && currentMinutes < closeMinutes) {
    status = minutesUntilClose <= 60 ? "Closing soon" : "Open";
    label = `Closes at 9:00 PM`;
  } else if (currentMinutes < openMinutes && minutesUntilOpen <= 60) {
    status = "Opening soon";
    label = `Opens at ${isFridayToSunday ? "9:00 AM" : "10:00 AM"}`;
  } else {
    label = `Opens at ${isFridayToSunday ? "9:00 AM" : "10:00 AM"}`;
  }

  const dateText = `${weekday}, ${getPart("month")} ${getPart("day")}, ${getPart("year")} - ${hour}:${getPart("minute")}:${getPart("second")} ${dayPeriod}`;
  mallStatus.textContent = status;
  if (mallStatusLabel) mallStatusLabel.textContent = label;
  mallDateTime.textContent = dateText;
  mallDateTime.setAttribute("datetime", now.toISOString());
};

updateMallHours();
setInterval(updateMallHours, 1000);

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
  const note = contactForm.querySelector("[data-form-note]");
  const formData = new FormData(contactForm);
  const name = formData.get("name")?.toString().trim();
  const email = formData.get("email")?.toString().trim();
  const phone = formData.get("phone")?.toString().trim();
  const type = formData.get("type")?.toString().trim();
  const date = formData.get("date")?.toString().trim();
  const message = formData.get("message")?.toString().trim();

  if (!contactForm.checkValidity()) {
    contactForm.reportValidity();
    return;
  }

  const emailBody = [
    `Name: ${name}`,
    `Email: ${email}`,
    `Mobile: ${phone}`,
    `Inquiry type: ${type}`,
    `Preferred date: ${date || "Not specified"}`,
    "",
    "Message:",
    message,
  ].join("\n");

  const mailto = new URL("mailto:leasing.islandcentral@gmail.com");
  mailto.searchParams.set("subject", `Website inquiry - ${type}`);
  mailto.searchParams.set("body", emailBody);

  button.textContent = "Opening Email";
  if (note) note.textContent = "Your email app will open with the inquiry details prepared.";
  window.location.href = mailto.toString();

  setTimeout(() => {
    button.textContent = "Send Inquiry";
    if (note) note.textContent = "Submitting prepares an email to the Island Central Mactan team.";
  }, 3200);
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

if (spaceCards.length) {
  const spaceState = {
    type: "All",
    query: "",
    sort: "default",
  };

  const normalizeSpaceValue = (value) => value.toLowerCase().trim();

  const getSpaceHaystack = (card) =>
    normalizeSpaceValue(
      [
        card.dataset.spaceName,
        card.dataset.spaceType,
        card.dataset.spaceKeywords,
        card.textContent,
      ].join(" ")
    );

  const matchesSpace = (card) => {
    const matchesType = spaceState.type === "All" || card.dataset.spaceType === spaceState.type;
    const matchesQuery = !spaceState.query || getSpaceHaystack(card).includes(normalizeSpaceValue(spaceState.query));
    return matchesType && matchesQuery;
  };

  const sortSpaces = (cards) =>
    [...cards].sort((a, b) => {
      if (spaceState.sort === "name") {
        return a.dataset.spaceName.localeCompare(b.dataset.spaceName);
      }

      if (spaceState.sort === "type") {
        return a.dataset.spaceType.localeCompare(b.dataset.spaceType);
      }

      return spaceCards.indexOf(a) - spaceCards.indexOf(b);
    });

  const renderSpaces = () => {
    const orderedCards = sortSpaces(spaceCards);
    let visibleCount = 0;

    orderedCards.forEach((card) => {
      const isVisible = matchesSpace(card);
      card.hidden = !isVisible;
      if (isVisible) visibleCount += 1;
      spaceResults?.appendChild(card);
    });

    spaceFilterButtons.forEach((button) => {
      button.classList.toggle("active", button.dataset.spaceFilter === spaceState.type);
    });

    if (spaceCount) spaceCount.textContent = `${visibleCount} ${visibleCount === 1 ? "space" : "spaces"}`;
    if (spaceEmpty) spaceEmpty.hidden = visibleCount > 0;
  };

  spaceFilterButtons.forEach((button) => {
    button.addEventListener("click", () => {
      spaceState.type = button.dataset.spaceFilter;
      renderSpaces();
    });
  });

  spaceSearch?.addEventListener("input", (event) => {
    spaceState.query = event.target.value;
    renderSpaces();
  });

  spaceSort?.addEventListener("change", (event) => {
    spaceState.sort = event.target.value;
    renderSpaces();
  });

  spaceClear?.addEventListener("click", () => {
    spaceState.type = "All";
    spaceState.query = "";
    spaceState.sort = "default";
    if (spaceSearch) spaceSearch.value = "";
    if (spaceSort) spaceSort.value = "default";
    renderSpaces();
  });

  renderSpaces();
}
