<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Model;

use Labrador\AsyncUnit\Framework\HookType;
use PhpParser\Node\Stmt\ClassMethod;

/**
 * A representation of an invokable class method annotated with an AsyncUnit hook attribute.
 *
 * @package Labrador\AsyncUnit\Framework\Model
 */
final class HookModel {

    use MethodModelTrait {
        MethodModelTrait::__construct as setClassAndMethod;
    }

    private HookType $type;
    private int $priority;

    public function __construct(
        string $class,
        string $classMethod,
        HookType $type,
        int $priority = 0
    ) {
        $this->setClassAndMethod($class, $classMethod);
        $this->type = $type;
        $this->priority = $priority;
    }

    /**
     * Returns the type of the hook; this corresponds to the simple class name for the attribute annotated on the class
     * method.
     *
     * @return HookType
     * @todo In 8.1 convert this to use a native PHP enum
     */
    public function getType() : HookType {
        return $this->type;
    }

    public function getPriority() : int {
        return $this->priority;
    }

}