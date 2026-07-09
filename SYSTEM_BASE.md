# Island Central Mactan Website System Base

This site is a static marketing and information system for Island Central Mactan.
It is organized around four main visitor jobs:

- Explore the mall, stores, services, cinema, and events.
- Browse the tenant directory by category, floor, and search terms.
- Review leasing opportunities and available space types.
- Send leasing, marketing, services, or general inquiries.

## Core Files

- `index.html` is the main landing page and route into the rest of the site.
- `mall.html`, `event-promos.html`, `services.html`, and `about.html` hold visitor-facing content.
- `directory.html`, `directory-data.js`, and `directory.js` form the tenant directory module.
- `leasing.html` and the available-space code in `script.js` form the leasing browser module.
- `inquire.html` and the contact form code in `script.js` prepare inquiry emails.
- `styles.css` is the shared design system, layout, responsive behavior, and component styling.

## Content Rules

- Put tenant names, categories, logos, lease types, and locations in `directory-data.js`.
- Put interactive directory behavior only in `directory.js`.
- Put shared interactions, sliders, reveal animation, inquiry mailto logic, and leasing-space filtering in `script.js`.
- Keep local tunnel binaries, generated logs, and runtime output out of version control.

## Refinement Direction

The current site remains static for simple hosting. If the site grows, the next best upgrade is a light template/build layer so repeated header and footer markup can be maintained in one place.
