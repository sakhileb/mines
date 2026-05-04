---
name: awesome-design
description: 'Expert UI/visual design guidance covering color theory, typography, spacing, layout, visual hierarchy, and design systems. Use when: designing interfaces, choosing color palettes, picking fonts, establishing spacing scales, creating design tokens, building consistent visual identities, or reviewing designs for quality and polish.'
argument-hint: 'Describe the design problem or component you need help with'
---

# Awesome Design

A comprehensive design skill that brings expert-level visual design principles to your projects.

## When to Use

- Designing new UI components or pages from scratch
- Reviewing existing designs for polish and consistency
- Choosing color palettes, typography, or spacing systems
- Creating or extending a design system / design tokens
- Getting feedback on visual hierarchy and layout
- Aligning design decisions with brand identity

## Core Design Principles

### Color Theory
- **60-30-10 Rule**: 60% dominant color, 30% secondary, 10% accent
- **Contrast ratios**: Text on background must meet WCAG AA (4.5:1 normal, 3:1 large text)
- **Semantic color**: Use color purposefully — success (green), warning (amber), error (red), info (blue)
- **Dark/light modes**: Design dual-mode palettes using HSL adjustments, not just CSS inversions
- Prefer a palette of 5–7 shades per hue (e.g., 50–900 Tailwind scale)

### Typography
- **Type scale**: Use a modular scale (1.25× or 1.333×) — e.g., 12/14/16/20/24/32/48/64px
- **Line height**: Body 1.5–1.7×, headings 1.1–1.3×
- **Line length**: 45–75 characters per line for readability
- **Font pairing**: Combine a serif/display face with a neutral sans-serif; limit to 2–3 fonts
- **Weight contrast**: Use weight (400 vs 700) to create hierarchy before reaching for size

### Spacing & Layout
- **8-point grid**: All spacing values multiples of 4 or 8px (4, 8, 12, 16, 24, 32, 48, 64, 96)
- **Whitespace**: Generous padding improves readability and perceived quality
- **Alignment**: Align elements to a consistent grid; avoid arbitrary pixel nudges
- **Proximity**: Group related items; separate unrelated ones with more space

### Visual Hierarchy
1. Size — larger = more important
2. Weight — bold draws attention
3. Color — high-contrast elements get noticed first
4. Position — top-left is read first (F-pattern / Z-pattern)
5. Whitespace — isolation increases importance

### Design Tokens & Systems
- Define tokens at three levels: **global** (raw values) → **semantic** (intent) → **component** (specific use)
- Example: `--color-blue-500` → `--color-action-primary` → `--button-bg`
- Document tokens in a central config (CSS custom properties, Tailwind theme, or JS tokens)

## Design Review Checklist

- [ ] Consistent spacing using the grid system
- [ ] Sufficient color contrast (run through a contrast checker)
- [ ] Clear visual hierarchy — one focal point per section
- [ ] Typography scale applied coherently
- [ ] Interactive states defined (hover, focus, active, disabled)
- [ ] Responsive behavior considered (mobile-first)
- [ ] Empty states, loading states, and error states designed
- [ ] Design tokens used instead of hardcoded values

## Tools & Resources

- **Color**: [Coolors](https://coolors.co), [Radix Colors](https://www.radix-ui.com/colors), [Tailwind palette](https://tailwindcss.com/docs/customizing-colors)
- **Typography**: [Google Fonts](https://fonts.google.com), [Fontpair](https://www.fontpair.co), [Typescale](https://typescale.com)
- **Contrast**: [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- **Icons**: [Lucide](https://lucide.dev), [Heroicons](https://heroicons.com), [Phosphor](https://phosphoricons.com)
- **Inspiration**: [Dribbble](https://dribbble.com), [Mobbin](https://mobbin.com), [Screenlane](https://screenlane.com)

## Procedure

1. **Understand the context** — What is the product? Who are the users? What is the goal of this screen/component?
2. **Audit existing patterns** — Check if a design system or tokens already exist in the codebase.
3. **Apply principles** — Use the checklists and guidelines above to inform design decisions.
4. **Produce concrete output** — Provide specific values (hex colors, px/rem sizes, CSS/Tailwind classes) rather than vague advice.
5. **Explain the reasoning** — Briefly justify each design choice so it can be maintained or adapted later.
