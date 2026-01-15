# context.md
## RTS Colony Survival – Map Validation & Game Entry

### PROJECT OVERVIEW
RTS Colony Survival is a Medieval / RTS colony simulation game.

Stack:
- Backend: PHP (Laravel)
- Frontend: Livewire
- Database: MySQL
- Architecture: Persistent world simulation with queued generation and a tick loop

Maps are procedurally generated and persisted. A map must be validated before gameplay may begin.

---

## MAP LIFECYCLE

Maps move through explicit states:

- generating → tiles/resources being created
- validating → final checks before gameplay
- ready → approved for gameplay
- failed → invalid, cannot be played
- active → game has started

The `ready` state is authoritative and required before entering the game.

---

## REQUIRED MAP CONDITIONS

A valid map MUST have:
- Tiles exist (non-zero count)
- At least one land tile
- At least one water tile
- At least one mountain tile

No percentage thresholds or connectivity checks are required at this stage.

---

## DESIGN CONSTRAINTS

- Validation must be deterministic
- Validation occurs AFTER generation completes
- Validation is the final step before gameplay
- UI must respect map status at all times
- Do not introduce new features beyond what is specified

---

## NON-GOALS

Do NOT implement:
- Regeneration loops
- Advanced terrain heuristics
- Multiplayer logic
- Spawn logic
- AI or pathfinding changes

This file is authoritative project context.

