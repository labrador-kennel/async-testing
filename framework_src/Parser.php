<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\ByteStream\Payload;
use Amp\File\Driver;
use Amp\File\File;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Attribute\DataProvider;
use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Attribute\AttachToTestSuite as TestSuiteAttribute;
use Cspray\Labrador\AsyncUnit\Attribute\DefaultTestSuite as DefaultTestSuiteAttribute;
use Cspray\Labrador\AsyncUnit\Exception\TestCompilationException;
use Cspray\Labrador\AsyncUnit\Model\HookModel;
use Cspray\Labrador\AsyncUnit\Model\PluginModel;
use Cspray\Labrador\AsyncUnit\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Model\TestModel;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;
use Cspray\Labrador\AsyncUnit\NodeVisitor\AsyncUnitVisitor;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use Generator;
use stdClass;
use function Amp\call;
use function Amp\File\filesystem;

/**
 * Responsible for iterating over a directory of PHP source code, analyzing it for code annotated with AysncUnit
 * Attributes, and converting them into the appropriate AsyncUnit Model.
 *
 * !! Synchronous Warning !!
 *
 * To simplify getting started this implementation currently makes use of blocking I/O structures provided by PHP
 * natively. It is expected that the Parser will run BEFORE the Loop starts up or as one of the very first actions
 * that happens after Loop startup. Ultimately parsing the code and ensuring there are no errors and there is something
 * to process needs to be done before any application is able to run so this currently doesn't represent a primary
 * concern. In future versions of this library the Parser may be refactored to use asynchronous I/O.
 *
 * @package Cspray\Labrador\AsyncUnit
 * @see AsyncUnitVisitor
 */
final class Parser {

    use AttributeGroupTraverser;

    private PhpParser $phpParser;
    private NodeTraverser $nodeTraverser;
    private Driver $filesystem;

    public function __construct() {
        $this->phpParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->nodeTraverser = new NodeTraverser();
        $this->filesystem = filesystem();
    }

    /**
     * @param string|array $dirs
     * @return Promise<ParserResult>
     */
    public function parse(string|array $dirs) : Promise {
        return call(function() use($dirs) {
            $defaultTestSuite = null;
            $nonDefaultTestSuites = [];
            $plugins = [];
            $dirs = is_string($dirs) ? [$dirs] : $dirs;
            $parseState = new stdClass();
            $parseState->totalTestCaseCount = 0;
            $parseState->totalTestCount = 0;

            $asyncUnitVisitor = yield $this->doParseDirectories($dirs);
            foreach ($this->parseNodes($asyncUnitVisitor, $parseState) as $model) {
                // The parseDirs implementation is required to yield back models in the order in which we have listed them
                // in this if/elseif statement. ALL TestSuiteModels should be yielded, then all TestCase models and finally
                // any PluginModel. Failure to yield these models in the correct order will result in a fatal error
                if ($model instanceof TestSuiteModel) {
                    if ($model->isDefaultTestSuite()) {
                        $defaultTestSuite = $model;
                    } else {
                        $nonDefaultTestSuites[$model->getClass()] = $model;
                    }
                } else if ($model instanceof TestCaseModel) {
                    $parseState->totalTestCaseCount++;
                    $testCaseTestSuite = null;
                    if (is_null($model->getTestSuiteClass())) {
                        $testCaseTestSuite = $defaultTestSuite;
                    } else {
                        $testCaseTestSuite = $nonDefaultTestSuites[$model->getTestSuiteClass()];
                    }
                    $testCaseTestSuite->addTestCaseModel($model);
                    if ($testCaseTestSuite->isDisabled()) {
                        $model->markDisabled();
                        foreach ($model->getTestMethodModels() as $testMethodModel) {
                            $testMethodModel->markDisabled();
                        }
                    }
                } else if ($model instanceof PluginModel) {
                    $plugins[] = $model;
                }
            }
            $testSuites = array_values($nonDefaultTestSuites);
            if (!empty($defaultTestSuite->getTestCaseModels())) {
                array_unshift($testSuites, $defaultTestSuite);
            }
            return new ParserResult($testSuites, $plugins, $parseState->totalTestCaseCount, $parseState->totalTestCount);
        });
    }

    private function parseNodes(AsyncUnitVisitor $asyncUnitVisitor, stdClass $state) : Generator {
        $classMethods = $asyncUnitVisitor->getAnnotatedClassMethods();

        $hasDefaultTestSuite = false;
        foreach ($asyncUnitVisitor->getTestSuites() as $testSuiteClass) {
            $defaultTestSuiteAttribute = $this->findAttribute(DefaultTestSuiteAttribute::class, ...$testSuiteClass->attrGroups);
            if (!$hasDefaultTestSuite && !is_null($defaultTestSuiteAttribute)) {
                $hasDefaultTestSuite = true;
            }
            $testSuiteModel = new TestSuiteModel($testSuiteClass->namespacedName->toString(), !is_null($defaultTestSuiteAttribute));

            $this->addHooks($testSuiteModel, $classMethods, HookType::BeforeAll());
            $this->addHooks($testSuiteModel, $classMethods, HookType::BeforeEach());
            $this->addHooks($testSuiteModel, $classMethods, HookType::BeforeEachTest());
            $this->addHooks($testSuiteModel, $classMethods, HookType::AfterEachTest());
            $this->addHooks($testSuiteModel, $classMethods, HookType::AfterEach());
            $this->addHooks($testSuiteModel, $classMethods, HookType::AfterAll());

            if ($disabledAttribute = $this->findAttribute(Disabled::class, ...$testSuiteClass->attrGroups)) {
                $reason = null;
                if (count($disabledAttribute->args) === 1) {
                    $reason = $disabledAttribute->args[0]->value->value;
                }
                $testSuiteModel->markDisabled($reason);
            }

            yield $testSuiteModel;
        }
        if (!$hasDefaultTestSuite) {
            yield new TestSuiteModel(ImplicitTestSuite::class, true);
        }

        $testCaseClasses = $asyncUnitVisitor->getTestCases();
        foreach ($testCaseClasses as $testCaseClass) {
            if ($testCaseClass->isAbstract()) {
                continue;
            }

            $testSuiteAttribute = $this->findAttribute(TestSuiteAttribute::class, ...$testCaseClass->attrGroups);
            $testSuiteClassName = null;
            if (!is_null($testSuiteAttribute)) {
                // Right now we are making a huge assumption that the AttachToTestSuite is being specified by declaring it as a class constant, i.e. MyTestSuite::class
                $testSuiteClassName = $testSuiteAttribute->args[0]->value->class->toString();
            }

            $testCaseModel = new TestCaseModel($testCaseClass->namespacedName->toString(), $testSuiteClassName);
            if ($disabledAttribute = $this->findAttribute(Disabled::class, ...$testCaseClass->attrGroups)) {
                $reason = null;
                if (count($disabledAttribute->args) === 1) {
                    $reason = $disabledAttribute->args[0]->value->value;
                }
                $testCaseModel->markDisabled($reason);
            }

            $this->addTestsToTestCaseModel($testCaseClasses, $classMethods, $testCaseModel, $testCaseModel->getClass(), $state);
            if (empty($testCaseModel->getTestMethodModels())) {
                $msg = sprintf(
                    'Failure compiling "%s". There were no #[Test] found.',
                    $testCaseModel->getClass()
                );
                throw new TestCompilationException($msg);
            }

            $this->addHooks($testCaseModel, $classMethods, HookType::BeforeAll());
            $this->addHooks($testCaseModel, $classMethods, HookType::BeforeEach());
            $this->addHooks($testCaseModel, $classMethods, HookType::AfterEach());
            $this->addHooks($testCaseModel, $classMethods, HookType::AfterAll());

            yield $testCaseModel;
        }

        foreach ($asyncUnitVisitor->getPlugins() as $pluginClass) {
            yield new PluginModel($pluginClass->namespacedName->toString());
        }
    }

    /**
     * @param array $dirs
     * @return Promise
     */
    private function doParseDirectories(array $dirs) : Promise {
        return call(function() use($dirs) {
            $nodeConnectingVisitor = new NodeConnectingVisitor();
            $nameResolver = new NameResolver();
            $asyncUnitVisitor = new AsyncUnitVisitor();

            $this->nodeTraverser->addVisitor($nodeConnectingVisitor);
            $this->nodeTraverser->addVisitor($nameResolver);
            $this->nodeTraverser->addVisitor($asyncUnitVisitor);

            foreach ($dirs as $dir) {
                yield $this->traverseDir($dir);
            }

            return $asyncUnitVisitor;
        });
    }

    private function traverseDir(string $dir) : Promise {
        return call(function() use($dir) {
            $files = yield $this->filesystem->scandir($dir);

            foreach ($files as $fileOrDir) {
                $fullPath = $dir . '/' . $fileOrDir;
                if (yield $this->filesystem->isdir($fullPath)) {
                    yield $this->traverseDir($fullPath);
                } else {
                    /** @var File $handle */
                    $handle = yield $this->filesystem->open($fullPath, 'r');
                    $contents = yield (new Payload($handle))->buffer();
                    $statements = $this->phpParser->parse($contents);
                    $this->nodeTraverser->traverse($statements);
                    yield $handle->close();
                    unset($handle);
                    unset($contents);
                }
            }
        });
    }

    private function addHooks(TestSuiteModel|TestCaseModel $model, array $classMethods, HookType $hookType) : void {
        $hookAttribute = sprintf('Cspray\\Labrador\\AsyncUnit\\Attribute\\%s', $hookType->toString());
        foreach ($classMethods as $classMethod) {
            if ($model->getClass() !== $classMethod->getAttribute('parent')->namespacedName->toString()) {
                continue;
            }

            if ($this->findAttribute($hookAttribute, ...$classMethod->attrGroups)) {
                $model->addHook(new HookModel($classMethod, $hookType));
            }
        }
    }
    private function addTestsToTestCaseModel(array $classes, array $classMethods, TestCaseModel $testCaseModel, string $className, stdClass $parseState) {
        foreach ($classMethods as $classMethod) {
            if (!$this->findAttribute(Test::class, ...$classMethod->attrGroups)) {
                continue;
            }
            if ($classMethod->getAttribute('parent')->namespacedName->toString() === $className) {
                $testMethodModel = new TestModel($testCaseModel->getClass(), $classMethod->name->toString());
                $dataProviderAttribute = $this->findAttribute(DataProvider::class, ...$classMethod->attrGroups);
                if (!is_null($dataProviderAttribute)) {
                    $testMethodModel->setDataProvider($dataProviderAttribute->args[0]->value->value);
                }

                $disabledAttribute = $this->findAttribute(Disabled::class, ...$classMethod->attrGroups);
                if (!is_null($disabledAttribute) || $testCaseModel->isDisabled()) {
                    $reason = null;
                    if (!is_null($disabledAttribute) && count($disabledAttribute->args) === 1) {
                        $reason = $disabledAttribute->args[0]->value->value;
                    }
                    $testMethodModel->markDisabled($reason);
                }

                $parseState->totalTestCount++;
                $testCaseModel->addTestMethodModel($testMethodModel);
            }
        }

        $extendedClass = $this->getExtendedClass($classes, $className);
        if (!is_null($extendedClass)) {
            $this->addTestsToTestCaseModel($classes, $classMethods, $testCaseModel, $extendedClass->namespacedName->toString(), $parseState);
        }

    }

    /**
     * @param Class_[] $classes
     * @param string $className
     * @return Class_|null
     */
    private function getExtendedClass(array $classes, string $className) : ?Class_ {
        // Find the Class_ object for the $className we're dealing with
        $foundClass = null;
        foreach ($classes as $class) {
            if ($class->namespacedName->toString() === $className) {
                $foundClass = $class;
                break;
            }
        }

        // Find the class that our Class_->extends matches
        foreach ($classes as $class) {
            if ($foundClass->extends->toString() === $class->namespacedName->toString()) {
                return $class;
            }
        }

        return null;
    }

}