---
name: shadcn-ui
description: 'Expert guidance for building UIs with shadcn/ui components. Use when: adding shadcn/ui components to a project, customizing shadcn themes and CSS variables, composing complex UI from shadcn primitives, using the CLI, integrating with React Hook Form and Zod, building data tables with TanStack Table, or troubleshooting shadcn component styling.'
argument-hint: 'Describe the component or UI pattern you want to build with shadcn/ui'
---

# shadcn/ui

Expert knowledge for building production UIs with shadcn/ui — the collection of beautifully designed, accessible, copy-paste components built on Radix UI and Tailwind CSS.

## When to Use

- Adding or customizing shadcn/ui components
- Setting up a new shadcn/ui project from scratch
- Theming with CSS variables (light/dark mode)
- Composing complex UIs from primitive components
- Building forms with React Hook Form + Zod + shadcn Form
- Building data tables with TanStack Table + shadcn DataTable
- Troubleshooting styling, overrides, or component composition issues

---

## Setup & Installation

### New Project
```bash
npx shadcn@latest init
```
Follow prompts: choose style (Default/New York), base color, CSS variables.

### Add Components
```bash
npx shadcn@latest add button
npx shadcn@latest add card dialog form input table
# Multiple at once:
npx shadcn@latest add button card dialog form input label select textarea toast
```

Components are copied into `components/ui/` — they are **yours to modify**.

### Required `components.json`
```json
{
  "$schema": "https://ui.shadcn.com/schema.json",
  "style": "default",
  "rsc": true,
  "tsx": true,
  "tailwind": {
    "config": "tailwind.config.ts",
    "css": "app/globals.css",
    "baseColor": "slate",
    "cssVariables": true
  },
  "aliases": {
    "components": "@/components",
    "utils": "@/lib/utils"
  }
}
```

---

## Theming with CSS Variables

shadcn/ui uses CSS custom properties for all colors. Override in your `globals.css`:

```css
@layer base {
  :root {
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;
    --popover: 0 0% 100%;
    --popover-foreground: 222.2 84% 4.9%;
    --primary: 221.2 83.2% 53.3%;        /* Main brand color */
    --primary-foreground: 210 40% 98%;
    --secondary: 210 40% 96%;
    --secondary-foreground: 222.2 47.4% 11.2%;
    --muted: 210 40% 96%;
    --muted-foreground: 215.4 16.3% 46.9%;
    --accent: 210 40% 96%;
    --accent-foreground: 222.2 47.4% 11.2%;
    --destructive: 0 84.2% 60.2%;
    --destructive-foreground: 210 40% 98%;
    --border: 214.3 31.8% 91.4%;
    --input: 214.3 31.8% 91.4%;
    --ring: 221.2 83.2% 53.3%;
    --radius: 0.5rem;
  }

  .dark {
    --background: 222.2 84% 4.9%;
    --foreground: 210 40% 98%;
    /* ... dark mode overrides */
  }
}
```

**Color format**: `H S% L%` (hue saturation lightness) — **no `hsl()` wrapper**, applied as `hsl(var(--primary))` by Tailwind.

---

## Core Components Reference

### Button
```jsx
import { Button } from "@/components/ui/button"

<Button>Default</Button>
<Button variant="destructive">Delete</Button>
<Button variant="outline">Outline</Button>
<Button variant="secondary">Secondary</Button>
<Button variant="ghost">Ghost</Button>
<Button variant="link">Link</Button>
<Button size="sm">Small</Button>
<Button size="lg">Large</Button>
<Button size="icon"><Icon /></Button>
<Button disabled>Disabled</Button>
<Button>
  <Loader2 className="mr-2 h-4 w-4 animate-spin" /> Loading
</Button>
```

### Card
```jsx
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"

<Card>
  <CardHeader>
    <CardTitle>Card Title</CardTitle>
    <CardDescription>Card description text.</CardDescription>
  </CardHeader>
  <CardContent>
    <p>Content goes here.</p>
  </CardContent>
  <CardFooter className="flex justify-between">
    <Button variant="outline">Cancel</Button>
    <Button>Save</Button>
  </CardFooter>
</Card>
```

### Dialog (Modal)
```jsx
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"

<Dialog>
  <DialogTrigger asChild>
    <Button>Open</Button>
  </DialogTrigger>
  <DialogContent className="sm:max-w-[425px]">
    <DialogHeader>
      <DialogTitle>Edit profile</DialogTitle>
      <DialogDescription>Make changes to your profile here.</DialogDescription>
    </DialogHeader>
    {/* form content */}
    <DialogFooter>
      <Button type="submit">Save changes</Button>
    </DialogFooter>
  </DialogContent>
</Dialog>
```

### Form (React Hook Form + Zod)
```jsx
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form"
import { Input } from "@/components/ui/input"

const formSchema = z.object({
  username: z.string().min(2, { message: "Username must be at least 2 characters." }),
})

export function ProfileForm() {
  const form = useForm<z.infer<typeof formSchema>>({
    resolver: zodResolver(formSchema),
    defaultValues: { username: "" },
  })

  return (
    <Form {...form}>
      <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
        <FormField
          control={form.control}
          name="username"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Username</FormLabel>
              <FormControl>
                <Input placeholder="shadcn" {...field} />
              </FormControl>
              <FormDescription>Your public display name.</FormDescription>
              <FormMessage />
            </FormItem>
          )}
        />
        <Button type="submit">Submit</Button>
      </form>
    </Form>
  )
}
```

### Toast Notifications
```jsx
// In root layout:
import { Toaster } from "@/components/ui/toaster"
<Toaster />

// Usage:
import { useToast } from "@/components/ui/use-toast"
const { toast } = useToast()
toast({ title: "Saved!", description: "Your changes have been saved." })
toast({ variant: "destructive", title: "Error", description: "Something went wrong." })
```

---

## Useful Patterns

### `cn()` utility — merging Tailwind classes
```ts
// lib/utils.ts (auto-generated)
import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

// Usage: safely merge and override classes
<Button className={cn("w-full", isLoading && "opacity-50 cursor-not-allowed")} />
```

### `asChild` prop — render your own element
```jsx
// Instead of <Button> rendering a <button>, render a <Link>:
<Button asChild>
  <Link href="/dashboard">Go to Dashboard</Link>
</Button>
```

### Extending component variants
```tsx
// Add a new variant without modifying the original
import { buttonVariants } from "@/components/ui/button"
<Link className={buttonVariants({ variant: "outline" })} href="/about">About</Link>
```

---

## Common Gotchas

| Problem | Solution |
|---------|----------|
| Classes not applying | Use `cn()` to merge; never concatenate strings |
| Dark mode not working | Ensure `darkMode: 'class'` in `tailwind.config` and `class="dark"` on `<html>` |
| Popover/dropdown cut off | Check parent `overflow: hidden`; use `z-50` on overlay |
| Form field not registering | Pass `{...field}` to the input; don't spread props manually |
| Dialog autofocus wrong element | Use `DialogContent`'s `onOpenAutoFocus` to set custom focus |
| Icons misaligned | Add `className="h-4 w-4"` to all Lucide icons inside buttons |

---

## Procedure

1. **Check if the component exists** — Run `npx shadcn@latest add <name>` or browse [ui.shadcn.com/components](https://ui.shadcn.com/components)
2. **Add to project** — Use the CLI; never copy manually to avoid missing dependencies
3. **Compose, don't customize primitives** — Wrap components in your own; only edit `ui/` files for style changes
4. **Use CSS variables for theming** — Change `--primary` etc. rather than overriding individual component classes
5. **Test all states** — Loading, error, disabled, empty per component
6. **Verify accessibility** — shadcn/ui is accessible by default; don't break it by removing focus rings or ARIA attributes
