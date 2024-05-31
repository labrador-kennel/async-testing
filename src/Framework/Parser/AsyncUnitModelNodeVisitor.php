<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Parser;

use Labrador\AsyncUnit\Framework\Attribute\AfterAll;
use Labrador\AsyncUnit\Framework\Attribute\AfterEach;
use Labrador\AsyncUnit\Framework\Attribute\AfterEachTest;
use Labrador\AsyncUnit\Framework\Attribute\AttachToTestSuite;
use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\BeforeEach;
use Labrador\AsyncUnit\Framework\Attribute\BeforeEachTest;
use Labrador\AsyncUnit\Framework\Attribute\DataProvider;
use Labrador\AsyncUnit\Framework\Attribute\DefaultTestSuite;
use Labrador\AsyncUnit\Framework\Attribute\Disabled;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Attribute\Timeout;
use Labrador\AsyncUnit\Framework\Exception\TestCompilationException;
use Labrador\AsyncUnit\Framework\HookType;
use Labrador\AsyncUnit\Framework\Model\HookModel;
use Labrador\AsyncUnit\Framework\Model\TestCaseModel;
use Labrador\AsyncUnit\Framework\Model\TestModel;
use Labrador\AsyncUnit\Framework\Model\TestSuiteModel;
use Labrador\AsyncUnit\Framework\TestCase;
use Labrador\AsyncUnit\Framework\TestSuite;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;
use ReflectionMethod;

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
        if ($node instanceof Node\Stmt\Class_ && $node->namespacedName !== null) {
            $class = $node->namespacedName->toString();
            if (is_subclass_of($class, TestSuite::class)) {
                $testSuiteReflection = new ReflectionClass($class);
                $hasDefaultTestSuiteAttribute = $testSuiteReflection->getAttributes(DefaultTestSuite::class) !== [];
                $testSuiteModel = new TestSuiteModel($class, $hasDefaultTestSuiteAttribute);
                $disabledAttributes = $testSuiteReflection->getAttributes(Disabled::class);
                if (count($disabledAttributes) === 1) {
                    $testSuiteModel->markDisabled($disabledAttributes[0]->newInstance()->reason);
                }
                $timeoutAttributes = $testSuiteReflection->getAttributes(Timeout::class);
                if (count($timeoutAttributes) === 1) {
                    $testSuiteModel->setTimeout($timeoutAttributes[0]->newInstance()->timeoutInMilliseconds);
                }
                $this->collector->attachTestSuite($testSuiteModel);
            } else if (is_subclass_of($class, TestCase::class)) {
                if ($node->isAbstract()) {
                    return;
                }
                $testCaseReflection = new ReflectionClass($class);

                $testSuiteClassName = null;
                $testSuiteAttributes = $testCaseReflection->getAttributes(AttachToTestSuite::class);
                if (count($testSuiteAttributes) === 1) {
                    $testSuiteClassName = $testSuiteAttributes[0]->newInstance()->testSuiteClass;
                }
                $testCaseModel = new TestCaseModel($class, $testSuiteClassName);
                $disabledAttributes = $testCaseReflection->getAttributes(Disabled::class);
                if (count($disabledAttributes) === 1) {
                    $testCaseModel->markDisabled($disabledAttributes[0]->newInstance()->reason);
                }
                $timeoutAttributes = $testCaseReflection->getAttributes(Timeout::class);
                if (count($timeoutAttributes) === 1) {
                    $testCaseModel->setTimeout($timeoutAttributes[0]->newInstance()->timeoutInMilliseconds);
                }

                $this->collector->attachTestCase($testCaseModel);
            }
        } else if ($node instanceof Node\Stmt\ClassMethod) {
            $this->collectIfHasAnyAsyncUnitAttribute($node);
        }
    }

    private function collectIfHasAnyAsyncUnitAttribute(Node\Stmt\ClassMethod $classMethod) : void {
        $validAttributes = [
            Test::class => $this->validateTest(...),
            BeforeAll::class => $this->validateBeforeAll(...),
            BeforeEach::class => $this->validateBeforeEach(...),
            AfterAll::class => $this->validateAfterAll(...),
            AfterEach::class => $this->validateAfterEach(...),
            BeforeEachTest::class => $this->validateBeforeEachTest(...),
            AfterEachTest::class => $this->validateAfterEachTest(...)
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

                $reflectionMethod = new ReflectionMethod($className, $classMethod->name->toString());
                $validator($reflectionMethod);
            }
        }
    }

    private function validateTest(ReflectionMethod $reflectionMethod) : void {
        $className = $reflectionMethod->class;
        if (!is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[Test] but this class does not extend "%s".',
                $className,
                $reflectionMethod->name,
                TestCase::class
            );
            throw new TestCompilationException($msg);
        }

        $testModel = new TestModel($className, $reflectionMethod->name);

        $disabledAttributes = $reflectionMethod->getAttributes(Disabled::class);
        if (count($disabledAttributes) === 1) {
            $testModel->markDisabled($disabledAttributes[0]->newInstance()->reason);
        }
        $dataProviderAttributes = $reflectionMethod->getAttributes(DataProvider::class);
        if (count($dataProviderAttributes) === 1) {
            $testModel->setDataProvider($dataProviderAttributes[0]->newInstance()->methodName);
        }
        $timeoutAttributes = $reflectionMethod->getAttributes(Timeout::class);
        if (count($timeoutAttributes) === 1) {
            $testModel->setTimeout($timeoutAttributes[0]->newInstance()->timeoutInMilliseconds);
        }

        $this->collector->attachTest($testModel);
    }

    private function validateBeforeEach(ReflectionMethod $reflectionMethod) : void {
        $className = $reflectionMethod->class;
        if (!is_subclass_of($className, TestSuite::class) && !is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[BeforeEach] but this class does not extend "%s" or "%s".',
                $className,
                $reflectionMethod->name,
                TestSuite::class,
                TestCase::class
            );
            throw new TestCompilationException($msg);
        }

        $beforeEachAttributes = $reflectionMethod->getAttributes(BeforeEach::class);
        assert($beforeEachAttributes !== []);

        $this->collector->attachHook(
            new HookModel(
                $className,
                $reflectionMethod->name,
                HookType::BeforeEach,
                $beforeEachAttributes[0]->newInstance()->priority
            )
        );
    }

    private function validateAfterEach(ReflectionMethod $reflectionMethod) : void {
        $className = $reflectionMethod->class;
        if (!is_subclass_of($className, TestSuite::class) && !is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[AfterEach] but this class does not extend "%s" or "%s".',
                $className,
                $reflectionMethod->name,
                TestSuite::class,
                TestCase::class
            );
            throw new TestCompilationException($msg);
        }

        $afterEachAttributes = $reflectionMethod->getAttributes(AfterEach::class);
        assert($afterEachAttributes !== []);

        $this->collector->attachHook(
            new HookModel(
                $className,
                $reflectionMethod->name,
                HookType::AfterEach,
                $afterEachAttributes[0]->newInstance()->priority
            )
        );
    }

    private function validateBeforeAll(ReflectionMethod $reflectionMethod) : void {
        $className = $reflectionMethod->class;
        if (!is_subclass_of($className, TestSuite::class) && !is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[BeforeAll] but this class does not extend "%s" or "%s".',
                $className,
                $reflectionMethod->getName(),
                TestSuite::class,
                TestCase::class
            );
            throw new TestCompilationException($msg);
        } else if (is_subclass_of($className, TestCase::class)) {
            if (!$reflectionMethod->isStatic()) {
                $msg = sprintf(
                    'Failure compiling "%s". The non-static method "%s" cannot be used as a #[BeforeAll] hook.',
                    $className,
                    $reflectionMethod->getName(),
                );
                throw new TestCompilationException($msg);
            }
        }

        // We know there's an attribute here because we wouldn't call this method if the parser didn't know there
        // was an attribute here
        $beforeAllAttributes = $reflectionMethod->getAttributes(BeforeAll::class);
        assert($beforeAllAttributes !== []);

        $this->collector->attachHook(
            new HookModel(
                $className,
                $reflectionMethod->getName(),
                HookType::BeforeAll,
                $beforeAllAttributes[0]->newInstance()->priority
            )
        );
    }

    private function validateAfterAll(ReflectionMethod $reflectionMethod) : void {
        $className = $reflectionMethod->class;
        if (!is_subclass_of($className, TestSuite::class) && !is_subclass_of($className, TestCase::class)) {
            $msg = sprintf(
                'Failure compiling "%s". The method "%s" is annotated with #[AfterAll] but this class does not extend "%s" or "%s".',
                $className,
                $reflectionMethod->name,
                TestSuite::class,
                TestCase::class
            );
            throw new TestCompilationException($msg);
        } else if (is_subclass_of($className, TestCase::class)) {
            if (!$reflectionMethod->isStatic()) {
                $msg = sprintf(
                    'Failure compiling "%s". The non-static method "%s" cannot be used as a #[AfterAll] hook.',
                    $className,
                    $reflectionMethod->name,
                );
                throw new TestCompilationException($msg);
            }
        }

        $afterAllAttributes = $reflectionMethod->getAttributes(AfterAll::class);
        assert($afterAllAttributes !== []);

        $this->collector->attachHook(
            new HookModel(
                $className,
                $reflectionMethod->name,
                HookType::AfterAll,
                $afterAllAttributes[0]->newInstance()->priority
            )
        );
    }

    private function validateBeforeEachTest(ReflectionMethod $reflectionMethod) : void {
        $className = $reflectionMethod->class;

        $beforeEachTestAttributes = $reflectionMethod->getAttributes(BeforeEachTest::class);
        assert($beforeEachTestAttributes !== []);

        $this->collector->attachHook(
            new HookModel(
                $className,
                $reflectionMethod->name,
                HookType::BeforeEachTest,
                $beforeEachTestAttributes[0]->newInstance()->priority
            )
        );
    }

    private function validateAfterEachTest(ReflectionMethod $reflectionMethod) : void {
        $className = $reflectionMethod->class;

        $afterEachTestAttributes = $reflectionMethod->getAttributes(AfterEachTest::class);
        assert($afterEachTestAttributes !== []);

        $this->collector->attachHook(
            new HookModel(
                $className,
                $reflectionMethod->name,
                HookType::AfterEachTest,
                $afterEachTestAttributes[0]->newInstance()->priority
            )
        );
    }
}