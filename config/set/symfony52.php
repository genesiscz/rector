<?php

declare(strict_types=1);

use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstantRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassConstant;
use Rector\Symfony5\Rector\New_\PropertyAccessorCreationBooleanToFlagsRector;
use Rector\Symfony5\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

# https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/symfony50-types.php');

    $services = $containerConfigurator->services();

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#httpfoundation
    $services->set(BinaryFileResponseCreateToNewInstanceRector::class);

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#mime
    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename('Symfony\Component\Mime\Address', 'fromString', 'create'),
            ]),
        ]]);

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#propertyaccess
    $services->set(PropertyAccessorCreationBooleanToFlagsRector::class);

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#security
    $services->set(RenameClassConstantRector::class)
        ->call('configure', [[
            RenameClassConstantRector::CLASS_CONSTANT_RENAME => ValueObjectInliner::inline([
                new RenameClassConstant(
                    'Symfony\Component\Security\Http\Firewall\AccessListener',
                    'PUBLIC_ACCESS',
                    'Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter::PUBLIC_ACCESS'
                ),
            ]),
        ]]);
};
