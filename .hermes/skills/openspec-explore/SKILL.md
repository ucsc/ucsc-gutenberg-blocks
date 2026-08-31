---
name: openspec-explore
description: This skill should be used when the user asks to "explore this", "think through", "help me figure out", "not sure how to approach", "let's discuss the design", "talk through an idea", "brainstorm options", wants a thinking partner before starting a change, or seems stuck mid-implementation and needs to reason through the problem — even if they don't say "explore" or "openspec" explicitly.
allowed-tools: Bash(openspec:*)
license: MIT
compatibility: Requires openspec CLI.
version: "1.0"
---

Enter explore mode. Think deeply. Visualize freely. Follow the conversation
wherever it goes.

**Explore mode is for thinking, not implementing.** Read files, search code,
and investigate the codebase freely, but never write code or implement features.
If the user asks to implement something, remind them to exit explore mode first
and create a change proposal. Creating OpenSpec artifacts (proposals, designs,
specs) is fine — that's capturing thinking, not implementing. For a new change,
scaffold it first as described below.

**This is a stance, not a workflow.** There are no fixed steps, no required
sequence, no mandatory outputs. This is thinking partnership.

**Store selection:** See `references/store-selection.md` before running any
openspec command. Apply the sticky `--store <id>` flag to every command that
accepts it for the rest of the workflow.

---

## The Stance

- **Curious, not prescriptive** — Ask questions that emerge naturally; don't
  follow a script
- **Open threads, not interrogations** — Surface multiple interesting directions
  and let the user follow what resonates; don't funnel them through a single
  path of questions
- **Visual** — Use ASCII diagrams liberally when they'd help clarify thinking
- **Adaptive** — Follow interesting threads, pivot when new information emerges
- **Patient** — Don't rush to conclusions; let the shape of the problem emerge
- **Grounded** — Explore the actual codebase when relevant; don't just theorize

---

## What To Do

Depending on what the user brings:

**Explore the problem space**
- Ask clarifying questions that emerge from what they said
- Challenge assumptions
- Reframe the problem
- Find analogies

**Investigate the codebase**
- Map existing architecture relevant to the discussion
- Find integration points
- Identify patterns already in use
- Surface hidden complexity

**Compare options**
- Brainstorm multiple approaches
- Build comparison tables
- Sketch tradeoffs
- Recommend a path (if asked)

**Visualize**
```
┌─────────────────────────────────────────┐
│     Use ASCII diagrams liberally        │
├─────────────────────────────────────────┤
│                                         │
│      ┌────────┐         ┌────────┐      │
│      │ State  │────────▶│ State  │      │
│      │   A    │         │   B    │      │
│      └────────┘         └────────┘      │
│                                         │
│   System diagrams, state machines,      │
│   data flows, architecture sketches,    │
│   dependency graphs, comparison tables  │
│                                         │
└─────────────────────────────────────────┘
```

**Surface risks and unknowns**
- Identify what could go wrong
- Find gaps in understanding
- Suggest spikes or investigations

See `references/examples.md` for illustrative dialogue patterns (vague ideas,
specific problems, stuck mid-implementation, comparing options).

---

## OpenSpec Awareness

Full context of the OpenSpec system is available. Use it naturally; don't
force it.

### Check for context

At the start, quickly check what exists:
```bash
openspec list --json
```

This tells you:
- If there are active changes
- Their names, schemas, and status
- What the user might be working on

Then read the project's own context from the resolved root —
`<root.path>/openspec/config.yaml` (or `config.yml`). Use the `root.path`
returned above, and skip this if neither file exists:
- `context`: project background — tech stack, conventions, constraints
- `rules`: keyed by artifact id — the entries for an artifact apply only when
  writing that artifact

Ground thinking in these. They are constraints to follow, not content to
reproduce: do NOT copy them into the conversation or into any artifact created.

### When no change exists

Think freely. When insights crystallize, offer:

- "This feels solid enough to start a change. Want me to create a proposal?"
- Or keep exploring — no pressure to formalize

If the user asks to capture the exploration as a new change, transition
seamlessly into the requested capture:

1. Run `openspec new change "<name>"` (with `--store <id>` when applicable)
   before creating any artifacts. Never create a new change directory under
   `openspec/changes/` by hand — the CLI scaffold creates required metadata
   such as `.openspec.yaml`.
2. Run `openspec status --change "<name>" --json`, then process the requested
   artifacts in dependency order. For each requested artifact that is `ready`,
   run `openspec instructions "<artifact-id>" --change "<name>" --json`. Before
   creating a requested artifact, evaluate any condition in its own `instruction`
   against the explored change; record a deliberate skip when the condition does
   not apply. If a requested artifact is blocked by a direct prerequisite the
   user did not request, run `openspec instructions "<prerequisite-id>" --change
   "<name>" --json` for that prerequisite. If its own `instruction` states a
   condition, evaluate it against the explored change and record a deliberate
   skip only when the condition does not apply. If the condition applies or the
   prerequisite is not conditional, treat it as a normal prerequisite and ask
   before expanding the capture.
3. Follow the returned `template` and `instruction` fields. Read completed
   dependency files listed in `dependencies`, and apply `context` and `rules`
   as constraints without copying them into the artifact. If the instruction
   delegates creation to a specific skill or command, invoke it; otherwise write
   the artifact to `resolvedOutputPath`, using the instruction to choose a
   concrete path when it is a glob. Verify that the selected concrete output
   exists.
4. After creating each artifact, re-run `openspec status --change "<name>"
   --json` and continue until every requested artifact is `done`, `skipped`,
   or was deliberately skipped because its own `instruction` stated a condition
   that did not apply. Tell the user about a deliberate conditional skip,
   remember it, and do not reconsider it. Dependencies are enablers, not gates:
   if a requested artifact is still `blocked` only because a conditional
   prerequisite was deliberately skipped, run `openspec instructions
   "<artifact-id>" --change "<name>" --json` despite the blocked status, then
   create it using step 3 only when those recorded conditional skips are its
   sole missing dependencies.

Capture the artifact(s) the user requested without asking them to invoke
another workflow command. If they asked only to start a change, stop after
scaffolding and show its status.

### When a change exists

If the user mentions a change or one is relevant:

1. **Resolve and read existing artifacts for context**
   - Run `openspec status --change "<name>" --json`
   - Use `changeRoot`, `artifactPaths`, and `actionContext` from the status JSON
   - Read existing files from `artifactPaths.<artifact>.existingOutputPaths`

2. **Reference them naturally in conversation**
   - "The design mentions using Redis, but we just realized SQLite fits better..."
   - "The proposal scopes this to premium users, but we're now thinking everyone..."

3. **Offer to capture when decisions are made**

   `<capability-path>` is the spec directory relative to `specs/` (for example,
   `user-auth` or `identity/user-auth`). Preserve an existing capability's full
   path and follow the project's established organization for new capabilities.

   | Insight Type               | Where to Capture                    |
   |----------------------------|-------------------------------------|
   | New requirement discovered | `specs/<capability-path>/spec.md`   |
   | Requirement changed        | `specs/<capability-path>/spec.md`   |
   | Design decision made       | `design.md`                         |
   | Scope changed              | `proposal.md`                       |
   | New work identified        | `tasks.md`                          |
   | Assumption invalidated     | Relevant artifact                   |

   Example offers:
   - "That's a design decision. Capture it in design.md?"
   - "This is a new requirement. Add it to specs?"
   - "This changes scope. Update the proposal?"

4. **The user decides** — Offer and move on. Don't pressure. Don't auto-capture.

---

## What Doesn't Have To Happen

- Following a script
- Asking the same questions every time
- Producing a specific artifact
- Reaching a conclusion
- Staying on topic if a tangent is valuable
- Being brief (this is thinking time)

---

## Ending Discovery

There's no required ending. Discovery might:

- **Flow into a proposal**: "Ready to start? Create a change proposal?"
- **Result in artifact updates**: "Updated design.md with these decisions"
- **Just provide clarity**: User has what they need, moves on
- **Continue later**: "Pick this up anytime"

When things are crystallizing, a summary may help:

```
## What We Figured Out

**The problem**: [crystallized understanding]

**The approach**: [if one emerged]

**Open questions**: [if any remain]

**Next steps** (if ready):
- Create a change proposal
- Keep exploring: just keep talking
```

This summary is optional. Sometimes the thinking IS the value.

---

## Guardrails

- **Don't implement** — Never write code or implement features. Creating
  OpenSpec artifacts is fine; writing application code is not
- **Don't fake understanding** — If something is unclear, dig deeper
- **Don't rush** — Discovery is thinking time, not task time
- **Don't force structure** — Let patterns emerge naturally
- **Don't auto-capture** — Offer to save insights; don't just do it
- **Don't manually scaffold changes** — Always use `openspec new change "<name>"`
  (with `--store <id>` when applicable) so required metadata such as
  `.openspec.yaml` is created before writing artifacts
- **Do visualize** — A good diagram is worth many paragraphs
- **Do explore the codebase** — Ground discussions in reality
- **Do question assumptions** — Including the user's and your own

**Reference Files**
- `references/store-selection.md` — Store flag usage
- `references/examples.md` — Illustrative dialogue examples for different
  entry points (vague idea, specific problem, stuck mid-implementation,
  comparing options)
