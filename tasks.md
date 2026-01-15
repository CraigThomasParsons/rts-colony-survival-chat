# tasks.md
## Tonight’s Tasks – Map Validation & Game Entry

### TASK 1: Add Final Map Validation Step

- Create a `MapValidator` service
- Input: `Map $map`
- Perform required terrain checks:
  - tiles.count > 0
  - land tiles >= 1
  - water tiles >= 1
  - mountain tiles >= 1
  - trees >= 1

- Integrate validation as the FINAL step in the MapGenerator queue process

#### On Success
- Set:
  - `map.status = 'ready'`
  - `map.validated_at = now()`

#### On Failure
- Set:
  - `map.status = 'failed'`
- Store validation errors if applicable

---

### TASK 2: UI Gating

- Update UI to reflect map status:
  - generating
  - validating
  - ready
  - failed

- Ensure the “Start Game” button:
  - Is hidden or disabled unless `map.status === 'ready'`

---

### TASK 3: Start Game Route

- Add backend route/action:
  - `POST /maps/{map}/start`

- Guard conditions:
  - Abort unless `map.status === 'ready'`

- On success:
  - `map.status = 'active'`
  - `map.started_at = now()`
  - Redirect to main game view

---

## DEFINITION OF DONE

- Invalid maps cannot be started
- Only validated maps become playable
- UI enforces map lifecycle rules
- Clean entry into game simulation exists

