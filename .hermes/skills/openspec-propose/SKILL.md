---
name: openspec-propose
description: This skill should be used when the user asks to "propose a change", "create a spec", "plan a feature", "write a proposal", "scaffold a change", "draft a design", or wants to describe something they want to build and get a complete planning artifact set ready for implementation — even if they don't say "openspec" explicitly.
allowed-tools: Bash(openspec:*)
license: MIT
compatibility: Requires openspec CLI.
version: "1.0"
---

Propose a new change — create the change and generate all artifacts in one step.

**Planning boundary**: This workflow creates planning artifacts only. The user
request that selected or triggered this workflow authorizes planning only, even
if it asks to build or fix something. Do not edit project code. After the
planning artifacts are complete, stop. Do not start implementation in the same
response, even if the initial request asks for it. Wait for a new user request
after the artifacts are presented; then start the apply workflow.

The default spec-driven schema produces:
- proposal.md (what & why)
- `specs/<capability-path>/spec.md` (what the system must do — a delta, not the main spec)
- design.md (how)
- tasks.md (implementation steps)

`<capability-path>` is the spec directory relative to `specs/` (for example,
`user-auth` or `identity/user-auth`). Preserve an existing capability's full
path and follow the project's established organization for new capabilities.

When the user is ready to implement, they must start the apply workflow
explicitly.

---

**Store selection:** See `references/store-selection.md` before running any
openspec command. Apply the sticky `--store <id>` flag to every command that
accepts it for the rest of the workflow.

**Input**: The user's request should include a change name (kebab-case) OR a
description of what they want to build.

**Steps**

1. **Understand the request and clarify material ambiguity**

   If no clear input is provided, ask the user (open-ended, no preset options):
   > "What change do you want to work on? Describe what you want to build or fix."

   From their description, derive a kebab-case name (e.g., "add user authentication" → `add-user-auth`).

   **Do NOT proceed without understanding what the user wants to build.**

   If the request contains ambiguity that would materially affect scope,
   externally observable behavior, compatibility, or acceptance criteria, ask
   before creating the change. For minor details, make a reasonable assumption
   and record it in the planning artifacts.

2. **Determine the workflow schema**

   Use the configured default schema unless the user explicitly requests a
   different workflow.

   **Use a different schema only if the user:**
   - Explicitly requests a specific schema by name → use `--schema <schema-name>`
   - Asks to "show workflows" or asks "what workflows" exist → resolve the
     authoritative root by running `openspec context --json` from the current
     working directory (append `--store "<store-id>"` if a store was selected),
     then run `openspec schemas --json` with its working directory set to the
     returned `root.path` and let them choose. If context reports only
     `no_openspec_root`, run `openspec schemas --json` from the current working
     directory instead.

   Otherwise, omit `--schema` to preserve the configured default.

3. **Create the change directory**

   Using the configured default:
   ```bash
   openspec new change "<name>"
   ```

   Using an explicitly requested schema:
   ```bash
   openspec new change "<name>" --schema "<schema-name>"
   ```

4. **Get the artifact build order**
   ```bash
   openspec status --change "<name>" --json
   ```
   Parse the JSON to get:
   - `applyRequires`: array of artifact IDs needed before implementation
   - `artifacts`: list of all artifacts, each with its `status` and `requires` edges
   - `planningHome`, `changeRoot`, `artifactPaths`, `actionContext`: path and
     scope context — use these instead of assuming repo-local paths

5. **Create every artifact in the required set**

   Use a todo list to track progress through the artifacts.

   Loop through artifacts in dependency order (artifacts with no pending
   dependencies first):

   a. **For each artifact that is `ready` (dependencies satisfied)**:
      - Get instructions:
        ```bash
        openspec instructions <artifact-id> --change "<name>" --json
        ```
      - The instructions JSON includes:
        - `context`: Project background (constraints for you — do NOT include in output)
        - `rules`: Artifact-specific rules (constraints for you — do NOT include in output)
        - `template`: The structure to use for your output file
        - `instruction`: Schema-specific guidance for this artifact type
        - `skipped`/`warning`: present when the change declares skip_specs and
          this artifact must NOT be created — stop and pick another artifact
        - `resolvedOutputPath`: Resolved path or pattern to write the artifact
        - `dependencies`: Completed artifacts to read for context
      - Read any completed dependency files for context — always re-read from
        disk, even if seen earlier in the conversation (the user may have edited them)
      - If the `instruction` field delegates creation to a specific skill or
        command, invoke it; otherwise create the artifact file using `template`
        as the structure and write it to `resolvedOutputPath`. If
        `resolvedOutputPath` is a glob, follow `instruction` to choose the
        concrete file path
      - Apply `context` and `rules` as constraints — do NOT copy them into the file
      - Show brief progress: "Created <artifact-id>"

   b. **Continue until every artifact in the required set exists**
      - After creating each artifact, re-run `openspec status --change "<name>" --json`
      - The required set is `applyRequires` plus every artifact reachable from
        those by following the `requires` edges — walk them transitively.
        Leave artifacts outside that set alone
      - `status` is file-existence only: a `done` status does NOT mean all
        dependencies exist. Use each artifact's `requires` edges, not its
        `status`, to build the required set
      - An artifact already reading `status: "skipped"` is satisfied — never
        try to create one
      - Create every missing artifact in the required set, then re-check —
        creating one can unblock others
      - Skip one only when `status` reports it `skipped`, or when its own
        `instruction` marks it optional (run `openspec instructions` to verify)
      - Dependencies are enablers, not gates: if a required artifact is still
        `blocked` only because a conditional dependency was deliberately skipped,
        write it anyway

   c. **If an artifact requires user input** (unclear context):
      - Ask the user to clarify, then continue

6. **Show final status**
   ```bash
   openspec status --change "<name>"
   ```

**Output**

After completing all artifacts, summarize:
- Change name and location
- List of artifacts created with brief descriptions, plus any conditional
  artifact deliberately skipped and why
- What's ready: "All artifacts needed for implementation are ready."
- Prompt: "The artifacts are ready for review. When you are ready, run
  `/openspec-apply-change` or ask me to apply this change."

**Artifact Creation Guidelines**

- Follow the `instruction` field from `openspec instructions` for each artifact
  type — it is the authoritative guidance, even for familiar artifact names
- If the `instruction` delegates to a specific skill or command, invoke it
  instead of writing the artifact directly
- The schema defines what each artifact should contain — follow it
- Read dependency artifacts for context before creating new ones
- Use `template` as the structure — fill in its sections
- `context` and `rules` are constraints for you, not content for the file.
  Do NOT copy `<context>`, `<rules>`, or `<project_context>` blocks into
  the artifact

**Guardrails**
- The request that invoked this workflow authorizes planning only. Do NOT
  implement the change, start the apply workflow, or edit project code during
  this workflow. After presenting the artifacts, stop and wait for a new user
  request to start the apply workflow
- Create every artifact the apply phase transitively depends on, not just the
  ids listed in `apply.requires`
- Always read dependency artifacts before creating a new one — re-read from
  disk, not from conversation memory
- Ask about ambiguities that would materially change scope, observable behavior,
  compatibility, or acceptance criteria; for minor details, make reasonable
  assumptions and record them
- If a change with that name already exists, ask if the user wants to continue
  it or create a new one
- Verify each artifact file exists after writing before proceeding to the next
