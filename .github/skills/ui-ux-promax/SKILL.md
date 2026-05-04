---
name: ui-ux-promax
description: 'Advanced UX/UI design guidance covering user research, interaction design, information architecture, usability heuristics, micro-interactions, onboarding flows, and conversion optimization. Use when: designing user flows, improving usability, reducing friction, planning user research, auditing UX problems, designing forms/wizards/modals, optimizing CTAs, or creating delightful product experiences.'
argument-hint: 'Describe the UX problem, user flow, or screen you need help with'
---

# UI/UX Pro Max

Expert-level UX and UI design guidance for building products that users love.

## When to Use

- Designing or auditing user flows and journeys
- Improving usability and reducing cognitive load
- Building forms, wizards, multi-step flows, modals, drawers
- Optimizing conversion, engagement, or retention
- Planning user research (interviews, surveys, usability tests)
- Designing empty states, error states, onboarding, and loading states
- Micro-interactions and animation guidance
- Information architecture and navigation design

---

## UX Foundations

### Nielsen's 10 Usability Heuristics
1. **Visibility of system status** — Always tell users what is happening (loading spinners, progress bars, toast notifications)
2. **Match between system and real world** — Use language and concepts familiar to the user, not internal jargon
3. **User control and freedom** — Provide undo, back, cancel, and escape hatches
4. **Consistency and standards** — Follow platform conventions; don't reinvent patterns users already know
5. **Error prevention** — Design to prevent mistakes before they happen (confirmations, disabled states, validation)
6. **Recognition over recall** — Surface options; don't require users to memorize workflows
7. **Flexibility and efficiency** — Offer shortcuts for power users without overwhelming novices
8. **Aesthetic and minimalist design** — Remove anything that doesn't serve the user's immediate goal
9. **Help users recognize, diagnose, recover from errors** — Plain-language error messages with actionable next steps
10. **Help and documentation** — Provide contextual help inline; avoid requiring users to read manuals

---

## Interaction Design Patterns

### Forms & Input
- Label inputs above the field (not as floating labels or placeholders alone)
- Show validation **inline** on blur, not only on submit
- Group related fields with whitespace and section headers
- Mark **optional** fields, not required ones (assume most are required)
- Use the correct input type (`email`, `tel`, `number`, `date`) for mobile keyboards
- Place primary CTA at the natural end of the reading flow
- Limit forms to 5–7 fields; use progressive disclosure for longer forms

### Navigation
- Limit top-level nav to 5–7 items (Miller's Law)
- Highlight the current location (active states, breadcrumbs)
- Mobile: bottom tab bar for primary nav (thumb-reachable zone)
- Provide a global search for content-heavy products
- Use consistent back/cancel affordances throughout

### Modals & Overlays
- Use modals sparingly — only for critical interruptions or focused tasks
- Always provide a clear dismissal (× button + clicking backdrop + Escape key)
- Keep modal content focused; don't put entire pages inside modals
- Trap focus inside open modals for accessibility
- Avoid stacking modals

### Loading & Feedback States
| State | Pattern |
|-------|---------|
| < 100ms | No indicator needed |
| 100ms – 1s | Spinner or subtle animation |
| 1s – 10s | Progress bar with estimated time |
| > 10s | Progress bar + ability to cancel + notify on completion |
| Skeleton screens | Use for layout-preserving placeholders (preferred over spinners for page loads) |

### Empty States
Every list, table, or data view needs an empty state:
- Illustration or icon (contextual, not generic)
- Friendly headline ("No projects yet")
- Brief explanation of why it's empty
- Primary CTA to fix the emptiness ("Create your first project →")

### Error States
- **Validation errors**: Inline, specific, actionable ("Email must include @")
- **System errors**: Toast/banner with retry option; never expose stack traces
- **404/500**: Branded page with navigation back to safety
- **Network errors**: Offline banner; queue actions for when connection resumes

---

## User Research Quick Reference

| Method | When to Use | Time Investment |
|--------|-------------|-----------------|
| User interviews | Understand motivations, pain points | High |
| Usability testing | Validate designs, find friction | Medium |
| Surveys | Quantify opinions at scale | Low |
| Card sorting | Define information architecture | Medium |
| A/B testing | Optimize between known variants | High (needs traffic) |
| Heatmaps / session recordings | Diagnose engagement and drop-offs | Low |
| Jobs-to-be-Done framework | Understand what users are hiring the product to do | Medium |

---

## Cognitive Load Reduction

- **Chunk information**: Group related items into scannable sections of 3–5
- **Progressive disclosure**: Show only what's needed now; reveal more on demand
- **Defaults**: Pre-fill sensible defaults to reduce decision fatigue
- **Confirmation dialogs**: Reserve for destructive, irreversible actions only
- **Breadcrumbs**: Always show users where they are in deep hierarchies
- **Inline help**: Tooltips, helper text, and examples rather than a separate help center

---

## Micro-interactions & Delight

- Button press: subtle scale-down (0.97) + color shift on active
- Transitions: 150–300ms easing (`ease-out` for elements entering, `ease-in` for leaving)
- Success feedback: checkmark animation + positive color change
- Destructive actions: brief shake animation on cancel to confirm safety
- Drag and drop: ghost preview + snap guides + drop zone highlight

---

## UX Audit Checklist

- [ ] Every action has clear feedback (success / error / loading)
- [ ] No dead ends — every error state has a recovery path
- [ ] Forms are validated inline with helpful messages
- [ ] Navigation is consistent and location is always visible
- [ ] CTAs are clear, specific, and action-oriented ("Save changes" not "OK")
- [ ] Destructive actions require confirmation
- [ ] Content is scannable: headings, bullets, short paragraphs
- [ ] Mobile touch targets ≥ 44px × 44px
- [ ] Empty and loading states are designed for all data views
- [ ] Onboarding flow exists for new users

---

## Procedure

1. **Define the goal** — What is the user trying to accomplish? What does success look like?
2. **Map the current flow** — List every step a user takes (happy path + edge cases)
3. **Identify friction** — Apply heuristics and checklists above to spot problems
4. **Propose improvements** — Concrete, specific changes with rationale
5. **Design edge cases first** — Empty, loading, error, and success states
6. **Spec interactions** — Describe hover/focus/active states and transitions
7. **Verify accessibility** — Ensure keyboard navigation, focus management, and ARIA where needed
