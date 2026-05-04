---
name: awesome-agent
description: 'Expert guidance for building, designing, and optimizing GitHub Copilot agents and AI agent workflows. Use when: creating custom agents (.agent.md), designing multi-agent pipelines, building skills and prompts, choosing the right agent primitive, orchestrating subagents, optimizing agent instructions for tool use, debugging why an agent is not behaving correctly, or architecting agentic systems for complex multi-step workflows.'
argument-hint: 'Describe the agent you want to build or the problem you need help solving using an agent'
---

# Awesome Agent

Expert-level guidance for designing, building, and optimizing GitHub Copilot agents and AI agent workflows.

## When to Use

- Creating custom agent modes (`.agent.md` files)
- Designing multi-agent or subagent orchestration patterns
- Building skills (`SKILL.md`), prompts (`.prompt.md`), and instructions (`.instructions.md`)
- Choosing between agent primitives (agent vs skill vs prompt vs instructions vs hooks)
- Optimizing agent instructions for specific tool usage and reliability
- Debugging agents that are ignoring instructions or behaving unexpectedly
- Architecting complex agentic systems for large codebases
- Designing agents with tool restrictions and safety guardrails

---

## Agent Primitives — Decision Guide

| Need | Use | Location |
|------|-----|----------|
| Always-on coding rules (style, conventions) | `copilot-instructions.md` | `.github/` |
| File-specific guidance (e.g., test files) | `*.instructions.md` | `.github/instructions/` |
| On-demand workflow with bundled assets | `SKILL.md` | `.github/skills/<name>/` |
| Parameterized single-task command | `*.prompt.md` | `.github/prompts/` |
| Specialized agent mode with tool restrictions | `*.agent.md` | `.github/agents/` |
| Shell commands at agent lifecycle events | `*.json` hooks | `.github/hooks/` |

**Quick heuristic:**
- "I want this applied always" → Instructions
- "I want to invoke this as a slash command for a specific task" → Prompt
- "I want a multi-step workflow I can trigger on demand with assets" → Skill
- "I need a separate agent persona with different tools/restrictions" → Agent
- "I need shell-level enforcement (block a tool, run a linter)" → Hooks

---

## Writing High-Quality Agent Instructions

### The Description Field is the Agent's Radar

The `description` in any frontmatter is how the model decides whether to load the file. **If the trigger phrases aren't in the description, the agent won't find it.**

```yaml
# Bad — too vague
description: 'A skill for testing.'

# Good — keyword rich, "Use when:" pattern
description: 'Run and debug automated tests using Playwright. Use when: writing e2e tests, debugging test failures, capturing screenshots, checking UI behavior in a headless browser, or verifying frontend functionality.'
```

### Structure for Reliability

Always include these sections in agent/skill instructions:

1. **When to Use** — Trigger conditions and use cases
2. **Procedure** — Numbered steps (agents follow numbered lists well)
3. **Examples** — Concrete inputs/outputs when behavior is nuanced
4. **Guardrails** — What NOT to do (negatives are powerful constraints)

### Grounding Tool Use

If the agent should use specific tools, name them explicitly:
```markdown
## Procedure
1. Use `grep_search` to find all usages of the function before modifying it.
2. Use `read_file` to read the target file before any edits.
3. Use `replace_string_in_file` for surgical edits; avoid full rewrites.
4. Run `get_errors` after each file edit to catch TypeScript errors immediately.
```

---

## Agent `.agent.md` Template

```markdown
---
name: code-reviewer
description: 'Expert code review agent. Use when: reviewing PRs, checking code quality, identifying security issues, suggesting refactors, or auditing code for best practices.'
tools:
  - read_file
  - grep_search
  - file_search
  - semantic_search
  - get_errors
  - run_in_terminal
---

# Code Reviewer

A specialized agent for thorough, constructive code reviews.

## Capabilities

- Security vulnerability scanning (OWASP Top 10)
- Performance and algorithmic analysis
- Style and convention consistency
- Architecture and design pattern review
- Test coverage gaps

## Procedure

1. **Understand scope** — Identify which files changed and why.
2. **Security first** — Check for injection, auth bypass, exposed secrets, insecure defaults.
3. **Logic review** — Trace data flows; check edge cases, error handling, and null safety.
4. **Style & conventions** — Verify alignment with codebase patterns.
5. **Tests** — Check for missing test coverage on new behavior.
6. **Report findings** — Organize by severity: Critical → Warning → Suggestion.

## Report Format

### 🔴 Critical
Issues that must be fixed before merge (security, data loss, crashes).

### 🟡 Warning
Issues that could cause bugs or tech debt.

### 🟢 Suggestion
Improvements to consider (readability, performance, patterns).
```

---

## Skill SKILL.md Template

```markdown
---
name: skill-name
description: 'What this skill does and when to use it. Include Use when: trigger phrases.'
argument-hint: 'Hint shown for slash invocation'
---

# Skill Name

## When to Use
- Specific trigger 1
- Specific trigger 2

## Procedure
1. Step one (reference [script](./scripts/run.sh) if needed)
2. Step two
3. Step three

## Examples
<!-- Include concrete examples for complex skills -->
```

---

## Subagent Orchestration Patterns

### Pattern 1: Research → Implement
Use a read-only subagent to gather context, then implement based on findings:
```
Main Agent:
  → Launch subagent: "Search for all usages of X and report the pattern"
  ← Subagent returns: list of files + pattern summary
  → Implement changes based on findings
```

### Pattern 2: Parallel Research
Gather independent context in parallel with semantic_search + grep_search before acting.

### Pattern 3: Stage Gate
```
Stage 1: Gather requirements → Stage 2: Plan → Stage 3: Implement → Stage 4: Verify
Each stage is a separate focused prompt/agent.
```

### When to Use Subagents vs Doing Work Directly
| Use Subagent When | Do Directly When |
|-------------------|-----------------|
| Searching large codebases | Simple file read |
| Multiple independent searches | 1–2 targeted searches |
| Exploratory research you'll summarize | Clear, specific lookup |
| You want to avoid cluttering main context | Quick answer needed |

---

## Prompt `.prompt.md` Template

```markdown
---
name: generate-component
description: 'Generate a new React component from a description. Use when creating new UI components.'
---

# Generate React Component

Create a new React component for: ${input:componentDescription}

## Requirements
- Use TypeScript
- Use Tailwind CSS for styling
- Follow project conventions from `.github/copilot-instructions.md`
- Include hover, focus, and disabled states
- Export as named export
```

---

## Instructions File Template

```markdown
---
applyTo: "src/components/**"
---

# Component Guidelines

- All components use TypeScript with explicit prop interfaces
- Use `cn()` from `@/lib/utils` for conditional class merging
- Prefer composition: build complex components from small primitives
- Export components as named exports (not default)
- Co-locate tests: `ComponentName.test.tsx` in same directory
```

---

## Hooks: Enforcing Agent Behavior

Hooks run shell commands at agent lifecycle points and can block operations:

```json
// .github/hooks/pre-write.json
{
  "event": "PreToolUse",
  "tools": ["replace_string_in_file", "create_file"],
  "command": "scripts/validate-before-write.sh $TOOL_INPUT_FILE_PATH"
}
```

Use hooks for:
- Blocking writes to protected files
- Running auto-formatters after file edits
- Requiring test runs before commits
- Injecting env context before commands

---

## Common Agent Anti-Patterns

| Anti-Pattern | Problem | Fix |
|-------------|---------|-----|
| Vague `description` | Agent never loads the file | Use "Use when:" with specific trigger keywords |
| Unescaped colons in YAML `description` | Silent YAML parse failure | Wrap in single quotes |
| `applyTo: "**"` on instructions | Burns context instantly | Use specific glob patterns |
| No procedure steps | Agent improvises — inconsistently | Add numbered steps |
| No guardrails | Agent does unexpected things | Add "Do NOT" constraints |
| Stacking modals / deeply nested agents | Context pollution | Use subagents that return single output |
| Giant monolithic SKILL.md | Too long to load efficiently | Split into modular sections with progressive loading |

---

## Agent Quality Checklist

- [ ] `name` matches folder name exactly
- [ ] `description` contains trigger keywords and "Use when:" pattern
- [ ] YAML frontmatter has no unescaped colons (wrap in quotes)
- [ ] File is in the correct location for its scope (workspace vs user)
- [ ] Procedure is numbered and concrete
- [ ] Tool usage is specified when the agent should use specific tools
- [ ] Guardrails ("Do NOT") are present for sensitive operations
- [ ] Examples are included for non-obvious inputs/outputs
- [ ] File size is appropriate (< 5000 tokens for instructions body)

---

## Procedure

1. **Clarify the agent's job** — What workflow does it automate? What triggers it?
2. **Choose the right primitive** — Use the decision guide above
3. **Write the description first** — It's the discovery surface; make it keyword-rich
4. **Define the procedure** — Step-by-step; name specific tools if relevant
5. **Add guardrails** — What should the agent never do?
6. **Test it** — Invoke with a real prompt; check if it behaves as expected
7. **Iterate on description** — If the agent isn't loading when expected, update description
