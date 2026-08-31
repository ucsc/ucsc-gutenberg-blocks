# Store Selection

If the user names a store (a store is a standalone OpenSpec repo registered on
this machine) or the work lives in one:

1. Run `openspec store list --json` to discover registered store ids.
2. Pass `--store <id>` on every command that reads or writes specs or changes:
   `new change`, `status`, `instructions`, `list`, `show`, `validate`,
   `archive`, `doctor`, `context`, `schemas`, `view`.
3. Treat `--store <id>` as sticky for the rest of the workflow — append it to
   every applicable follow-up command even when the inline examples below omit
   it (those are shorthand; prepend the flag before running them).
4. Hints printed by commands already carry the flag; keep it on follow-ups.

Other commands do not accept `--store`. Without a store, commands act on the
nearest local `openspec/` root.

**Example:** run `openspec status --change "<name>" --json --store "<id>"`,
not the unscoped form shown in the skill body.
