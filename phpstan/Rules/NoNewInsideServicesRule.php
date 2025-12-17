<?php

namespace PHPStan\Custom\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<New_>
 */
final class NoNewInsideServicesRule implements Rule
{
    public function getNodeType(): string
    {
        return New_::class;
    }

    /**
     * @param New_ $node
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof New_) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return [];
        }

        $declaringClass = $classReflection->getName();
        if (! $this->isInsideTargetNamespace($declaringClass)) {
            return [];
        }

        $className = $node->class;
        if ($className instanceof Name) {
            $fqcn = $scope->resolveName($className);

            if ($this->isAllowedClass($fqcn)) {
                return [];
            }

            return [
                RuleErrorBuilder::message(sprintf(
                    'Avoid instantiating %s directly; prefer dependency injection.',
                    $fqcn
                ))->build(),
            ];
        }

        return [];
    }

    private function isInsideTargetNamespace(string $class): bool
    {
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
