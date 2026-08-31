# Spec Format Reference

## Delta Spec Format

Delta specs live inside a change directory and describe what changed relative
to the current main spec.

```markdown
## Purpose

Only on a delta that introduces a brand-new capability. Seeds the new main spec.

## ADDED Requirements

### Requirement: New Feature
The system SHALL do something new.

#### Scenario: Basic case
- **WHEN** user does X
- **THEN** system does Y

## MODIFIED Requirements

### Requirement: Existing Feature
The system SHALL keep doing the existing thing, now also handling A.

#### Scenario: Scenario the main spec already has
- **WHEN** user does X
- **THEN** system does Y

#### Scenario: New scenario to add
- **WHEN** user does A
- **THEN** system does B

## REMOVED Requirements

### Requirement: Deprecated Feature

## RENAMED Requirements

- FROM: `### Requirement: Old Name`
- TO: `### Requirement: New Name`
```

## Main Spec Format

Main specs are what deltas merge INTO. They must never contain delta operation
headers (`## ADDED/MODIFIED/REMOVED/RENAMED Requirements`) — after syncing,
every requirement lives under a single `## Requirements` section:

```markdown
# <capability> Specification

## Purpose
Short description of what this capability does and why it exists.

## Requirements

### Requirement: New Feature
The system SHALL do something new.

#### Scenario: Basic case
- **WHEN** user does X
- **THEN** system does Y
```

## Key Principle: Intelligent Merging

Unlike programmatic merging, merge rather than overwrite:
- A MODIFIED block carries the whole requirement — body plus every scenario
  that survives the change. `openspec validate` and `openspec archive` both
  reject one that drops a scenario the main spec still has.
- Keep anything the delta does not mention, in the main spec's existing order.
- Use judgment to merge changes sensibly.
