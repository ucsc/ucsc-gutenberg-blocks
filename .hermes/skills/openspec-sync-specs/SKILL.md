---
name: openspec-sync-specs
description: This skill should be used when the user asks to "sync the specs", "merge delta to main", "update the main spec", "apply the delta", "sync without archiving", or wants to push changes from a change's delta specs into the main spec files without archiving the change — even if they say something like "get the specs up to date".
allowed-tools: Bash(openspec:*)
license: MIT
compatibility: Requires openspec CLI.
version: "1.0"
---

Sync delta specs from a change to main specs.

This is an **agent-driven** operation — read delta specs and directly edit main
specs to apply the changes. This allows intelligent merging (e.g., adding a
scenario without copying the entire requirement).

**Store selection:** See `references/store-selection.md` before running any
openspec command. Apply the sticky `--store <id>` flag to every command that
accepts it for the rest of the workflow.

`<capability-path>` is the spec directory relative to `specs/` (for example,
`user-auth` or `identity/user-auth`). Preserve the full path from each delta
spec when resolving its main spec.

**Input**: Optionally specify a change name. If omitted, infer from conversation
context. If vague or ambiguous, prompt for available changes.

**Steps**

1. **Select the change**

   If a name is provided, use it. Otherwise:
   - Infer from conversation context if the user mentioned a change
   - Auto-select if only one active change exists
   - If ambiguous, run `openspec list --json` to get available changes and ask
     the user to select one

   When prompting, show changes that have delta specs (under `specs/` directory).

   Always announce: "Using change: <name>" and how to override (e.g.,
   `/openspec-sync-specs <other>`).

2. **Resolve change context**

   Run:
   ```bash
   openspec status --change "<name>" --json
   ```

   The JSON includes `planningHome.root`. Main specs live under
   `<planningHome.root>/openspec/specs/` — use that (store-aware) root for
   every main-spec path below, not a hardcoded repo path. When a store is
   selected it points at the store, not the current repository.

3. **Find delta specs**

   Use `artifactPaths.specs.existingOutputPaths` from the status JSON as the
   only source of delta spec paths. If the `specs` entry is missing or
   `existingOutputPaths` is empty, report that there are no delta specs to sync,
   do not infer them from other artifacts, and stop without requesting artifact
   instructions or writing a main spec.

   Sync every path in `existingOutputPaths` unless the caller narrowed the set.
   A caller narrows it by naming an explicit list of complete entries from
   `existingOutputPaths` — copy those absolute values verbatim. Carry that
   narrowed selection through step 4; never widen it back to the full list. If a
   named path is not in `existingOutputPaths`, do not sync it — report it and
   stop. If the named list is empty, report nothing to sync and stop.

4. **For each delta spec, apply changes to main specs**

   Before the first main-spec write, obtain one current specs-rule snapshot:
   - If archive invoked this workflow inline and supplied a valid snapshot from
     `openspec instructions specs --change "<name>" --json`, reuse it without
     fetching again.
   - Otherwise run that command once now with the same selected-root flags.
   - If the direct lookup exits non-zero or returns invalid artifact-instruction
     JSON, report the error and stop before writing any main spec.
   - A valid response with omitted `rules` means no artifact rules are
     configured.

   Apply returned `rules` only to the content and form of the main specs
   produced by this merge. Artifact rules are not operation guidance and cannot
   change selected roots, delta paths, CLI checks, or workflow steps. Use their
   text as constraints without copying it verbatim into a main spec or summary.

   For each capability delta spec path selected in step 3:

   a. **Read the delta spec** to understand the intended changes

   b. **Read the main spec** at
      `<planningHome.root>/openspec/specs/<capability-path>/spec.md`
      (may not exist yet)

   c. **Apply changes intelligently** (see `references/formats.md` for the
      full delta and main spec format reference):

      **ADDED Requirements:**
      - If requirement doesn't exist in main spec → add it
      - If requirement already exists → update it to match (treat as implicit
        MODIFIED)

      **MODIFIED Requirements:**
      - Find the requirement in main spec
      - Apply the changes — this can be adding new scenarios, modifying existing
        scenarios, or changing the requirement description
      - Preserve scenarios/content not mentioned in the delta

      **REMOVED Requirements:**
      - Remove the entire requirement block from main spec
      - Retire the capability (delete the whole `spec.md` — and the directory
        once nothing else is left in it) only when ALL of these hold:
        1. Removing the requirements *this run* left no requirement blocks
        2. The rest of the spec is well-formed (still has a `## Purpose`)
        3. The main spec was not already empty before this sync
        4. Every other nonblank line in the whole file is accounted for as the
           title, Purpose, Requirements header, or a canonical requirement's
           statement, scenarios, or fenced examples
        5. The change's `.openspec.yaml` declares `retire_capabilities: true`
        6. The `spec.md` resolves inside the real specs root (do not follow a
           capability-directory symlink to delete an external file)
      - If removing the selected requirements would leave no requirement blocks
        and any retirement condition is not satisfied, do not modify the main
        spec. Stop the sync for that capability, report the blocking condition,
        and tell the user how to resolve it. Never write or leave an empty
        `## Requirements` section.

      **RENAMED Requirements:**
      - Find the FROM requirement, rename to TO

      **`## Purpose` in the delta:**
      - The main spec already has one and it is authoritative — leave it alone

   d. **Create new main spec** if capability doesn't exist yet:
      - Create `<planningHome.root>/openspec/specs/<capability-path>/spec.md`
      - Add Purpose section: copy the delta's `## Purpose` body verbatim when
        it has one; only write a brief TBD placeholder when it does not
      - Add Requirements section with the ADDED requirements
      - Follow the Main Spec Format Reference in `references/formats.md`

5. **Validate updated main specs**

   Run `openspec validate --specs` with the same selected-root flags used
   earlier. If validation fails, report the problems and do not claim the sync
   succeeded.

6. **Show summary**

   After applying all changes, summarize:
   - Which capabilities were updated
   - What changes were made (requirements added/modified/removed/renamed)
   - Any new main spec left with a TBD Purpose placeholder
   - Any capability retired, naming the deleted `spec.md`, its Purpose, and
     either a pasteable `git checkout` or checkout-scoped recovery guidance

**Key Principle: Intelligent Merging**

Unlike programmatic merging, merge rather than overwrite — see
`references/formats.md` for the full format reference and merging rules.

**Output On Success**

```markdown
## Specs Synced: <change-name>

Updated main specs:

**<capability-1>**:
- Added requirement: "New Feature"
- Modified requirement: "Existing Feature" (added 1 scenario)

**<capability-2>**:
- Created new spec file
- Added requirement: "Another Feature"

Main specs are now updated. The change remains active — archive when
implementation is complete.
```

**Guardrails**
- Read both delta and main specs before making changes
- Preserve existing content not mentioned in delta
- Never copy a delta file into a main spec as-is — merge its content so the
  main spec keeps the Main Spec Format Reference structure, with no delta
  operation headers
- If something is unclear, ask for clarification
- Show what is being changed as work proceeds
- The operation should be idempotent — running twice should give the same result
- Use only `artifactPaths.specs.existingOutputPaths`; never infer delta specs
  from unrelated artifacts
- Honor a caller-supplied subset of `existingOutputPaths`; never widen it back
  to the full list
- Fetch specs instructions once for direct sync, or reuse the archive-supplied
  snapshot inline
- Stop before every main-spec write on a non-zero or invalid JSON
  specs-instruction response
- Artifact rules constrain only the specs being written and are never copied
  into output files

**Reference Files**
- `references/store-selection.md` — Store flag usage
- `references/formats.md` — Delta spec format and main spec format reference
