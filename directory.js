const directoryRoot = document.querySelector("[data-directory-results]");
const directorySearch = document.querySelector("[data-directory-search]");
const categoryFilters = document.querySelector("[data-category-filters]");
const directoryEmpty = document.querySelector("[data-directory-empty]");
const resultCount = document.querySelector("[data-result-count]");
const totalTenants = document.querySelector("[data-total-tenants]");
const totalCategories = document.querySelector("[data-total-categories]");
const totalFeatured = document.querySelector("[data-total-featured]");
const viewButtons = document.querySelectorAll("[data-view]");

const tenants = Array.isArray(window.TENANTS) ? window.TENANTS : [];
const state = {
  query: "",
  category: "All",
  view: "grid",
  filtersOpen: false,
};

const getInitials = (name) =>
  name
    .replace(/[^a-zA-Z0-9\s]/g, " ")
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((word) => word[0])
    .join("")
    .toUpperCase();

const normalize = (value) => value.toLowerCase().trim();

const createFilterButton = (label, type) => {
  const button = document.createElement("button");
  button.type = "button";
  button.textContent = label;
  button.dataset.filterType = type;
  button.dataset.filterValue = label;
  return button;
};

const renderFilterGroup = (container, values, type) => {
  if (!container) return;
  container.innerHTML = "";
  ["All", ...values].forEach((value) => {
    const button = createFilterButton(value, type);
    button.classList.toggle("active", state[type] === value);
    container.appendChild(button);
  });
};

const matchesTenant = (tenant) => {
  const haystack = normalize(`${tenant.name} ${tenant.category} ${tenant.location || ""}`);
  const matchesQuery = !state.query || haystack.includes(normalize(state.query));
  const matchesCategory = state.category === "All" || tenant.category === state.category;
  return matchesQuery && matchesCategory;
};

const featuredNames = new Set([
  "Cebuana Lhuillier",
  "Core Pacific",
  "RD Pawnshop",
  "Robinson's Supermarket",
  "Security Bank",
  "Southstar Drugstore",
  "Executive Optical",
  "Goldilocks",
  "Leylam",
  "Master Siomai",
  "Thirsty",
  "National Bookstore",
  "Royal Spoon Inc. (Jollibee)",
  "Kuya J",
  "Timezone",
  "McDonald's",
  "Watsons",
  "ZUS Coffee",
  "Ace Hardware",
  "Penshoppe",
  "Regatta",
]);

const renderTenantCard = (tenant, index) => {
  const article = document.createElement("article");
  article.className = "directory-tenant-card";
  article.style.setProperty("--card-delay", `${Math.min(index, 18) * 35}ms`);

  const logoMarkup = tenant.logo
    ? `<img src="${tenant.logo}" alt="${tenant.name} logo" loading="lazy" />`
    : `<span>${getInitials(tenant.name)}</span>`;

  article.innerHTML = `
    <div class="tenant-logo ${tenant.logo ? "" : "tenant-logo-fallback"}">
      ${featuredNames.has(tenant.name) ? '<span class="featured-badge">Featured</span>' : ""}
      ${logoMarkup}
    </div>
    <div class="tenant-card-body">
      <div class="tenant-card-topline">
        <span class="tenant-category">${tenant.category}</span>
      </div>
      <h3>${tenant.name}</h3>
      <span class="tenant-category-line">${tenant.category}</span>
      <span class="tenant-location">${tenant.location || "Location to be confirmed"}</span>
      <p>${getTenantDescription(tenant)}</p>
      <a class="tenant-action" href="inquire.html">View details</a>
    </div>
  `;

  return article;
};

const getTenantDescription = (tenant) => {
  const descriptions = {
    "Dining & Food": "Restaurants, snacks, coffee, desserts, and everyday food stops.",
    "Retail & Essentials": "Shops, essentials, lifestyle finds, and convenient retail services.",
    "Health & Wellness": "Health, beauty, fitness, optical, and personal care services.",
    "Banking & Finance": "Banking, ATM, payment, pawnshop, and financial support.",
    "Gadgets & Tech": "Mobile, gadget, repair, accessories, and telecom services.",
    Entertainment: "Cinema, play, leisure, and family entertainment experiences.",
    Services: "Daily errands, travel, printing, auto, and commercial services.",
    "Government & Offices": "Government, office, permit, and public-service access.",
  };

  return descriptions[tenant.category] || "Tenant available at Island Central Mactan.";
};

const updateMetrics = (filteredTenants) => {
  if (totalTenants) totalTenants.textContent = tenants.length;
  if (totalCategories) totalCategories.textContent = new Set(tenants.map((tenant) => tenant.category)).size;
  if (totalFeatured) totalFeatured.textContent = tenants.filter((tenant) => featuredNames.has(tenant.name)).length;
  if (resultCount) resultCount.textContent = filteredTenants.length;
};

const renderDirectory = () => {
  if (!directoryRoot) return;

  const filteredTenants = tenants.filter(matchesTenant);
  directoryRoot.classList.toggle("list-view", state.view === "list");
  directoryRoot.innerHTML = "";
  filteredTenants.forEach((tenant, index) => directoryRoot.appendChild(renderTenantCard(tenant, index)));

  if (directoryEmpty) directoryEmpty.hidden = filteredTenants.length > 0;
  updateMetrics(filteredTenants);
};

const categories = [...new Set(tenants.map((tenant) => tenant.category))].sort();
renderFilterGroup(categoryFilters, categories, "category");
renderDirectory();

directorySearch?.addEventListener("input", (event) => {
  state.query = event.target.value;
  renderDirectory();
});

[categoryFilters].forEach((container) => {
  container?.addEventListener("click", (event) => {
    const button = event.target.closest("button");
    if (!button) return;
    state[button.dataset.filterType] = button.dataset.filterValue;
    renderFilterGroup(categoryFilters, categories, "category");
    renderDirectory();
  });
});

viewButtons.forEach((button) => {
  button.addEventListener("click", () => {
    state.view = button.dataset.view;
    viewButtons.forEach((item) => item.classList.toggle("active", item === button));
    renderDirectory();
  });
});

const filterToggle = document.querySelector("[data-filter-toggle]");
const filterPanel = document.querySelector("[data-filter-panel]");

filterToggle?.addEventListener("click", () => {
  state.filtersOpen = !state.filtersOpen;
  filterPanel?.classList.toggle("open", state.filtersOpen);
  filterToggle.setAttribute("aria-expanded", String(state.filtersOpen));
});
