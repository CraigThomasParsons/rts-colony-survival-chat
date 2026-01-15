# Editor Graph (MVP)

This is a minimal semantic indexer for the repo that builds a local SQLite graph with nodes (classes, functions, methods) and edges (basic relations).

## Why
- Quickly answer questions like: which controller methods are bound to routes?
- Provide a base for smarter refactors and test impact analysis.

## Files
- `scripts/dev/graph-index.php`: builds `storage/graph.db` (requires `nikic/php-parser`)
- `scripts/dev/graph-query.php`: search nodes and print edges

## Usage
```bash
# Install dev deps
composer install

# Build the graph
composer graph:index

# Query by substring
php scripts/dev/graph-query.php MapController
```

## Schema (simplified)
- nodes(id, type, name, file, extra)
- edges(src, dst, type)

Types currently indexed:
- class, function, method (route bindings produce method-like nodes of `Class@method`)

## Roadmap
- Parse methods inside classes and add `class -> method` edges
- Link Blade views, events/jobs/queues, migrations
- Export JSON for UI panels
