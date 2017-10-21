<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Form;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\Rector\Contrib\Symfony\Form\Helper\FormTypeStringToTypeProvider;

/**
 * Converts all:
 * $form->add('name', 'form.type.text');
 *
 * into:
 * $form->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
 *
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#frameworkbundle
 */
final class StringFormTypeToClassRector extends AbstractRector
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var FormTypeStringToTypeProvider
     */
    private $formTypeStringToTypeProvider;

    public function __construct(NodeFactory $nodeFactory, FormTypeStringToTypeProvider $formTypeStringToTypeProvider)
    {
        $this->nodeFactory = $nodeFactory;
        $this->formTypeStringToTypeProvider = $formTypeStringToTypeProvider;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof String_) {
            return false;
        }

        if (! $this->formTypeStringToTypeProvider->hasClassForNameWithPrefix($node->value)) {
            return false;
        }

        $methodCallName = (string) $node->getAttribute(Attribute::METHOD_CALL);

        return $methodCallName === 'add';
    }

    /**
     * @param String_ $stringNode
     */
    public function refactor(Node $stringNode): ?Node
    {
        $class = $this->formTypeStringToTypeProvider->getClassForNameWithPrefix($stringNode->value);

        return $this->nodeFactory->createClassConstantReference($class);
    }
}
