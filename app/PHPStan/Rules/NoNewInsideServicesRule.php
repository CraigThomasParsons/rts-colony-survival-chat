<?php

namespace App\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node>
 */
class NoNewInsideServicesRule implements Rule
{
    /**
     * @param class-string<Node> $nodeClass
     */
    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof New_) {
            return [];
        }

        if (! $this->isInsideTargetNamespace($scope->getClassReflection()?->getName())) {
            return [];
        }

        $className = $node->class;
        if ($className instanceof Node\Name) {
            $fqcn = $scope->resolveName($className);

            if ($this->isAllowedClass($fqcn)) {
                return [];
            }

            return [
                RuleErrorBuilder::message(
                    sprintf('Avoid instantiating %s directly; request it via dependency injection.', $fqcn)
                )->build(),
            ];
        }

        return [];
    }

    private function isInsideTargetNamespace(?string $class): bool
    {
        if ($class === null) {
            return false;
        }

        return str_starts_with($class, 'App\\Http\\Controllers\\')
            || str_starts_with($class, 'App\\Services\\')
            || str_starts_with($class, 'App\\Jobs\\');
    }

    private function isAllowedClass(string $class): bool
    {
        return str_starts_with($class, 'Illuminate\\')
            || str_starts_with($class, 'Livewire\\')
            || str_starts_with($class, 'StdClass')
            || str_starts_with($class, 'stdClass');
    }
}
