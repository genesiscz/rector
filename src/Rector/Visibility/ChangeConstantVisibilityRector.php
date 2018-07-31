<?php declare(strict_types=1);

namespace Rector\Rector\Visibility;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Node\Attribute;
use Rector\NodeModifier\VisibilityModifier;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ChangeConstantVisibilityRector extends AbstractRector
{
    /**
     * @var string[] { class => [ method name => visibility ] }
     */
    private $constantToVisibilityByClass = [];

    /**
     * @var VisibilityModifier
     */
    private $visibilityModifier;

    /**
     * @param string[] $constantToVisibilityByClass
     */
    public function __construct(array $constantToVisibilityByClass, VisibilityModifier $visibilityModifier)
    {
        $this->constantToVisibilityByClass = $constantToVisibilityByClass;
        $this->visibilityModifier = $visibilityModifier;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change visibility of constant from parent class.',
            [new CodeSample(
<<<'CODE_SAMPLE'
class FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}

class MyClass extends FrameworkClass
{
    public const SOME_CONSTANT = 1;
}
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
class FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}

class MyClass extends FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}
CODE_SAMPLE
            )]
        );
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof ClassConst) {
            return false;
        }

        // doesn't have a parent class
        if (! $node->hasAttribute(Attribute::PARENT_CLASS_NAME)) {
            return false;
        }

        $nodeParentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if (! isset($this->constantToVisibilityByClass[$nodeParentClassName])) {
            return false;
        }

        $constantName = $node->consts[0]->name->toString();

        return isset($this->constantToVisibilityByClass[$nodeParentClassName][$constantName]);
    }

    /**
     * @param ClassConst $classConstantNode
     */
    public function refactor(Node $classConstantNode): ?Node
    {
        $constantName = $classConstantNode->consts[0]->name->toString();
        $this->visibilityModifier->removeOriginalVisibilityFromFlags($classConstantNode);

        $nodeParentClassName = $classConstantNode->getAttribute(Attribute::PARENT_CLASS_NAME);
        $newVisibility = $this->constantToVisibilityByClass[$nodeParentClassName][$constantName];
        $this->visibilityModifier->addVisibilityFlag($classConstantNode, $newVisibility);

        return $classConstantNode;
    }
}
