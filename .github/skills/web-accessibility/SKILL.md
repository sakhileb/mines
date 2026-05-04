---
name: web-accessibility
description: 'Web accessibility (a11y) guidance covering WCAG 2.1/2.2, ARIA patterns, keyboard navigation, screen reader support, focus management, accessible forms, color contrast, and testing. Use when: building accessible components, auditing for a11y issues, adding ARIA attributes, implementing keyboard navigation, fixing focus management, ensuring screen reader compatibility, or meeting WCAG AA/AAA compliance.'
argument-hint: 'Describe the component or a11y issue you need help with'
---

# Web Accessibility (a11y)

Comprehensive guidance for building inclusive, accessible web experiences that comply with WCAG 2.1/2.2 and work for all users.

## When to Use

- Building or reviewing accessible UI components
- Auditing a page or flow for WCAG compliance
- Adding ARIA attributes and roles correctly
- Implementing keyboard navigation and focus management
- Ensuring screen reader compatibility (NVDA, JAWS, VoiceOver)
- Making forms, modals, and complex widgets accessible
- Meeting WCAG 2.1 AA or AAA requirements

---

## WCAG 2.1 Quick Reference

WCAG is organized around 4 principles: **POUR**

| Principle | Meaning | Key Guidelines |
|-----------|---------|---------------|
| **Perceivable** | Content must be perceivable by all users | Alt text, captions, color contrast, resizable text |
| **Operable** | UI must be operable without a mouse | Keyboard nav, no seizure-inducing content, sufficient time |
| **Understandable** | Content and UI must be understandable | Readable text, predictable behavior, error assistance |
| **Robust** | Content must work with assistive technologies | Valid HTML, ARIA correct |

### Conformance Levels
- **A**: Minimum — fix these first
- **AA**: Required for most legal/government standards — target this
- **AAA**: Enhanced accessibility — aspirational

---

## Color & Contrast

### Minimum Contrast Ratios (WCAG AA)
| Text Type | Minimum Ratio |
|-----------|--------------|
| Normal text (< 18pt / < 14pt bold) | **4.5:1** |
| Large text (≥ 18pt / ≥ 14pt bold) | **3:1** |
| UI components & graphical objects | **3:1** |
| Decorative / logos / disabled | No requirement |

### WCAG AAA
| Text Type | AAA Ratio |
|-----------|----------|
| Normal text | **7:1** |
| Large text | **4.5:1** |

**Never convey information by color alone.** Always pair color with icons, labels, or patterns.

```jsx
// Bad: only color communicates error
<input className="border-red-500" />

// Good: color + icon + text
<input className="border-red-500" aria-describedby="error-msg" aria-invalid="true" />
<p id="error-msg" className="text-red-600 flex items-center gap-1">
  <AlertCircle className="h-4 w-4" aria-hidden="true" />
  This field is required
</p>
```

---

## Semantic HTML First

Use semantic HTML before reaching for ARIA. Semantics are free accessibility.

```html
<!-- Headings (one h1 per page, logical order) -->
<h1>Page Title</h1>
  <h2>Section</h2>
    <h3>Subsection</h3>

<!-- Landmarks -->
<header>   <!-- banner landmark -->
<nav>      <!-- navigation landmark -->
<main>     <!-- main landmark (one per page) -->
<aside>    <!-- complementary landmark -->
<footer>   <!-- contentinfo landmark -->
<section aria-labelledby="section-heading">

<!-- Lists -->
<ul> / <ol> / <li>  <!-- for related groups of items -->
<dl> / <dt> / <dd>  <!-- for definition lists / key-value pairs -->

<!-- Tables -->
<table>
  <caption>Monthly Sales Data</caption>
  <thead><tr><th scope="col">Month</th></tr></thead>
  <tbody><tr><td>January</td></tr></tbody>
</table>

<!-- Buttons vs Links -->
<!-- Button: triggers action (submit, toggle, open modal) -->
<button type="button" onClick={...}>Open Menu</button>
<!-- Link: navigates to a URL -->
<a href="/about">About Us</a>
```

---

## ARIA — When and How

**First rule of ARIA:** Don't use ARIA if native HTML works.

### Essential ARIA Patterns

#### Labeling
```jsx
// aria-label: short, descriptive name (use when no visible label)
<button aria-label="Close dialog">×</button>

// aria-labelledby: reference visible text
<section aria-labelledby="settings-heading">
  <h2 id="settings-heading">Settings</h2>
</section>

// aria-describedby: extra description
<input aria-describedby="password-hint" />
<p id="password-hint">Must be at least 8 characters.</p>
```

#### Live Regions
```jsx
// For dynamic content updates (search results, notifications)
<div aria-live="polite" aria-atomic="true">
  {status && <p>{status}</p>}
</div>

// For urgent alerts (errors)
<div role="alert">{errorMessage}</div>
```

#### Modal / Dialog
```jsx
<div
  role="dialog"
  aria-modal="true"
  aria-labelledby="dialog-title"
  aria-describedby="dialog-description"
>
  <h2 id="dialog-title">Confirm Delete</h2>
  <p id="dialog-description">This action cannot be undone.</p>
  {/* Focus trapped inside */}
</div>
```

#### Expandable / Accordion
```jsx
<button
  aria-expanded={isOpen}
  aria-controls="panel-id"
>
  Toggle Section
</button>
<div id="panel-id" hidden={!isOpen}>
  Content
</div>
```

#### Loading States
```jsx
<button aria-busy={isLoading} disabled={isLoading}>
  {isLoading ? 'Saving...' : 'Save'}
</button>
```

---

## Keyboard Navigation

### Required Keyboard Support
| Key | Action |
|-----|--------|
| `Tab` | Move focus forward |
| `Shift+Tab` | Move focus backward |
| `Enter` | Activate button / follow link |
| `Space` | Toggle checkbox / activate button |
| `Arrow keys` | Navigate within composite widgets (menus, radios, tabs) |
| `Escape` | Close modal / cancel action |
| `Home` / `End` | First / last item in list |

### Focus Management Rules
- **Never remove focus outline** — Override with custom styles, not `outline: none`
- **Trap focus in modals** — On open, move focus to first focusable element; on close, return focus to trigger
- **Skip links** — Provide "Skip to main content" as the first focusable element
- **Focus order** — Must follow visual reading order (DOM order = tab order when no `tabindex`)
- **`tabindex` rules**:
  - `tabindex="0"` — Make non-focusable element focusable (e.g., `<div role="button">`)
  - `tabindex="-1"` — Focusable only via script (for focus management)
  - `tabindex="1+"` — **Avoid** — breaks natural tab order

```jsx
// Skip link (place as first child of body)
<a
  href="#main-content"
  className="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:rounded focus:shadow-lg"
>
  Skip to main content
</a>
<main id="main-content" tabIndex={-1}>
```

---

## Accessible Forms

```jsx
// Every input needs a label
<label htmlFor="email">Email address</label>
<input id="email" type="email" name="email" />

// Required fields
<input required aria-required="true" />

// Error state
<input
  id="email"
  aria-invalid={!!errors.email}
  aria-describedby={errors.email ? "email-error" : "email-hint"}
/>
{errors.email && (
  <p id="email-error" role="alert" className="text-red-600 text-sm mt-1">
    {errors.email.message}
  </p>
)}
<p id="email-hint" className="text-gray-500 text-sm mt-1">
  We'll never share your email.
</p>
```

---

## Screen Reader Utilities

```css
/* Visually hidden but accessible to screen readers */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
```

```jsx
// Hide decorative icons from screen readers
<svg aria-hidden="true" focusable="false">...</svg>

// Provide accessible text for icon-only buttons
<button>
  <TrashIcon aria-hidden="true" className="h-4 w-4" />
  <span className="sr-only">Delete item</span>
</button>
```

---

## Reduced Motion

```css
@media (prefers-reduced-motion: reduce) {
  *, ::before, ::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

```jsx
// In Tailwind
className="motion-safe:animate-bounce"
className="motion-reduce:transition-none"
```

---

## a11y Testing Tools

| Tool | Type | Purpose |
|------|------|---------|
| [axe DevTools](https://www.deque.com/axe/) | Browser extension | Automated WCAG testing |
| [WAVE](https://wave.webaim.org/) | Browser extension | Visual a11y feedback |
| Lighthouse | Browser DevTools | a11y score + audit |
| [NVDA](https://www.nvaccess.org/) | Screen reader (Windows) | Real screen reader testing |
| VoiceOver | Screen reader (macOS/iOS) | Built-in; Cmd+F5 to toggle |
| [Color Contrast Analyser](https://www.tpgi.com/color-contrast-checker/) | Desktop app | Precise contrast checking |
| [Who Can Use](https://www.whocanuse.com/) | Web tool | Simulate color vision deficiencies |

---

## Accessibility Checklist

- [ ] All images have descriptive `alt` text (decorative images `alt=""`)
- [ ] Color contrast meets WCAG AA (4.5:1 normal, 3:1 large text)
- [ ] Information is not conveyed by color alone
- [ ] All interactive elements are keyboard accessible
- [ ] Focus order is logical and follows visual order
- [ ] Focus indicator is always visible
- [ ] Page has a single `<h1>` and logical heading hierarchy
- [ ] Page has landmark regions (`<main>`, `<nav>`, `<header>`, `<footer>`)
- [ ] All form inputs have associated `<label>` elements
- [ ] Error messages are programmatically associated and announced
- [ ] Modals trap focus and return focus on close
- [ ] Dynamic content changes are announced via `aria-live`
- [ ] Icon-only buttons have accessible names (`aria-label` or `.sr-only`)
- [ ] Skip navigation link exists
- [ ] Animations respect `prefers-reduced-motion`
- [ ] Page is usable at 200% zoom

---

## Procedure

1. **Start with semantic HTML** — Use the right element for the job
2. **Run axe or Lighthouse** — Catch automated violations first
3. **Keyboard test** — Tab through the entire flow without a mouse
4. **Screen reader test** — Navigate with VoiceOver (macOS) or NVDA (Windows)
5. **Check contrast** — Run all text/UI colors through a contrast checker
6. **Fix violations by severity** — Critical (A) first, then AA, then AAA
7. **Document ARIA patterns** — Comment complex ARIA so future devs understand the intent
