<?php

/*
 * States.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\States\PHPStan\Reflection;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\UnionType;
use PHPStan\BetterReflection\BetterReflection;
use PHPStan\BetterReflection\Util\Exception\NoNodePosition;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\Php\BuiltinMethodReflection;
use PHPStan\TrinaryLogic;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionFunction;
use PHPStan\BetterReflection\Reflection\ReflectionFunction as BetterReflectionFunction;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionIntersectionType;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionNamedType;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionParameter;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionUnionType;
use PHPStan\BetterReflection\Reflection\ReflectionParameter as BetterReflectionParameter;
use ReflectionIntersectionType as NativeReflectionIntersectionType;
use ReflectionMethod as NativeReflectionMethod;
use ReflectionFunction as NativeReflectionFunction;
use ReflectionNamedType as NativeReflectionNamedType;
use ReflectionUnionType as NativeReflectionUnionType;
use Teknoo\States\PHPStan\Reflection\Exception\MissingReflectionMethodException;

use function array_map;

/**
 * To provide a PHPStan reflection for state's methode in a stated class.
 *
 * State's method are cloure binded dynamically to the parent scope, So the APIs Reflection provides a
 * `ReflectionFunction` instead of `ReflectionMethod` and lost method's status about visibility (private/public),
 * static etc.. *
 *
 * This class provide a valid PHPStan reflection for these method from the PHPStan API Reflection on the closure
 * and the closure factory. A fallback to native API Reflection is provided when PHPSTan's Reflection is not available
 * for states classes (disable by AST Visitor of States to avoid anothers false positives)
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class StateMethod implements ExtendedMethodReflection
{
    public function __construct(
        private readonly ReflectionMethod|NativeReflectionMethod $factoryReflection,
        private readonly ReflectionFunction|NativeReflectionFunction $closureReflection,
        private readonly ReflectionClass $reflectionClass,
    ) {
    }

    public function getName(): string
    {
        return $this->factoryReflection->getName();
    }

    public function getReflection(): ReflectionMethod
    {
        if ($this->factoryReflection instanceof ReflectionMethod) {
            return $this->factoryReflection;
        }

        throw new MissingReflectionMethodException("Missing Factory Reflection Method");
    }

    public function getFileName(): ?string
    {
        if (empty($fileName = $this->factoryReflection->getFileName())) {
            return null;
        }

        return $fileName;
    }

    public function getDeclaringClass(): ReflectionClass
    {
        return $this->reflectionClass;
    }

    public function getStartLine(): ?int
    {
        if (empty($startLine = $this->closureReflection->getStartLine())) {
            return null;
        }

        return $startLine;
    }

    public function getEndLine(): ?int
    {
        if (empty($endLine = $this->closureReflection->getEndLine())) {
            return null;
        }

        return $endLine;
    }

    public function getDocComment(): ?string
    {
        $doc = $this->factoryReflection->getDocComment();

        if (false === $doc) {
            return null;
        }

        return $doc;
    }

    public function isStatic(): bool
    {
        return $this->factoryReflection->isStatic();
    }

    public function isPrivate(): bool
    {
        return $this->factoryReflection->isPrivate();
    }

    public function isPublic(): bool
    {
        return $this->factoryReflection->isPublic();
    }

    public function getPrototype(): BuiltinMethodReflection
    {
        return $this;
    }

    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean($this->factoryReflection->isDeprecated());
    }

    public function isFinal(): bool
    {
        return $this->factoryReflection->isFinal();
    }

    public function isInternal(): bool
    {
        return $this->factoryReflection->isInternal();
    }

    public function isAbstract(): bool
    {
        return $this->factoryReflection->isAbstract();
    }

    public function isVariadic(): bool
    {
        return $this->closureReflection->isVariadic();
    }

    public function getReturnType(): ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType|null
    {
        if (!$this->closureReflection instanceof ReflectionFunction) {
            return null;
        }

        return $this->closureReflection->getReturnType();
    }

    public function getTentativeReturnType(): ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType|null
    {
        return null;
    }

    private static function buildFinalType(
        mixed $type
    ): IntersectionType|UnionType|Name|NullableType|null {
        $finalType = null;
        if ($type instanceof NativeReflectionIntersectionType) {
            $finalType = new IntersectionType(
                array_map(
                    static fn (NativeReflectionNamedType $namedType): Name => new Name($namedType->getName()),
                    $type->getTypes()
                )
            );
        }

        if ($type instanceof NativeReflectionUnionType) {
            $allowNull = $type->allowsNull();
            $types = array_map(
                static function (NativeReflectionNamedType $namedType) use (&$allowNull): Name {
                    $allowNull = $allowNull || $namedType->allowsNull();
                    return new Name($namedType->getName());
                },
                $type->getTypes()
            );
            if (true === $allowNull) {
                $types[] = new Identifier('null');
            }

            $finalType = new UnionType($types);
        }

        if ($type instanceof NativeReflectionNamedType) {
            $finalType = new Name($type->getName());
            if ($type->allowsNull()) {
                $finalType = new NullableType($finalType);
            }
        }

        return $finalType;
    }

    /**
     * @return ReflectionParameter[]
     */
    public function getParameters(): array
    {
        if (!$this->closureReflection instanceof ReflectionFunction) {
            //Simulate a BetterReflectionFunction behavior when the extension was not able
            //to create it (ast node must be removed by ASTVisitor to avoid false postive)
            $final = [];
            foreach ($this->closureReflection->getParameters() as $parameter) {
                $default = null;
                if ($parameter->isOptional() && null !== ($defaultValue = $parameter->getDefaultValue())) {
                    $default = new Variable($defaultValue);
                }

                $finalType = null;
                if (null !== ($type = $parameter->getType())) {
                    $finalType = self::buildFinalType($type);
                }

                $final[] = new ReflectionParameter(
                    BetterReflectionParameter::createFromNode(
                        reflector: (new BetterReflection())->reflector(),
                        node: new Param(
                            var: new Variable((string) $parameter->getName()),
                            default: $default,
                            type: $finalType,
                            byRef: $parameter->isPassedByReference(),
                            variadic: $parameter->isVariadic(),
                            attributes: $parameter->getAttributes(),
                            flags: 0,
                        ),
                        function: new class ($this->factoryReflection) extends BetterReflectionFunction {
                            public function __construct(
                                private readonly NativeReflectionMethod $method,
                            ) {
                            }

                            //@codeCoverageIgnoreStart
                            public function inNamespace(): bool
                            {
                                return false;
                            }

                            public function getFileName(): ?string
                            {
                                return null;
                            }

                            public function getShortName(): string
                            {
                                return $this->method->getShortName();
                            }

                            //@codeCoverageIgnoreEnd

                            public function getLocatedSource(): never
                            {
                                //Throw an exception to hack BetterReflectionParaneeter constructor
                                //To not extract lines
                                throw new NoNodePosition();
                            }
                        },
                        parameterIndex: $parameter->getPosition(),
                        isOptional: $parameter->isOptional(),
                    )
                );
            }

            return $final;
        }

        return $this->closureReflection->getParameters();
    }

    public function returnsByReference(): TrinaryLogic
    {
        if ($this->closureReflection->returnsReference()) {
            return TrinaryLogic::createYes();
        }

        return TrinaryLogic::createNo();
    }
}
