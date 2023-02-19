<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Parser;

use Cspray\Labrador\AsyncUnit\Attribute\AfterAll;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEachTest;
use Cspray\Labrador\AsyncUnit\Attribute\AttachToTestSuite;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeAll;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEachTest;
use Cspray\Labrador\AsyncUnit\Attribute\DataProvider;
use Cspray\Labrador\AsyncUnit\Attribute\DefaultTestSuite;
use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Attribute\Timeout;
use Cspray\Labrador\AsyncUnit\CustomAssertionPlugin;
use Cspray\Labrador\AsyncUnit\HookType;
use Cspray\Labrador\AsyncUnit\Model\HookModel;
use Cspray\Labrador\AsyncUnit\Model\PluginModel;
use Cspray\Labrador\AsyncUnit\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Model\TestModel;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;
use Cspray\Labrador\AsyncUnit\ResultPrinterPlugin;
use Cspray\Labrador\AsyncUnit\Exception\TestCompilationException;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\TestSuite;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

/**
 * Responsible for interacting with PHP-Parser to transform Nodes into appropriate AsyncUnit models.
 * @internal
 */
final class AsyncUnitModelNodeVisitor extends NodeVisitorAbstract implements NodeVisitor {

    use AttributeGroupTraverser;

    public function __construct(private AsyncUnitModelCollector $collector) {}

    public function leaveNode(Node $node) : void {
        // Do not change this hook from leaveNode, we need the other visitors we rely on to be invoked before we start
        // interacting with nodes. Otherwise your class names may be missing the namespace and not be FQCN
        $validPluginTypes = [
            CustomAssertionPlugin::class,
            ResultPrinterPlugin::class
        ];
        if ($node instanceof Node\Stmt\Class_) {
            $class = $node->namespacedName->toString();
            if (is_subclass_of($class, TestSuite::class)) {
                $defaultTestSuiteAttribute = $this->findAttribute(DefaultTestSuite::class, ...$node->attrGroups);
                $testSuiteModel = new TestSuiteModel($class, !is_null($defaultTestSuiteAttribute));
                if ($disabledAttribute = $this->findAttribute(Disabled::class, ...$node->attrGroups)) {
                    $reason = null;
                    if (count($disabledAttribute->args) === 1) {
                        // TODO Make sure that the disabled value is a string, otherwise throw an error
                        $reason = $disabledAttribute->args[0]->value->value;
                    }
                    $testSuiteModel->markDisabled($reason);
                }
                if ($timeoutAttribute = $this->findAttribute(Timeout::class, ...$node->attrGroups)) {
                    $testSuiteModel->setTimeout($timeoutAttribute->args[0]->value->value);
                }
                $this->collector->attachTestSuite($testSuiteModel);
            } else if (is_subclass_of($class, TestCase::class)) {
                if ($node->isAbstract()) {
                    return;
                }
                $testSuiteAttribute = $this->findAttribute(AttachToTestSuite::class, ...$node->attrGroups);
                $testSuiteClassName = null;
                if (!is_null($testSuiteAttribute)) {
                    // TODO Ensure that a string can be passed to AttachToTestSuite as well as ::class, any other type should throw error
                    $testSuiteClassName = $testSuiteAttribute->args[0]->value->class->toString();
                }
                $testCaseModel = new TestCaseModel($class, $testSuiteClassName);
                if ($disabledAttribute = $this->findAttribute(Disabled::class, ...$node->attrGroups)) {
                    $reason = null;
                    if (count($disabledAttribute->args) === 1) {
                        // TODO Make sure that the disabled value is a string, otherwise throw an error
                        $reason = $disabledAttribute->args[0]->value->value;
                    }
                    $testCaseModel->markDisabled($reason);
                }
                if ($timeoutAttribute = $this->findAttribute(Timeout::class, ...$node->attrGroups)) {
                    // TODO make sure we add more error checks around the presence of an argument and its value matching expected types
                    $testCaseModel->setTimeout($timeoutAttribute->args[0]->value->value);
                }

                $this->collector->attachTestCase($testCaseModel);
            } else {
                foreach ($validPluginTypes as $validPluginType) {
                    if (is_subclass_of($class, $validPluginType)) {
                        $this->collector->attachPlugin(new PluginModel($class));
                    }
                }
            }
        } else if ($node instanceof Node\Stmt\ClassMethod) {
            $this->collectIfHasAnyAsyncUnitAttribute($node);
        }
    }

    private function collectIfHasAnyAsyncUnitAttribute(Node\Stmt\ClassMethod $classMethod) : void {
        $validAttributes = [
            Test::class => fn() => $this->validateTest($classMethod),
            BeforeAll::class => fn(Node\Attribute $attribute) => $this->validateBeforeAll($attribute, $classMethod),
            BeforeEach::class => fn(Node\Attribute $attribute) => $this->validateBeforeEach($attribute, $classMethod),
            AfterAll::class => fn(Node\Attribute $attribute) => $this->validateAfterAll($attribute, $classMethod),
            AfterEach::class => fn(Node\Attribute $attribute) => $this->validateAfterEach($attribute, $classMethod),
            BeforeEachTest::class => fn(Node\Attribute $attribute) => $this->validateBeforeEachTest($attribute, $classMethod),
            AfterEachTest::class => fn(Node\Attribute $attribute) => $this->validateAfterEachTest($attribute, $classMethod)
        ];
        foreach ($validAttributes as $validAttribute => $validator) {
            $attribute = $this->findAttribute($validAttribute, ...$classMethod->attrGroups);
            if (!is_null($attribute)) {
                $className = $classMethod->getAttribute('parent')->namespacedName->toString();
                if (!class_exists($className)) {
                    $msg = sprintf(
                        'Failure compiling %s. The class cannot be autoloaded. Please ensure your Composer autoloader settings have been configured correctly',
                        $className
                    );
                    throw new TestCompilationException($msg);
                }
                $validator($attribute);
            }
        }
    }

    private function validateTest(Node\Stmt\ClassMethod $classMethod) : void {
        $className = $classMethod->getAttribute('parent')->namespacedName->toString();
        if (!is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[Test] but this class does not extend "%s".',
                $className,
                $classMethod->name->toString(),
                TestCase::class
            );
            throw new TestCompilationException($msg);
        }

        $testModel = new TestModel((string) $className, $classMethod->name->toString());
        if ($disabledAttribute = $this->findAttribute(Disabled::class, ...$classMethod->attrGroups)) {
            $reason = null;
            if (count($disabledAttribute->args) === 1) {
                // TODO Make sure that the disabled value is a string, otherwise throw an error
                $reason = $disabledAttribute->args[0]->value->value;
            }
            $testModel->markDisabled($reason);
        }
        $dataProviderAttribute = $this->findAttribute(DataProvider::class, ...$classMethod->attrGroups);
        if (!is_null($dataProviderAttribute)) {
            // TODO Make sure that the data provider value is a string, otherwise throw an error
            $testModel->setDataProvider($dataProviderAttribute->args[0]->value->value);
        }
        if ($timeoutAttribute = $this->findAttribute(Timeout::class, ...$classMethod->attrGroups)) {
            $value = $timeoutAttribute->args[0]->value->value;
            $testModel->setTimeout($value);
        }

        $this->collector->attachTest($testModel);
    }

    private function validateBeforeEach(Node\Attribute $attribute, Node\Stmt\ClassMethod $classMethod) : void {
        $className = $classMethod->getAttribute('parent')->namespacedName->toString();
        if (!is_subclass_of($className, TestSuite::class) && !is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[BeforeEach] but this class does not extend "%s" or "%s".',
                $className,
                $classMethod->name->toString(),
                TestSuite::class,
                TestCase::class
            );
            throw new TestCompilationException($msg);
        }

        $this->collector->attachHook(
            new HookModel(
                (string) $className,
                $classMethod->name->toString(),
                HookType::BeforeEach,
                $this->getPriority($attribute)
            )
        );
    }

    private function validateAfterEach(Node\Attribute $attribute, Node\Stmt\ClassMethod $classMethod) : void {
        $className = $classMethod->getAttribute('parent')->namespacedName->toString();
        if (!is_subclass_of($className, TestSuite::class) && !is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[AfterEach] but this class does not extend "%s" or "%s".',
                $className,
                $classMethod->name->toString(),
                TestSuite::class,
                TestCase::class
            );
            throw new TestCompilationException($msg);
        }

        $this->collector->attachHook(
            new HookModel(
                (string) $className,
                $classMethod->name->toString(),
                HookType::AfterEach,
                $this->getPriority($attribute)
            )
        );
    }

    private function validateBeforeAll(Node\Attribute $attribute, Node\Stmt\ClassMethod $classMethod) : void {
        $className = $classMethod->getAttribute('parent')->namespacedName->toString();
        if (!is_subclass_of($className, TestSuite::class) && !is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[BeforeAll] but this class does not extend "%s" or "%s".',
                $className,
                $classMethod->name->toString(),
                TestSuite::class,
                TestCase::class
            );
            throw new TestCompilationException($msg);
        } else if (is_subclass_of($className, TestCase::class)) {
            if (!$classMethod->isStatic()) {
                $msg = sprintf(
                    'Failure compiling "%s". The non-static method "%s" cannot be used as a #[BeforeAll] hook.',
                    $classMethod->getAttribute('parent')->namespacedName->toString(),
                    $classMethod->name->toString(),
                );
                throw new TestCompilationException($msg);
            }
        }


        $this->collector->attachHook(
            new HookModel(
                (string) $className,
                $classMethod->name->toString(),
                HookType::BeforeAll,
                $this->getPriority($attribute)
            )
        );
    }

    private function validateAfterAll(Node\Attribute $attribute, Node\Stmt\ClassMethod $classMethod) : void {
        $className = $classMethod->getAttribute('parent')->namespacedName->toString();
        if (!is_subclass_of($className, TestSuite::class) && !is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[AfterAll] but this class does not extend "%s" or "%s".',
                $className,
                $classMethod->name->toString(),
                TestSuite::class,
                TestCase::class
            );
            throw new TestCompilationException($msg);
        } else if (is_subclass_of($className, TestCase::class)) {
            if (!$classMethod->isStatic()) {
                $msg = sprintf(
                    'Failure compiling "%s". The non-static method "%s" cannot be used as a #[AfterAll] hook.',
                    $classMethod->getAttribute('parent')->namespacedName->toString(),
                    $classMethod->name->toString(),
                );
                throw new TestCompilationException($msg);
            }
        }

        $this->collector->attachHook(
            new HookModel(
                $className,
                $classMethod->name->toString(),
                HookType::AfterAll,
                $this->getPriority($attribute)
            )
        );
    }

    private function validateBeforeEachTest(Node\Attribute $attribute, Node\Stmt\ClassMethod $classMethod) : void {
        $className = $classMethod->getAttribute('parent')->namespacedName->toString();
        $this->collector->attachHook(
            new HookModel(
                $className,
                $classMethod->name->toString(),
                HookType::BeforeEachTest,
                $this->getPriority($attribute)
            )
        );
    }

    private function validateAfterEachTest(Node\Attribute $attribute, Node\Stmt\ClassMethod $classMethod) : void {
        $className = $classMethod->getAttribute('parent')->namespacedName->toString();
        $this->collector->attachHook(
            new HookModel(
                $className,
                $classMethod->name->toString(),
                HookType::AfterEachTest,
                $this->getPriority($attribute)
            )
        );
    }

    private function getPriority(Node\Attribute $attribute) : int {
        $priority = 0;
        if (count($attribute->args) === 1) {
            // TODO make sure this is an integer value
            $priority = $attribute->args[0]->value->value;
        }

        return $priority;
    }
}