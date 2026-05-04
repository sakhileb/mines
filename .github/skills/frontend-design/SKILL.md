---
name: frontend-design
description: 'Frontend implementation of UI designs using HTML, CSS, Tailwind CSS, and modern JavaScript frameworks. Use when: translating Figma/designs to code, building responsive layouts, implementing CSS animations, setting up Tailwind config, styling React/Vue/Svelte components, implementing dark mode, building component libraries, or reviewing frontend code for design fidelity.'
argument-hint: 'Describe the component or layout you want to build, or paste a design description'
---

# Frontend Design

Bridging the gap between design and implementation — turning great designs into pixel-perfect, performant code.

## When to Use

- Translating visual designs (Figma, mockups, descriptions) into HTML/CSS/JSX
- Building responsive layouts (mobile-first, adaptive breakpoints)
- Implementing Tailwind CSS utility classes and config
- Creating CSS animations, transitions, and micro-interactions
- Setting up design tokens in Tailwind or CSS custom properties
- Implementing dark mode / theme switching
- Building reusable, styled component libraries
- Reviewing frontend code for design fidelity and quality

---

## Technology Stack Defaults

This skill defaults to these technologies (adjust as needed):
- **CSS Framework**: Tailwind CSS v3+
- **Component Framework**: React (JSX/TSX) — adapt examples to Vue/Svelte/Blade as needed
- **Icons**: Lucide React
- **Fonts**: Via `@fontsource` or Google Fonts `<link>`

---

## Responsive Design

### Breakpoint Strategy (Mobile-First)
```css
/* Tailwind defaults */
sm:  640px   /* Small tablet */
md:  768px   /* Tablet */
lg:  1024px  /* Desktop */
xl:  1280px  /* Large desktop */
2xl: 1536px  /* Wide screen */
```

**Rules:**
- Write base styles for mobile, override at larger breakpoints
- Use `container` + `mx-auto` + `px-4 sm:px-6 lg:px-8` for page gutters
- Test layouts at 375px (iPhone SE), 768px, 1280px, and 1440px
- Avoid fixed pixel widths; prefer `max-w-*`, `w-full`, `flex`, or `grid`

### Layout Patterns
```jsx
// Centered content with max width
<div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

// Responsive grid: 1 → 2 → 3 columns
<div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

// Sidebar layout
<div className="flex flex-col lg:flex-row gap-8">
  <aside className="w-full lg:w-64 shrink-0">...</aside>
  <main className="flex-1 min-w-0">...</main>
</div>

// Stack → row on larger screens
<div className="flex flex-col sm:flex-row items-start sm:items-center gap-4">
```

---

## Tailwind CSS Best Practices

### Class Organization Order
Follow this mental model for ordering classes:
1. Layout (`flex`, `grid`, `block`, `hidden`)
2. Positioning (`relative`, `absolute`, `z-10`)
3. Box model (`w-`, `h-`, `p-`, `m-`, `border`, `rounded`)
4. Typography (`text-`, `font-`, `leading-`, `tracking-`)
5. Visual (`bg-`, `shadow-`, `ring-`, `opacity-`)
6. Interactive (`hover:`, `focus:`, `active:`)
7. Responsive (`sm:`, `md:`, `lg:`)

### Common Patterns
```jsx
// Card
<div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">

// Button variants
// Primary
<button className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 active:scale-[0.98] transition-all">

// Ghost
<button className="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 dark:text-gray-300 dark:hover:bg-gray-800">

// Badge
<span className="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">

// Input
<input className="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
```

### Dark Mode
```js
// tailwind.config.js
module.exports = {
  darkMode: 'class', // Toggle via class on <html>
  // ...
}
```
```jsx
// Pattern: always pair light and dark values
<div className="bg-white text-gray-900 dark:bg-gray-900 dark:text-white">
<p className="text-gray-600 dark:text-gray-400">
<div className="border-gray-200 dark:border-gray-700">
```

---

## CSS Animations & Transitions

### Transition Defaults
```jsx
// All properties
className="transition-all duration-200 ease-out"

// Specific (preferred — better performance)
className="transition-colors duration-200"
className="transition-transform duration-150 ease-out"
className="transition-opacity duration-300"
```

### Keyframe Animations (Tailwind)
```js
// tailwind.config.js
theme: {
  extend: {
    keyframes: {
      'fade-in': { from: { opacity: '0', transform: 'translateY(4px)' }, to: { opacity: '1', transform: 'translateY(0)' } },
      'fade-out': { from: { opacity: '1' }, to: { opacity: '0' } },
      'slide-in': { from: { transform: 'translateX(-100%)' }, to: { transform: 'translateX(0)' } },
      'scale-in': { from: { opacity: '0', transform: 'scale(0.95)' }, to: { opacity: '1', transform: 'scale(1)' } },
    },
    animation: {
      'fade-in': 'fade-in 0.2s ease-out',
      'scale-in': 'scale-in 0.15s ease-out',
    },
  }
}
```

---

## Component Structure Conventions

```jsx
// File naming: PascalCase for components
// ComponentName.tsx

interface Props {
  variant?: 'primary' | 'secondary' | 'ghost'
  size?: 'sm' | 'md' | 'lg'
  className?: string
  children: React.ReactNode
}

export function Button({ variant = 'primary', size = 'md', className, children, ...props }: Props) {
  return (
    <button
      className={cn(
        // base styles
        'inline-flex items-center justify-center font-medium rounded-lg transition-all',
        // size variants
        size === 'sm' && 'px-3 py-1.5 text-xs',
        size === 'md' && 'px-4 py-2 text-sm',
        size === 'lg' && 'px-6 py-3 text-base',
        // color variants
        variant === 'primary' && 'bg-blue-600 text-white hover:bg-blue-700',
        variant === 'secondary' && 'bg-gray-100 text-gray-900 hover:bg-gray-200',
        variant === 'ghost' && 'hover:bg-gray-100 text-gray-700',
        className
      )}
      {...props}
    >
      {children}
    </button>
  )
}
```

---

## Design Fidelity Checklist

- [ ] Spacing matches the 4/8px grid
- [ ] Font sizes, weights, and line heights match the design scale
- [ ] Colors use design tokens / Tailwind theme (no arbitrary values unless necessary)
- [ ] Interactive states (hover, focus, active, disabled) are implemented
- [ ] Focus styles are visible and not removed (`outline-none` only paired with `focus-visible:ring-*`)
- [ ] Component is responsive at all breakpoints
- [ ] Dark mode variant implemented where applicable
- [ ] Animations use `prefers-reduced-motion` media query

---

## Procedure

1. **Read the design** — Understand layout, spacing, color, typography, and states needed
2. **Check existing components** — Look for reusable primitives in the codebase before building new ones
3. **Build mobile-first** — Start with the smallest breakpoint, add responsive overrides
4. **Implement all states** — Default, hover, focus, active, disabled, loading, error
5. **Test visually** — Check at 375px, 768px, 1280px; verify dark mode; check with browser zoom
6. **Review accessibility** — Keyboard navigable, focus visible, ARIA where needed (use web-accessibility skill)
