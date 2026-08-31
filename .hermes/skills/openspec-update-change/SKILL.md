---
name: openspec-update-change
description: This skill should be used when the user asks to "update the change", "revise the plan", "edit the proposal", "the design changed", "reconcile the artifacts", "fold in new decisions", "keep the artifacts coherent", or wants to revise planning artifacts without touching code — even if they say something informal like "update the docs" or "the spec is out of date".
allowed-tools: Bash(openspec:*)
license: MIT
compatibility: Requires openspec CLI.
version: "1.0"
---

Revise a change's existing planning artifacts and keep them coherent. Never
edit code.

**Store selection:** See `references/store-selection.md` before running any
openspec command. Apply the sticky `--store <id>` flag to every command that
accepts it for the rest of the workflow.

**Input**: Optionally specify a change name. If omitted, infer from conversation
context. If vague or ambiguous, prompt for available changes.

`/openspec-continue-change` is an optional workflow and may not be installed.
Before suggesting it, verify it is available. If unavailable, `openspec status
--change "<name>" --json` shows the next artifact and `openspec instructions
"<artifact-id>" --change "<name>" --json` explains how to create it.

**Steps**

1. **Select the change**

   If a name is provided, use it. Otherwise:
   - Infer from conversation context if the user mentioned a change
   - Auto-select if only one active change exists
   - If ambiguous, run `openspec list --json` to get available changes sorted
     by most recently modified, and ask the user to select one

   When prompting, present the top 3-4 most recently modified changes showing:
   - Change name
   - Schema (from `schema` field if present, otherwise "spec-driven")
   - Status (e.g., "0/5 tasks", "complete", "no tasks")
   - How recently it was modified (from `lastModified` field)

   Mark the most recently modified change as "(Recommended)".

   Always announce: "Using change: <name>" and how to override (e.g.,
   `/openspec-update-change <other>`).

2. **Get the change's artifacts**
   ```bash
   openspec status --change "<name>" --json
   ```
   Parse the JSON to understand current state:
   - `schemaName`: The workflow schema being used
   - `artifacts`: Array of artifacts with their status
   - `isPlanningComplete`: Whether all planning artifacts are complete
     (older CLI versions expose the same value as `isComplete`)
   - `planningHome`, `changeRoot`, `artifactPaths`, `actionContext`: path and
     scope context — use these instead of assuming repo-local paths

   The artifact ids and paths come from the active schema — do NOT assume them,
   and do NOT branch on hardcoded artifact names. Custom schemas must work unchanged.

   The files to edit are `artifactPaths.<id>.existingOutputPaths` — the concrete
   files that exist on disk, already glob-expanded. Do NOT write to
   `resolvedOutputPath`: for a glob artifact it is still the glob pattern, not
   a real file.

3. **Understand the request**
   - If the user asked for a specific revision ("the design now uses X"), that
     is the starting edit.
   - If they only said "update" / "make this coherent", treat it as a coherence
     review: read the existing artifacts and check them against each other for
     contradictions, gaps, and duplication.

4. **Read and reconcile**
   - Read the artifact(s) the request touches and the change's other existing
     artifacts.
   - Apply the requested edit. Then check every other existing artifact against
     it — in ANY direction: an edit to a later artifact may require revising an
     earlier one, not only the other way around. Build order is a useful reading
     order, not a constraint on which artifacts may be revised.
   - Note everything that is now inconsistent, missing, or contradictory.
   - Revise only files that already exist (`existingOutputPaths`). Do NOT create
     artifacts that don't exist yet, and do NOT invent new files under a glob
     artifact — note them and point the user to `/openspec-continue-change` to
     create them.
   - If the change is already coherent, say so and make no edits.

5. **Confirm and apply, one artifact at a time**
   - Show each proposed revision and why. Write only after the user confirms.
   - If the user rejects a revision, do not write it — leave that artifact unchanged.
   - When a substantial rewrite is needed, get that artifact's rules and template first:
     ```bash
     openspec instructions "<artifact-id>" --change "<name>" --json
     ```

6. **Point to the next step (guidance only — NEVER act on it)**
   - Artifacts still missing → suggest `/openspec-continue-change` to create them.
   - Change already implemented (tasks checked off / already applied) → the code
     may no longer match the revised plan; suggest `/openspec-apply-change` to
     carry the delta into code.
   - Everything done and implemented → suggest `/openspec-archive-change`.

**Output**

After each invocation, show:
- Which artifacts were revised (and which proposed revisions were rejected)
- Anything deferred to `/openspec-continue-change` (not-yet-created artifacts or files)
- Where the change stands and the recommended next command

**Guardrails**
- Planning artifacts only — NEVER edit implementation code. If the revised plan
  implies code changes, stop and point to `/openspec-apply-change`
- Use artifact ids and paths reported by `openspec status`; never branch on
  hardcoded artifact names
- Edit only the concrete files in `existingOutputPaths`; never write to a glob
  `resolvedOutputPath`
- Do not advance the build frontier: no new artifacts, no new files under glob
  artifacts — that is `/openspec-continue-change`'s job
- Confirm every edit with the user before writing
- If the request changes the change's *intent* rather than refining it, first
  verify whether the optional `/openspec-new-change` workflow is available. If
  it is, recommend starting fresh. If unavailable, ask for a distinct unused
  change name and recommend `openspec new change "<new-change-name>"`
