# Coding Conventions

Readability always wins unless we are explicitly tuning for performance. These conventions reflect that priority and apply across PHP, Blade, and Livewire code in this repo.

## Boolean checks
- Prefer explicit boolean comparisons over the logical NOT operator.
- When checking `isset()` (or any boolean-returning helper), use `=== true` / `=== false` so the intent is obvious at a glance.

```php
// ✅ Preferred
if (isset($payload['vlah']) === false) {
    return;
}

$shouldQueue = $job->shouldDispatch() === true;

// ❌ Avoid
if (! isset($payload['vlah'])) {
    return;
}
$shouldQueue = !$job->shouldDispatch();
```

## Conditional structure
- Never write single-line `if` statements.
- Always include braces, even for one-line bodies.
- Break long conditions across multiple lines for clarity instead of collapsing them into inline expressions.

```php
// ✅ Preferred
if ($user->isBanned() === true) {
    $user->notify(new BanReminder());
}

// ❌ Avoid
if ($user->isBanned()) $user->notify(new BanReminder());
if ($user->isBanned()) return;
```

## Ternary operators
- Do not use ternary (`condition ? valueA : valueB`). Use full `if/else` blocks instead so branching is obvious and easy to skim.
- Guard clauses (`if (...) { return ...; }`) are fine—just keep them multi-line and brace-wrapped.

```php
// ✅ Preferred
if ($request->has('seed') === true) {
    $map->seed = $request->input('seed');
} else {
    $map->seed = $map->id;
}

// ❌ Avoid
$map->seed = $request->has('seed') ? $request->input('seed') : $map->id;
```

## Dependency inversion & injection
- Honor the "D" in SOLID by depending on abstractions instead of concretes whenever practical.
- Favor constructor or method injection so collaborators can be swapped, mocked, or decorated easily; avoid static facades unless the framework requires them.
- When a class needs another service, inject an interface (or at least the concrete) via Laravel's container instead of instantiating it inline.
- Great refresher: [Brains to Bytes – Dependency Injection](https://www.brainstobytes.com/dependency-injection/).

```php
// ✅ Preferred
final class MapGeneratorService
{
    public function __construct(private readonly TerrainGeneratorContract $terrainGenerator)
    {
    }

    public function generate(Map $map): void
    {
        $layout = $this->terrainGenerator->build($map);
        // ...
    }
}

// ❌ Avoid
final class MapGeneratorService
{
    public function generate(Map $map): void
    {
        $terrainGenerator = new TerrainGenerator(); // inline dependency makes testing/composition hard
        $layout = $terrainGenerator->build($map);
    }
}
```

## General philosophy
- Favor expressive names and explicit conditionals over clever one-liners.
- Use early returns with full `if` blocks when it improves clarity, but never drop braces or cram them onto one line.
- Only sacrifice readability when profiling proves that the change is required for performance.
- Enforce these rules with `composer cs:fix` (see README for setup) to keep the codebase consistent.

Following these guidelines keeps the codebase approachable for the team and ensures new contributors can reason about logic without unraveling terse expressions.
