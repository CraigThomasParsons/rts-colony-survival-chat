#!/usr/bin/env php
<?php
declare(strict_types=1);

// Minimal semantic indexer (Phase 0 MVP)
// - Scans routes and app/ for PHP files
// - Extracts class names, functions, and basic "route -> controller@method" edges
// - Stores to a lightweight SQLite DB at storage/graph.db

require __DIR__ . '/../../vendor/autoload.php';

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

$root = realpath(__DIR__ . '/../../');
$dbPath = $root . '/storage/graph.db';
@mkdir($root . '/storage', 0777, true);

$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec('PRAGMA journal_mode=WAL');
$db->exec('CREATE TABLE IF NOT EXISTS nodes (id INTEGER PRIMARY KEY, type TEXT, name TEXT, file TEXT, extra TEXT)');
$db->exec('CREATE TABLE IF NOT EXISTS edges (src INTEGER, dst INTEGER, type TEXT)');
$db->exec('CREATE INDEX IF NOT EXISTS idx_nodes_name ON nodes(name)');

function putNode(PDO $db, string $type, string $name, string $file, array $extra = []): int {
    $stmt = $db->prepare('INSERT INTO nodes(type, name, file, extra) VALUES(?,?,?,?)');
    $stmt->execute([$type, $name, $file, json_encode($extra)]);
    return (int)$db->lastInsertId();
}

function findNodeId(PDO $db, string $type, string $name): ?int {
    $stmt = $db->prepare('SELECT id FROM nodes WHERE type=? AND name=? LIMIT 1');
    $stmt->execute([$type, $name]);
    $id = $stmt->fetchColumn();
    return $id !== false ? (int)$id : null;
}

function putEdge(PDO $db, int $src, int $dst, string $type): void {
    $stmt = $db->prepare('INSERT INTO edges(src, dst, type) VALUES(?,?,?)');
    $stmt->execute([$src, $dst, $type]);
}

$parser = (new ParserFactory())->createForHostVersion();

// 1) Index controllers and classes under app/
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/app'));
foreach ($rii as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') continue;
    $code = file_get_contents($file->getPathname());
    try {
        $ast = $parser->parse($code);
    } catch (Error $e) {
        // Skip parse errors
        continue;
    }
    $ns = '';
    $traverser = new NodeTraverser();
    $traverser->addVisitor(new class($db, $file->getPathname(), $ns) extends NodeVisitorAbstract {
        public function __construct(private PDO $db, private string $file, private string $ns) {}
        public function enterNode(Node $node) {
            if ($node instanceof Node\Stmt\Namespace_) {
                $this->ns = implode('\\', $node->name->parts ?? []);
            }
            if ($node instanceof Node\Stmt\Class_) {
                $name = ($this->ns ? $this->ns.'\\' : '') . ($node->name?->toString() ?? 'anonymous');
                putNode($this->db, 'class', $name, $this->file);
            }
            if ($node instanceof Node\Stmt\Function_) {
                $fname = ($this->ns ? $this->ns.'\\' : '').$node->name->toString();
                putNode($this->db, 'function', $fname, $this->file);
            }
            return null;
        }
    });
    $traverser->traverse($ast ?? []);
}

// 2) Parse routes to map route -> controller@method
$routeFiles = glob($root . '/routes/*.php');
foreach ($routeFiles as $rf) {
    $content = file_get_contents($rf);
    // naive match patterns: Route::get('/path', [Controller::class, 'method']); or 'Controller@method'
    $pattern1 = '/\\[\\\w]+::class\s*,\s*\'([^\']+)\'/';
    // We'll do a simple token approach too
    if (preg_match_all('/([A-Za-z0-9_\\\\]+)::class\s*,\s*\'([A-Za-z0-9_]+)\'/', $content, $m, PREG_SET_ORDER)) {
        foreach ($m as $mm) {
            $fqcn = $mm[1];
            $method = $mm[2];
            $cid = findNodeId($db, 'class', $fqcn) ?? putNode($db, 'class', $fqcn, $rf);
            $fid = putNode($db, 'method', $fqcn.'@'.$method, $rf);
            putEdge($db, $cid, $fid, 'declares');
            // Create a generic route node
            putEdge($db, $fid, $cid, 'belongs_to');
        }
    }
    if (preg_match_all('/\'([A-Za-z0-9_\\\\]+)@([A-Za-z0-9_]+)\'/', $content, $m2, PREG_SET_ORDER)) {
        foreach ($m2 as $mm) {
            $fqcn = $mm[1];
            $method = $mm[2];
            $cid = findNodeId($db, 'class', $fqcn) ?? putNode($db, 'class', $fqcn, $rf);
            $fid = putNode($db, 'method', $fqcn.'@'.$method, $rf);
            putEdge($db, $cid, $fid, 'declares');
        }
    }
}

echo "Indexed graph at: $dbPath\n";
