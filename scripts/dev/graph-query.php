#!/usr/bin/env php
<?php
declare(strict_types=1);

$root = realpath(__DIR__ . '/../../');
$dbPath = $root . '/storage/graph.db';
if (!is_file($dbPath)) {
    fwrite(STDERR, "Graph DB not found at $dbPath. Run: composer graph:index\n");
    exit(1);
}
$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

[$script, $query] = $argv + [null, ''];
if (!$query) {
    fwrite(STDERR, "Usage: graph-query.php <substring>\nSearches nodes by name, prints connected edges.\n");
    exit(2);
}

$stmt = $db->prepare('SELECT id, type, name, file FROM nodes WHERE name LIKE ? LIMIT 20');
$stmt->execute(['%' . $query . '%']);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) { echo "No matches.\n"; exit(0);} 

foreach ($rows as $r) {
    printf("[%s] %s\n  file: %s\n", $r['type'], $r['name'], $r['file']);
    // outgoing edges
    $eo = $db->prepare('SELECT e.type, n.type AS ntype, n.name AS nname FROM edges e JOIN nodes n ON n.id=e.dst WHERE e.src=? LIMIT 20');
    $eo->execute([$r['id']]);
    foreach ($eo->fetchAll(PDO::FETCH_ASSOC) as $e) {
        printf("  ->(%s) [%s] %s\n", $e['type'], $e['ntype'], $e['nname']);
    }
    // incoming edges
    $ei = $db->prepare('SELECT e.type, n.type AS ntype, n.name AS nname FROM edges e JOIN nodes n ON n.id=e.src WHERE e.dst=? LIMIT 20');
    $ei->execute([$r['id']]);
    foreach ($ei->fetchAll(PDO::FETCH_ASSOC) as $e) {
        printf("  <-(%s) [%s] %s\n", $e['type'], $e['ntype'], $e['nname']);
    }
    echo "\n";
}
