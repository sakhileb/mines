---
name: web-design-guidelines
description: 'Comprehensive web design standards and guidelines covering layout systems, component patterns, content hierarchy, imagery, iconography, motion design, performance-aware design, and cross-browser compatibility. Use when: establishing design standards for a project, reviewing designs against best practices, making decisions about layout grids, choosing icon sets, defining motion/animation standards, or documenting design conventions for a team.'
argument-hint: 'Describe the design decision or guideline area you need help with'
---

# Web Design Guidelines

A reference for design standards, conventions, and best practices for building high-quality web products.

## When to Use

- Establishing or documenting design standards for a project or team
- Making decisions about layout, grid, spacing, or component patterns
- Reviewing designs or implementations against best practices
- Standardizing icon usage, imagery, and motion across a product
- Ensuring designs are performant, responsive, and cross-browser compatible

---

## 1. Layout System

### Grid
- Use a **12-column grid** for complex layouts; **4-column** for mobile
- Standard column gutters: **16px** (mobile), **24px** (tablet), **32px** (desktop)
- Page max-width: `1280px` for regular content, `1536px` for dashboards
- Use CSS Grid for 2D layouts; Flexbox for 1D (rows or columns)

### Page Structure
```
┌──────────────────────────────────────┐
│ Header / Navbar (sticky or scroll)   │
├────────┬─────────────────────────────┤
│ Sidebar│ Main Content Area           │  ← Dashboard
│ (nav)  │                             │
├────────┴─────────────────────────────┤
│ Footer                               │
└──────────────────────────────────────┘
```

```
┌──────────────────────────────────────┐
│ Header                               │
├──────────────────────────────────────┤
│         Centered Content             │  ← Marketing / Blog
│         max-w-prose (~65ch)          │
├──────────────────────────────────────┤
│ Footer                               │
└──────────────────────────────────────┘
```

### Spacing Scale (8-point system)
| Token | Value | Use |
|-------|-------|-----|
| `space-1` | 4px | Tight inline gaps |
| `space-2` | 8px | Icon + label gaps, small padding |
| `space-3` | 12px | Input padding |
| `space-4` | 16px | Card padding (small), section gaps |
| `space-6` | 24px | Card padding, form group spacing |
| `space-8` | 32px | Section spacing, panel padding |
| `space-12` | 48px | Major section breaks |
| `space-16` | 64px | Hero padding, page-level spacing |
| `space-24` | 96px | Section separators |

---

## 2. Typography Guidelines

### Type Hierarchy
| Role | Size | Weight | Line Height |
|------|------|--------|-------------|
| Display | 48–72px | 700–800 | 1.1 |
| H1 | 36–48px | 700 | 1.15 |
| H2 | 28–36px | 600–700 | 1.2 |
| H3 | 22–28px | 600 | 1.25 |
| H4 | 18–22px | 600 | 1.3 |
| Body Large | 18px | 400 | 1.6 |
| Body | 16px | 400 | 1.5–1.6 |
| Body Small | 14px | 400 | 1.5 |
| Caption | 12px | 400–500 | 1.4 |
| Overline | 10–12px | 500–600 | 1.4, uppercase, tracked |

### Font Loading
```html
<!-- Preconnect to font origin -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<!-- Preload for critical fonts -->
<link rel="preload" as="font" type="font/woff2" href="/fonts/inter.woff2" crossorigin>
```

### Rules
- **Limit font families to 2**: one for headings (character, display), one for body (neutral, readable)
- **Minimum body font size**: 16px (never below 14px for body text)
- **Max line length (measure)**: 65–75 characters for prose (use `max-w-prose` in Tailwind)
- **Avoid full uppercase** for long text — use for labels, overlines, badges only
- **Ensure adequate tracking** for uppercase text: `letter-spacing: 0.05–0.1em`

---

## 3. Component Guidelines

### Buttons
| Type | Use Case |
|------|----------|
| **Primary** | One per section, main CTA (Create, Save, Continue) |
| **Secondary** | Alternative/supporting actions |
| **Destructive** | Dangerous irreversible actions (Delete, Remove) |
| **Ghost/Outline** | Low-emphasis actions in busy areas |
| **Link** | In-line navigation or de-emphasized actions |

- Minimum touch target: **44×44px**
- Button text: imperative verbs ("Save", "Create project", "Send message")
- Loading state: replace text with spinner + "Saving…" (use `aria-busy`)
- Never disable buttons without explanation; instead show a tooltip on hover

### Forms
- Top-aligned labels (not floating, not placeholder-only)
- Visible helper text below input when input rules are non-obvious
- Inline validation on blur (not on keystroke, not only on submit)
- Group related fields with fieldsets and legends for accessibility
- Single-column forms preferred; multi-column only for address or similar paired data

### Navigation
| Pattern | When to Use |
|---------|-------------|
| Top navbar | Primary navigation, ≤ 7 items, marketing/content sites |
| Left sidebar | App navigation with many sections (dashboards, admin) |
| Bottom tab bar | Mobile apps, primary destinations (3–5 items) |
| Breadcrumbs | Deep hierarchies (3+ levels) |
| Tabs | Switching between related views on the same page |

### Cards
- Use consistent border-radius within a product (usually `rounded-lg` or `rounded-xl`)
- Use shadow OR border, not both (unless card is elevated/active)
- Header, body, and footer regions should be visually consistent across all cards

### Tables
- Use `<table>` with `<th scope="col/row">` for tabular data
- Sticky header for tables with > 10 rows
- Align numbers right, text left
- Provide row hover state for large tables
- Include empty state and loading skeleton

---

## 4. Imagery & Illustration

### Photos
- Use authentic photography; avoid generic stock photos of "business people smiling"
- Optimize: WebP format, `srcset` for responsive images, `loading="lazy"` for below-fold
- Always include meaningful `alt` text
- Use consistent aspect ratios within a component type (e.g., all article cards: 16:9)

### Illustrations
- Use a consistent style within the product (flat, line art, isometric — pick one)
- Ensure illustrations work in both light and dark mode
- Provide meaningful empty states with contextual illustrations, not a generic "no data" icon

### Image Guidelines
```html
<!-- Responsive image with art direction -->
<picture>
  <source srcset="hero-desktop.webp" media="(min-width: 768px)" type="image/webp">
  <img src="hero-mobile.webp" alt="Team collaboration in a modern office" loading="eager" width="1200" height="630">
</picture>
```

---

## 5. Iconography

### Icon System Rules
- Use a **single icon library** consistently (Lucide, Heroicons, or Phosphor — not mixed)
- Default icon size: **16px** inline text, **20px** standalone, **24px** headers
- Icons must have accessible labels when used alone (see web-accessibility skill)
- Ensure icons render at specified sizes without blurriness (use `viewBox="0 0 24 24"`)
- Prefer `stroke` icons over `fill` icons for UI (cleaner, more neutral)

### When to Use Icons
- Use icons to **reinforce** text labels, not replace them (except well-known icons: ×, ✓, ←, →)
- Use icons for navigation items, status indicators, and category differentiation
- Avoid using too many icons — they create noise without conveying meaning

---

## 6. Motion & Animation

### Timing Guidelines
| Interaction | Duration | Easing |
|-------------|----------|--------|
| Hover color changes | 100–150ms | `ease-out` |
| Button press / feedback | 100ms | `ease-out` |
| Small element shows/hides | 150–200ms | `ease-out` (enter) / `ease-in` (exit) |
| Modal / drawer open | 200–300ms | `cubic-bezier(0.16, 1, 0.3, 1)` (spring-like) |
| Page transitions | 200–300ms | `ease-in-out` |
| Loading skeleton | Continuous 1.5s | `ease-in-out` loop |

### Animation Principles
1. **Purposeful**: animations communicate state, not just decorate
2. **Responsive**: don't block user interaction with long animations
3. **Respectful**: honor `prefers-reduced-motion`
4. **Consistent**: use the same easing and timing for similar interactions

---

## 7. Performance-Aware Design

- **Avoid heavy gradients** on frequently repainted elements (use `transform`/`opacity` instead)
- **Limit box-shadow usage** on scrolling containers — use `border` instead
- **Prefer CSS over JS** for animations — CSS `transition` and `@keyframes` run on the compositor
- **Be mindful of font weights** — each weight is a separate file download
- **Test on low-end devices** — designs that look great on a MacBook Pro may lag on a budget Android
- **Use skeleton screens** instead of spinners for layout-preserving loading states
- **Lazy load** off-screen images and components

---

## 8. Cross-Browser & Platform Compatibility

### Browser Support Tiers
| Tier | Browsers | Required |
|------|----------|----------|
| A | Chrome, Firefox, Safari, Edge (latest) | Full support |
| B | Safari (1 version back), Samsung Internet | Progressive enhancement |
| C | IE11 | Not required (transpile only if contractually required) |

### Common Gotchas
- `gap` in flex: supported in all modern browsers; fine to use
- CSS Grid subgrid: check caniuse before using
- `color-mix()`, `container queries`: check caniuse — progressive enhance
- iOS Safari: `100vh` includes browser chrome — use `100dvh` (dynamic viewport height)
- Custom scrollbars: style with `::-webkit-scrollbar` for WebKit; use `scrollbar-width: thin` for Firefox

---

## Design Review Checklist

- [ ] Layout uses consistent grid and spacing scale
- [ ] Typography scale applied correctly (sizes, weights, line heights)
- [ ] Interactive component states defined (default, hover, focus, active, disabled, loading)
- [ ] Color usage follows semantic rules (no arbitrary colors)
- [ ] Contrast meets WCAG AA at minimum
- [ ] Responsive breakpoints specified
- [ ] Dark mode handled (if applicable)
- [ ] Empty, loading, and error states designed for all data surfaces
- [ ] Icons are consistent in style and appropriately sized
- [ ] Motion is purposeful and respects `prefers-reduced-motion`
- [ ] Images are optimized and have alt text
- [ ] Component patterns are reused from design system (not reinvented)

---

## Procedure

1. **Audit against guidelines** — Review the component or page against each section above
2. **Identify deviations** — Note inconsistencies with spacing, typography, color, or patterns
3. **Apply fixes** — Provide specific, concrete values (px, color codes, CSS/Tailwind classes)
4. **Document decisions** — Record any intentional deviations from these guidelines and why
5. **Validate** — Re-check against the Design Review Checklist before shipping
