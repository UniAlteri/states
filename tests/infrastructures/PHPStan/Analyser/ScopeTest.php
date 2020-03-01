<?php

/*
 * States.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\States\PHPStan\Analyser;

use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\VariableTypeHolder;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Properties\PropertyReflectionFinder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\DynamicReturnTypeExtensionRegistry;
use PHPStan\Type\OperatorTypeSpecifyingExtensionRegistry;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use Teknoo\States\PHPStan\Analyser\Scope;
use Teknoo\States\State\StateTrait;
use PHPUnit\Framework\TestCase;
use Teknoo\Tests\Support\Article\Article;
use Teknoo\Tests\Support\Article\Article\Draft;

/**
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers      \Teknoo\States\PHPStan\Analyser\Scope
 */
class ScopeTest extends TestCase
{
    private function prepareArguments(): array
    {
        return [
            $this->createMock(ScopeFactory::class), //0: ScopeFactory $scopeFactory
            $this->createMock(ReflectionProvider::class), //1: $reflectionProvider,
            $this->createMock(DynamicReturnTypeExtensionRegistry::class), //2: $dynamicReturnTypeExtensionRegistry,
            $this->createMock(OperatorTypeSpecifyingExtensionRegistry::class), //3 $operatorTypeSpecifyingExtensionRegistry,
            $this->createMock(Standard::class), //4: Standard $printer,
            $this->createMock(TypeSpecifier::class), //5: TypeSpecifier $typeSpecifier,
            $this->createMock(PropertyReflectionFinder::class), //6: PropertyReflectionFinder $propertyReflectionFinder,
            $this->createMock(ScopeContext::class), //7: ScopeContext $context,
            false, //8: bool $declareStrictTypes = \false,
            null, //9: $function = null,
            null, //10: ?string $namespace = null,
            ['foo' => VariableTypeHolder::createYes(new StringType())], //11: array $variablesTypes = [],
            [], //12: array $moreSpecificTypes = [],
            null, //13: ?string $inClosureBindScopeClass = null,
            null, //14: ?ParametersAcceptor $anonymousFunctionReflection = null,
            true, //15: bool $inFirstLevelStatement = \true,
            [], //16: array $currentlyAssignedExpressions = [],
            [] //17: array $dynamicConstantNames = []
        ];
    }

    public function testNoScopeUpdatingWithoutAnonymousReflectionAndClassReflection()
    {
        $arguments = $this->prepareArguments();
        $scope = new Scope(...$arguments);
        self::assertEquals(new StringType(), $scope->getVariableType('foo'));
    }

    public function testNoScopeUpdatingWithAnonymousReflectionAndNotClassReflection()
    {
        $arguments = $this->prepareArguments();
        $arguments[14] = $this->createMock(ParametersAcceptor::class);
        $scope = new Scope(...$arguments);
        self::assertEquals(new StringType(), $scope->getVariableType('foo'));
    }

    public function testNoScopeUpdatingWhenNoStateTraitUsed()
    {
        $arguments = $this->prepareArguments();
        $arguments[14] = $this->createMock(ParametersAcceptor::class);
        $arguments[7]->expects(self::any())->method('getClassReflection')->willReturn($classReflection = $this->createMock(ClassReflection::class));
        $nativeReflection = $this->createMock(\ReflectionClass::class);
        $nativeReflection->expects(self::any())->method('getTraitNames')->willReturn([]);
        $classReflection->expects(self::any())->method('getNativeReflection')->willReturn($nativeReflection);
        $scope = new Scope(...$arguments);
        self::assertEquals(new StringType(), $scope->getVariableType('foo'));
    }

    public function testNoScopeUpdatingWithoutThisDefinedInScope()
    {
        $arguments = $this->prepareArguments();
        $arguments[14] = $this->createMock(ParametersAcceptor::class);
        $arguments[7]->expects(self::any())->method('getClassReflection')->willReturn($classReflection = $this->createMock(ClassReflection::class));
        $nativeReflection = $this->createMock(\ReflectionClass::class);
        $nativeReflection->expects(self::any())->method('getTraitNames')->willReturn([StateTrait::class]);
        $classReflection->expects(self::any())->method('getNativeReflection')->willReturn($nativeReflection);
        $scope = new Scope(...$arguments);
        self::assertEquals(new StringType(), $scope->getVariableType('foo'));
    }

    public function testExceptionWhenProxyClassDoesNotExist()
    {
        $this->expectException(ShouldNotHappenException::class);

        $arguments = $this->prepareArguments();
        $arguments[14] = $this->createMock(ParametersAcceptor::class);
        $arguments[7]->expects(self::any())->method('getClassReflection')->willReturn($classReflection = $this->createMock(ClassReflection::class));
        $nativeReflection = $this->createMock(\ReflectionClass::class);
        $nativeReflection->expects(self::any())->method('getTraitNames')->willReturn([StateTrait::class]);
        $nativeReflection->expects(self::any())->method('getName')->willReturn('Foo\Bar\Not\ExistClass');
        $classReflection->expects(self::any())->method('getNativeReflection')->willReturn($nativeReflection);
        $arguments[11]['this'] = VariableTypeHolder::createYes($this->createMock(ThisType::class));
        $scope = new Scope(...$arguments);
    }

    public function testExceptionWhenProxyClassDoesNotImplementInterface()
    {
        $this->expectException(ShouldNotHappenException::class);

        $arguments = $this->prepareArguments();
        $arguments[14] = $this->createMock(ParametersAcceptor::class);
        $arguments[7]->expects(self::any())->method('getClassReflection')->willReturn($classReflection = $this->createMock(ClassReflection::class));
        $nativeReflection = $this->createMock(\ReflectionClass::class);
        $nativeReflection->expects(self::any())->method('getTraitNames')->willReturn([StateTrait::class]);
        $nativeReflection->expects(self::any())->method('getName')->willReturn(\DateTime::class.'\\Foo');
        $classReflection->expects(self::any())->method('getNativeReflection')->willReturn($nativeReflection);
        $arguments[11]['this'] = VariableTypeHolder::createYes($this->createMock(ThisType::class));
        $scope = new Scope(...$arguments);
    }

    public function testScopeUpdating()
    {
        $arguments = $this->prepareArguments();
        $arguments[14] = $this->createMock(ParametersAcceptor::class);
        $arguments[7]->expects(self::any())->method('getClassReflection')->willReturn($classReflection = $this->createMock(ClassReflection::class));
        $nativeReflection = $this->createMock(\ReflectionClass::class);
        $nativeReflection->expects(self::any())->method('getTraitNames')->willReturn([StateTrait::class]);
        $nativeReflection->expects(self::any())->method('getName')->willReturn(Draft::class);
        $classReflection->expects(self::any())->method('getNativeReflection')->willReturn($nativeReflection);
        $arguments[11]['this'] = VariableTypeHolder::createYes($this->createMock(ThisType::class));
        $scope = new Scope(...$arguments);

        self::assertEquals(new StringType(), $scope->getVariableType('foo'));
        self::assertEquals(new ThisType(Article::class), $scope->getVariableType('this'));
    }
}